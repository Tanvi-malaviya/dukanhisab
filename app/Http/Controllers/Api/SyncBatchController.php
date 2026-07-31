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

        try {
            $response = DB::transaction(function () use ($resource, $action, $data, $id, $shopId) {
                $controller = app($this->controllerMap[$resource]);
                $subRequest = Request::create('/', $this->verbMap[$action], $data);
                $subRequest->attributes->set('shop_id', $shopId);

                return match ($action) {
                    'create' => $controller->store($subRequest),
                    'update' => $controller->update($subRequest, $id),
                    'delete' => $controller->destroy($subRequest, $id),
                    'return' => method_exists($controller, 'returnSale')
                        ? $controller->returnSale($subRequest, $id)
                        : $controller->returnPurchase($subRequest, $id),
                };
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
}
