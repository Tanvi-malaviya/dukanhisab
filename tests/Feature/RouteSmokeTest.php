<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\AddOnSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Hits every registered API and admin-panel route against a seeded shop and
 * fails on any 5xx. Also verifies the JSON shapes the web/mobile screens read.
 */
class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::fake(['*' => Http::response(['Status' => 'Success', 'PostOffice' => []], 200)]);
        $this->withoutVite();
        $this->seed(SubscriptionPlanSeeder::class);
        $this->seed(AddOnSeeder::class);

        $this->user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $this->user->id, 'name' => 'Smoke Shop', 'status' => 'active']);
        $this->user->update(['shop_id' => $this->shop->id]);
        Sanctum::actingAs($this->user);
        $this->seedData();
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function seedData(): void
    {
        $p = Product::create(['shop_id' => $this->shop->id, 'name' => 'Item', 'stock' => 50, 'selling_price' => 100, 'purchase_price' => 60, 'low_stock_threshold' => 60]);
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Cust', 'mobile' => '9000000000', 'due_amount' => 0]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Sup', 'mobile' => '8000000000', 'due_amount' => 0]);
        $this->withHeaders($this->h())->postJson('/api/v1/categories', ['name' => 'Cat']);
        $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'customer_id' => $c->id, 'subtotal' => 200, 'grand_total' => 200, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 2, 'selling_price' => 100]],
        ])->assertStatus(201);
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 300, 'paid_amount' => 100, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 5, 'purchase_price' => 60]],
        ])->assertStatus(201);
        $this->withHeaders($this->h())->postJson('/api/v1/expenses', ['amount' => 20, 'payment_method' => 'cash', 'description' => 'Tea'])->assertStatus(201);
    }

    private function fill(string $uri): string
    {
        return preg_replace_callback('/\{(\w+)\??\}/', function ($m) {
            return match ($m[1]) {
                'filename' => 'missing.sql',
                'status' => 'closed',
                'pincode' => '380001',
                'subdomain' => 'smoke-shop',
                'path' => 'x.png',
                'any' => 'x',
                default => '1',
            };
        }, $uri);
    }

    // Routes that would log the test client out or hit external services in ways not worth exercising blindly.
    private function skip(string $uri): bool
    {
        return (bool) preg_match('#(logout|razorpay/webhook|/restore|backup/export|backups$)#', $uri);
    }

    public function test_every_api_route_responds_without_server_error(): void
    {
        $bad = [];
        $count = 0;
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (!str_starts_with($uri, 'api/') || $this->skip($uri)) {
                continue;
            }
            foreach (array_diff($route->methods(), ['HEAD', 'OPTIONS']) as $method) {
                $count++;
                $url = '/' . $this->fill($uri);
                try {
                    $resp = $this->withHeaders($this->h())->json($method, $url, []);
                    $status = $resp->status();
                } catch (\Throwable $e) {
                    $status = 500;
                    $resp = null;
                    $msg = get_class($e) . ': ' . substr($e->getMessage(), 0, 160);
                }
                if ($status >= 500) {
                    $bad[] = "$method $url -> $status " . ($msg ?? substr((string) $resp?->getContent(), 0, 160));
                }
            }
        }
        $this->assertGreaterThan(80, $count, 'expected to exercise the full API route list');
        $this->assertSame([], $bad, "Routes returning 5xx:\n" . implode("\n", $bad));
    }

    public function test_every_admin_screen_renders_for_a_logged_in_admin(): void
    {
        $admin = Admin::create(['name' => 'Root', 'email' => 'root@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $bad = [];
        $count = 0;
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (!str_starts_with($uri, 'admin') || !in_array('GET', $route->methods()) || $this->skip($uri) || $uri === 'admin/login') {
                continue;
            }
            if (str_contains($uri, 'login-as') || str_contains($uri, 'download') || str_contains($uri, '/backup')) {
                continue;
            }
            $count++;
            $url = '/' . $this->fill($uri);
            $obLevel = ob_get_level();
            try {
                $status = $this->actingAs($admin, 'admin')->get($url)->status();
                $msg = '';
            } catch (\Throwable $e) {
                $status = 500;
                $msg = get_class($e) . ': ' . substr($e->getMessage(), 0, 200);
            }
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            if ($status >= 500) {
                $bad[] = "GET $url -> $status $msg";
            }
        }
        $this->assertGreaterThan(10, $count);
        $this->assertSame([], $bad, "Admin screens failing:\n" . implode("\n", $bad));
    }

    public function test_shop_owner_web_pages_render(): void
    {
        foreach (['/', '/shop/login', '/shop/register', '/shop/dashboard', '/shop/sales', '/shop/reports', '/shop/settings'] as $url) {
            $this->get($url)->assertStatus(200);
        }
    }

    /** The web/mobile screens read these exact keys — make sure the API still supplies them. */
    public function test_json_shapes_used_by_screens(): void
    {
        $dash = $this->withHeaders($this->h())->getJson('/api/v1/dashboard')->assertStatus(200);
        foreach (['today_sales', 'today_purchases', 'cash_balance', 'bank_balance', 'customer_due', 'supplier_due', 'low_stock_count', 'low_stock_products', 'recent_sales', 'recent_purchases'] as $k) {
            $dash->assertJsonStructure([$k]);
        }
        $this->assertEquals(200, $dash->json('customer_due'));
        $this->assertEquals(200, $dash->json('supplier_due'));
        $this->assertGreaterThanOrEqual(1, $dash->json('low_stock_count'));

        $this->withHeaders($this->h())->getJson('/api/v1/reports')->assertJsonStructure(
            ['total_sales', 'sales_count', 'sales_by_payment_type', 'total_purchases', 'purchases_count', 'total_expenses', 'expenses_count', 'net_profit']
        );
        $this->withHeaders($this->h())->getJson('/api/v1/sales')->assertJsonStructure([['id', 'sale_number', 'grand_total', 'status', 'customer', 'items']]);
        $this->withHeaders($this->h())->getJson('/api/v1/purchases')->assertJsonStructure([['id', 'purchase_number', 'total_amount', 'status']]);
        $this->withHeaders($this->h())->getJson('/api/v1/customers')->assertJsonStructure([['id', 'name', 'mobile', 'due_amount']]);
        $this->withHeaders($this->h())->getJson('/api/v1/suppliers')->assertJsonStructure([['id', 'name', 'mobile', 'due_amount']]);
        $this->withHeaders($this->h())->getJson('/api/v1/products')->assertJsonStructure([['id', 'name', 'stock', 'selling_price', 'purchase_price']]);
        $this->withHeaders($this->h())->getJson('/api/v1/cashbooks')->assertStatus(200);
        $this->withHeaders($this->h())->getJson('/api/v1/expenses')->assertJsonStructure([['id', 'amount', 'description']]);
        $this->withHeaders($this->h())->getJson('/api/v1/bank-accounts')->assertStatus(200);
        $this->withHeaders($this->h())->getJson('/api/v1/register-closures/current-status')->assertStatus(200);

        $plans = $this->getJson('/api/v1/shopowner/subscription-plans')->assertStatus(200);
        $this->assertNotEmpty($plans->json('plans'), 'plans must come from the database');
        $addons = $this->getJson('/api/v1/shopowner/add-ons')->assertStatus(200);
        $this->assertNotEmpty($addons->json('add_ons'), 'add-ons must come from the database');
        $this->getJson('/api/v1/shopowner/profile')->assertStatus(200);
        $this->getJson('/api/v1/shopowner/subscription')->assertStatus(200);
    }

    public function test_plan_and_addon_prices_shown_to_users_come_from_the_database(): void
    {
        $plan = \App\Models\SubscriptionPlan::where('price', '>', 0)->first();
        $plan->update(['price' => 1234.50]);
        $found = collect($this->getJson('/api/v1/shopowner/subscription-plans')->json('plans'))->firstWhere('id', $plan->id);
        $this->assertEquals(1234.50, (float) $found['price']);
    }
}
