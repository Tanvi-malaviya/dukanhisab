<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    /**
     * Handle an incoming request.
     *
     * Makes POST requests carrying an Idempotency-Key header safe to retry:
     * a retried request with the same key and body replays the cached
     * response instead of re-executing the underlying business logic.
     * Requests without the header, or that aren't POST, pass through untouched.
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('Idempotency-Key');

        if ($request->method() !== 'POST' || !$key) {
            return $next($request);
        }

        $shopId = $request->attributes->get('shop_id');
        $hash = hash('sha256', $request->method() . '|' . $request->path() . '|' . $request->getContent());

        $record = $this->reserve($shopId, $key, $request, $hash);

        if ($record === null) {
            // Genuine concurrent race on a fresh key.
            return response()->json([
                'error' => 'Request already in progress, retry shortly.',
            ], 409)->header('Retry-After', 2);
        }

        if ($record->status === 'completed') {
            if ($record->request_hash !== $hash) {
                return response()->json([
                    'error' => 'Idempotency-Key already used with a different request.',
                ], 409);
            }

            return ResponseFacade::make($record->response_body, $record->response_status)
                ->header('Content-Type', 'application/json')
                ->header('X-Idempotent-Replay', 'true');
        }

        // $record is a fresh "processing" row this request now owns.
        $response = $next($request);

        if ($response->getStatusCode() >= 500) {
            // Don't poison the key on a genuine server error; next retry gets a clean attempt.
            IdempotencyKey::where('id', $record->id)->delete();
        } else {
            $record->update([
                'status' => 'completed',
                'response_status' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
            ]);
        }

        return $response;
    }

    /**
     * Reserve a processing row for this key, or return the existing row if
     * one is already there. Returns null if another request is genuinely
     * mid-flight and this one should back off.
     */
    private function reserve(?int $shopId, string $key, Request $request, string $hash): ?IdempotencyKey
    {
        try {
            return IdempotencyKey::create([
                'shop_id' => $shopId,
                'idempotency_key' => $key,
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'request_hash' => $hash,
                'status' => 'processing',
                'expires_at' => now()->addDays((int) config('sync.idempotency_ttl_days')),
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
        }

        $existing = IdempotencyKey::where('shop_id', $shopId)->where('idempotency_key', $key)->first();

        if (!$existing) {
            // Row vanished between the failed insert and this lookup (e.g. stale-lock
            // cleanup elsewhere) — safe to try reserving once more.
            return $this->reserveOnce($shopId, $key, $request, $hash);
        }

        if ($existing->status === 'completed') {
            return $existing;
        }

        $staleAfter = (int) config('sync.idempotency_stale_lock_seconds');
        if ($existing->updated_at->lt(now()->subSeconds($staleAfter))) {
            $existing->delete();
            return $this->reserveOnce($shopId, $key, $request, $hash);
        }

        return null;
    }

    private function reserveOnce(?int $shopId, string $key, Request $request, string $hash): ?IdempotencyKey
    {
        try {
            return IdempotencyKey::create([
                'shop_id' => $shopId,
                'idempotency_key' => $key,
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'request_hash' => $hash,
                'status' => 'processing',
                'expires_at' => now()->addDays((int) config('sync.idempotency_ttl_days')),
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            return null;
        }
    }
}
