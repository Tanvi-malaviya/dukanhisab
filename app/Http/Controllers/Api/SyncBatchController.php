<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SyncBatchController extends Controller
{
    private array $controllerMap = [
        'products' => ProductApiController::class,
        'customers' => CustomerApiController::class,
        'suppliers' => SupplierApiController::class,
        'sales' => SaleApiController::class,
        'purchases' => PurchaseApiController::class,
        'cashbooks' => CashBookApiController::class,
        'categories' => CategoryApiController::class,
        'expenses' => ExpenseApiController::class,
        // Secondary records the app can edit offline. Prices, invoice settings and the daily register
        // closure are upserts (safe to replay); the batch Idempotency-Key covers the rest.
        'customer_prices' => CustomerProductPriceApiController::class,
        'supplier_prices' => SupplierProductPriceApiController::class,
        'invoice_settings' => InvoiceSettingApiController::class,
        'register_closures' => RegisterClosureApiController::class,
        'bank_transfers' => BankTransferApiController::class,
        'stock_adjustments' => \App\Http\Controllers\Api\StockMovementApiController::class,
    ];

    /** Table that stores each resource (expenses are cash-book rows). */
    private array $tableMap = [
        'products' => 'products',
        'customers' => 'customers',
        'suppliers' => 'suppliers',
        'sales' => 'sales',
        'purchases' => 'purchases',
        'cashbooks' => 'cash_books',
        'categories' => 'categories',
        'expenses' => 'cash_books',
    ];

    private array $verbMap = [
        'create' => 'POST',
        'update' => 'PUT',
        'delete' => 'DELETE',
        'return' => 'POST',
    ];

    public function batch(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $maxOps = (int) config('sync.batch_max_operations');

        $validator = Validator::make($request->all(), [
            'operations' => "required|array|min:1|max:{$maxOps}",
            'operations.*.resource' => 'required|string|in:' . implode(',', array_keys($this->controllerMap)),
            'operations.*.action' => 'required|string|in:create,update,delete,return',
            'operations.*.id' => 'nullable|integer|required_if:operations.*.action,update,delete,return',
            'operations.*.data' => 'nullable|array|required_if:operations.*.action,create,update,return',
            'operations.*.op_id' => 'nullable|string|max:255',
            'operations.*.client_uuid' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $results = [];

        foreach ($request->input('operations') as $op) {
            $results[] = $this->runOperation($op, $shopId);
        }

        return response()->json(['results' => $results]);
    }

    private function runOperation(array $op, ?int $shopId): array
    {
        $resource = $op['resource'];
        $action = $op['action'];
        $opId = $op['op_id'] ?? null;
        $id = $op['id'] ?? null;
        $data = $op['data'] ?? [];

        // A client-generated id on a create makes the operation idempotent per record: if we already
        // created this record (an earlier attempt succeeded but the client never saw the reply), hand
        // back the existing one instead of creating a duplicate.
        $clientUuid = $action === 'create' ? ($op['client_uuid'] ?? ($data['client_uuid'] ?? null)) : null;
        unset($data['client_uuid']);

        try {
            $response = DB::transaction(function () use ($resource, $action, $data, $id, $shopId, $clientUuid) {
                $controller = app($this->controllerMap[$resource]);
                $table = $this->tableMap[$resource] ?? null;

                if ($clientUuid !== null && $shopId !== null && $table !== null) {
                    $existing = $this->findByClientUuid($table, $shopId, $clientUuid);
                    if ($existing) {
                        return $this->existingRecordResponse($controller, $existing, $shopId);
                    }
                }

                $subRequest = Request::create('/', $this->verbMap[$action], $data);
                $subRequest->attributes->set('shop_id', $shopId);

                $response = match ($action) {
                    'create' => $controller->store($subRequest),
                    'update' => $controller->update($subRequest, $id),
                    'delete' => $controller->destroy($subRequest, $id),
                    'return' => method_exists($controller, 'returnSale')
                        ? $controller->returnSale($subRequest, $id)
                        : $controller->returnPurchase($subRequest, $id),
                };

                if ($clientUuid !== null && $table !== null && $response->getStatusCode() === 201) {
                    $created = json_decode($response->getContent(), true);
                    if (isset($created['id'])) {
                        // Raw update: must not touch updated_at (that would re-announce the record to other devices).
                        DB::table($table)->where('id', $created['id'])->update(['client_uuid' => $clientUuid]);
                    }
                }

                return $response;
            });

            $status = $response->getStatusCode();
            $content = $response->getContent();
            $decoded = $content !== '' ? json_decode($content, true) : null;

            return [
                'op_id' => $opId,
                'resource' => $resource,
                'action' => $action,
                'success' => $status < 400,
                'status' => $status,
                'data' => $status < 400 ? $decoded : null,
                'error' => $status >= 400 ? $decoded : null,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'op_id' => $opId,
                'resource' => $resource,
                'action' => $action,
                'success' => false,
                'status' => 404,
                'error' => ['message' => 'Record not found.'],
            ];
        } catch (\Throwable $e) {
            Log::error('Sync batch operation failed', [
                'op_id' => $opId,
                'resource' => $resource,
                'action' => $action,
                'exception' => $e->getMessage(),
            ]);

            return [
                'op_id' => $opId,
                'resource' => $resource,
                'action' => $action,
                'success' => false,
                'status' => 500,
                'error' => ['message' => 'Internal error processing this operation.'],
            ];
        }
    }

    private function findByClientUuid(string $table, int $shopId, string $clientUuid): ?object
    {
        return DB::table($table)->where('shop_id', $shopId)->where('client_uuid', $clientUuid)->first();
    }

    /** Same shape the create would have returned, flagged as a replay so clients can tell. */
    private function existingRecordResponse(object $controller, object $row, int $shopId)
    {
        $subRequest = Request::create('/', 'GET');
        $subRequest->attributes->set('shop_id', $shopId);

        try {
            $response = $controller->show($subRequest, $row->id);
        } catch (ModelNotFoundException $e) {
            // Created, then deleted since — still the same record, so return it as-is rather than re-creating it.
            $response = response()->json($row);
        }

        return response()->json(json_decode($response->getContent(), true), 200)->header('X-Idempotent-Replay', 'true');
    }
}
