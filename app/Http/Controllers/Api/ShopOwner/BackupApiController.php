<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CashBook;
use App\Models\InvoiceConfig;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class BackupApiController extends Controller
{
    public function export(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->activePlan || !in_array($user->activePlan->slug, ['premium', 'business'])) {
            return response()->json(['message' => 'Please upgrade to Premium or Business Plan to use Backup & Restore.'], 403);
        }

        $shopId = $request->attributes->get('shop_id');
        $shop = Shop::find($shopId);

        if (!$shop && $request->user()) {
            $shop = $request->user()->shops()->first() ?? Shop::first();
            if ($shop) {
                $shopId = $shop->id;
            }
        }

        if (!$shop) {
            return response()->json(['message' => 'Shop not found.'], 404);
        }

        $backupData = [
            'app' => 'DukanHisab',
            'version' => '1.0',
            'type' => 'shop_data_backup',
            'exported_at' => now()->toDateTimeString(),
            'shop' => [
                'name' => $shop->name,
                'email' => $shop->email,
                'mobile' => $shop->mobile,
                'gst_number' => $shop->gst_number,
                'address' => $shop->address,
                'city' => $shop->city,
                'state' => $shop->state,
                'pincode' => $shop->pincode,
                'currency' => $shop->currency ?? 'INR',
                'upi_id' => $shop->upi_id,
                'bank_details' => $shop->bank_details,
                'invoice_footer' => $shop->invoice_footer,
            ],
            'invoice_settings' => InvoiceConfig::where('shop_id', $shopId)->first(),
            'categories' => Category::where('shop_id', $shopId)->get(),
            'products' => Product::where('shop_id', $shopId)->get(),
            'customers' => Customer::where('shop_id', $shopId)->get(),
            'suppliers' => Supplier::where('shop_id', $shopId)->get(),
            'sales' => Sale::where('shop_id', $shopId)->with('items')->get(),
            'purchases' => Purchase::where('shop_id', $shopId)->with('items')->get(),
            'cashbooks' => CashBook::where('shop_id', $shopId)->get(),
        ];

        $json = json_encode($backupData, JSON_UNESCAPED_SLASHES);
        $encrypted = Crypt::encryptString($json);
        $fileName = 'dukanhisab-backup-' . Str::slug($shop->name ?: 'shop') . '-' . date('Y-m-d-His') . '.dhbak';

        return response()->streamDownload(function () use ($encrypted) {
            echo $encrypted;
        }, $fileName, ['Content-Type' => 'application/octet-stream']);
    }

    public function restore(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->activePlan || !in_array($user->activePlan->slug, ['premium', 'business'])) {
            return response()->json(['message' => 'Please upgrade to Premium or Business Plan to use Backup & Restore.'], 403);
        }

        $shopId = $request->attributes->get('shop_id');
        $shop = Shop::find($shopId);

        if (!$shop && $request->user()) {
            $shop = $request->user()->shops()->first() ?? Shop::first();
            if ($shop) {
                $shopId = $shop->id;
            }
        }

        if (!$shop) {
            return response()->json(['message' => 'Shop not found.'], 404);
        }

        $request->validate([
            'backup_file' => 'required|file|max:10240',
        ], [
            'backup_file.required' => 'Please select a valid backup JSON file.',
        ]);

        $file = $request->file('backup_file');
        $content = file_get_contents($file->getRealPath());

        try {
            $json = Crypt::decryptString($content);
        } catch (DecryptException $e) {
            // Fall back to legacy unencrypted backups exported before encryption was added.
            $json = $content;
        }

        $data = json_decode($json, true);

        if (!$data || !isset($data['app']) || $data['app'] !== 'DukanHisab') {
            return response()->json(['message' => 'Invalid backup file. Please select a valid DukanHisab backup file.'], 422);
        }

        DB::transaction(function () use ($data, $shopId, $shop) {
            // 1. Shop details
            if (isset($data['shop']) && is_array($data['shop'])) {
                $sData = $data['shop'];
                $shop->update(array_filter([
                    'name' => $sData['name'] ?? null,
                    'email' => $sData['email'] ?? null,
                    'mobile' => $sData['mobile'] ?? null,
                    'gst_number' => $sData['gst_number'] ?? null,
                    'address' => $sData['address'] ?? null,
                    'city' => $sData['city'] ?? null,
                    'state' => $sData['state'] ?? null,
                    'pincode' => $sData['pincode'] ?? null,
                    'currency' => $sData['currency'] ?? 'INR',
                    'upi_id' => $sData['upi_id'] ?? null,
                    'bank_details' => $sData['bank_details'] ?? null,
                    'invoice_footer' => $sData['invoice_footer'] ?? null,
                ]));
            }

            // 2. Invoice Settings
            if (isset($data['invoice_settings']) && is_array($data['invoice_settings'])) {
                $inv = $data['invoice_settings'];
                unset($inv['id'], $inv['shop_id'], $inv['created_at'], $inv['updated_at']);
                InvoiceConfig::updateOrCreate(['shop_id' => $shopId], $inv);
            }

            // 3. Categories
            $categoryMap = [];
            if (isset($data['categories']) && is_array($data['categories'])) {
                foreach ($data['categories'] as $cat) {
                    $oldId = $cat['id'] ?? null;
                    $catObj = Category::updateOrCreate(
                        ['shop_id' => $shopId, 'name' => $cat['name']],
                        ['description' => $cat['description'] ?? null]
                    );
                    if ($oldId) $categoryMap[$oldId] = $catObj->id;
                }
            }

            // 4. Products
            $productMap = [];
            if (isset($data['products']) && is_array($data['products'])) {
                foreach ($data['products'] as $prod) {
                    $oldId = $prod['id'] ?? null;
                    $catId = isset($prod['category_id']) && isset($categoryMap[$prod['category_id']]) 
                        ? $categoryMap[$prod['category_id']] 
                        : null;

                    $prodObj = Product::updateOrCreate(
                        ['shop_id' => $shopId, 'name' => $prod['name']],
                        [
                            'category_id' => $catId,
                            'barcode' => $prod['barcode'] ?? null,
                            'selling_price' => $prod['selling_price'] ?? 0,
                            'purchase_price' => $prod['purchase_price'] ?? 0,
                            'stock' => $prod['stock'] ?? 0,
                            'unit' => $prod['unit'] ?? 'pcs',
                            'low_stock_threshold' => $prod['low_stock_threshold'] ?? 5,
                        ]
                    );
                    if ($oldId) $productMap[$oldId] = $prodObj->id;
                }
            }

            // 5. Customers
            $customerMap = [];
            if (isset($data['customers']) && is_array($data['customers'])) {
                foreach ($data['customers'] as $cust) {
                    $oldId = $cust['id'] ?? null;
                    $custObj = Customer::updateOrCreate(
                        ['shop_id' => $shopId, 'name' => $cust['name'], 'mobile' => $cust['mobile'] ?? null],
                        [
                            'email' => $cust['email'] ?? null,
                            'due_amount' => $cust['due_amount'] ?? 0,
                            'address' => $cust['address'] ?? null,
                        ]
                    );
                    if ($oldId) $customerMap[$oldId] = $custObj->id;
                }
            }

            // 6. Suppliers
            $supplierMap = [];
            if (isset($data['suppliers']) && is_array($data['suppliers'])) {
                foreach ($data['suppliers'] as $sup) {
                    $oldId = $sup['id'] ?? null;
                    $supObj = Supplier::updateOrCreate(
                        ['shop_id' => $shopId, 'name' => $sup['name'], 'mobile' => $sup['mobile'] ?? null],
                        [
                            'email' => $sup['email'] ?? null,
                            'due_amount' => $sup['due_amount'] ?? 0,
                            'address' => $sup['address'] ?? null,
                        ]
                    );
                    if ($oldId) $supplierMap[$oldId] = $supObj->id;
                }
            }

            // 7. Sales & Sale Items
            if (isset($data['sales']) && is_array($data['sales'])) {
                foreach ($data['sales'] as $saleData) {
                    $custId = isset($saleData['customer_id']) && isset($customerMap[$saleData['customer_id']])
                        ? $customerMap[$saleData['customer_id']]
                        : null;

                    $sale = Sale::withTrashed()->where('sale_number', $saleData['sale_number'])->first();
                    if ($sale) {
                        if ($sale->trashed()) {
                            $sale->restore();
                        }
                        $sale->update([
                            'shop_id' => $shopId,
                            'customer_id' => $custId,
                            'subtotal' => $saleData['subtotal'] ?? 0,
                            'discount' => $saleData['discount'] ?? 0,
                            'grand_total' => $saleData['grand_total'] ?? 0,
                            'payment_type' => $saleData['payment_type'] ?? 'Cash',
                            'status' => $saleData['status'] ?? 'Completed',
                            'sale_date' => $saleData['sale_date'] ?? now(),
                        ]);
                    } else {
                        $sale = Sale::create([
                            'shop_id' => $shopId,
                            'sale_number' => $saleData['sale_number'],
                            'customer_id' => $custId,
                            'subtotal' => $saleData['subtotal'] ?? 0,
                            'discount' => $saleData['discount'] ?? 0,
                            'grand_total' => $saleData['grand_total'] ?? 0,
                            'payment_type' => $saleData['payment_type'] ?? 'Cash',
                            'status' => $saleData['status'] ?? 'Completed',
                            'sale_date' => $saleData['sale_date'] ?? now(),
                        ]);
                    }

                    if (isset($saleData['items']) && is_array($saleData['items'])) {
                        $sale->items()->delete();
                        foreach ($saleData['items'] as $item) {
                            $prodId = isset($item['product_id']) && isset($productMap[$item['product_id']])
                                ? $productMap[$item['product_id']]
                                : null;

                            $price = $item['selling_price'] ?? $item['unit_price'] ?? 0;
                            $sale->items()->create([
                                'product_id' => $prodId,
                                'quantity' => $item['quantity'] ?? 1,
                                'returned_quantity' => $item['returned_quantity'] ?? 0,
                                'selling_price' => $price,
                            ]);
                        }
                    }
                }
            }

            // 8. Purchases & Purchase Items
            if (isset($data['purchases']) && is_array($data['purchases'])) {
                foreach ($data['purchases'] as $purData) {
                    $supId = isset($purData['supplier_id']) && isset($supplierMap[$purData['supplier_id']])
                        ? $supplierMap[$purData['supplier_id']]
                        : null;

                    $purchase = Purchase::withTrashed()->where('purchase_number', $purData['purchase_number'])->first();
                    if ($purchase) {
                        if ($purchase->trashed()) {
                            $purchase->restore();
                        }
                        $purchase->update([
                            'shop_id' => $shopId,
                            'supplier_id' => $supId,
                            'total_amount' => $purData['total_amount'] ?? 0,
                            'payment_type' => $purData['payment_type'] ?? 'Cash',
                            'status' => $purData['status'] ?? 'Completed',
                            'purchase_date' => $purData['purchase_date'] ?? now(),
                        ]);
                    } else {
                        $purchase = Purchase::create([
                            'shop_id' => $shopId,
                            'purchase_number' => $purData['purchase_number'],
                            'supplier_id' => $supId,
                            'total_amount' => $purData['total_amount'] ?? 0,
                            'payment_type' => $purData['payment_type'] ?? 'Cash',
                            'status' => $purData['status'] ?? 'Completed',
                            'purchase_date' => $purData['purchase_date'] ?? now(),
                        ]);
                    }

                    if (isset($purData['items']) && is_array($purData['items'])) {
                        $purchase->items()->delete();
                        foreach ($purData['items'] as $item) {
                            $prodId = isset($item['product_id']) && isset($productMap[$item['product_id']])
                                ? $productMap[$item['product_id']]
                                : null;

                            $price = $item['purchase_price'] ?? $item['unit_price'] ?? 0;
                            $purchase->items()->create([
                                'product_id' => $prodId,
                                'quantity' => $item['quantity'] ?? 1,
                                'returned_quantity' => $item['returned_quantity'] ?? 0,
                                'purchase_price' => $price,
                            ]);
                        }
                    }
                }
            }

            // 9. CashBook
            if (isset($data['cashbooks']) && is_array($data['cashbooks'])) {
                foreach ($data['cashbooks'] as $cb) {
                    CashBook::create([
                        'shop_id' => $shopId,
                        'type' => $cb['type'] ?? 'cash_in',
                        'amount' => $cb['amount'] ?? 0,
                        'payment_method' => $cb['payment_method'] ?? 'cash',
                        'description' => $cb['description'] ?? '',
                        'transaction_date' => $cb['transaction_date'] ?? now(),
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Shop data restored successfully from backup!'], 200);
    }
}
