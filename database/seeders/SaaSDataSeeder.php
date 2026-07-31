<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shop;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\SupportTicket;
use App\Models\AuditLog;
use App\Models\AppSetting;
use App\Models\InvoiceSetting;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SaaSDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        $premiumPlan = SubscriptionPlan::where('slug', 'premium')->first();

        // 1. App configuration boot settings
        AppSetting::set('app_version', '2.1.0');
        AppSetting::set('min_required_version', '1.9.0');
        AppSetting::set('force_update', 'no');
        AppSetting::set('maintenance_mode', 'no');
        AppSetting::set('announcement_message', 'Welcome to DukanHisab v2.1. New multi-device billing dashboard released!');
        AppSetting::set('feature_flags', json_encode([
            'billing_enabled' => true,
            'backup_enabled' => true,
            'whatsapp_notifications' => false,
            'multi_currency' => false
        ]));

        // 2. Invoice default settings
        InvoiceSetting::set('default_logo', '');
        InvoiceSetting::set('watermark', 'yes');
        InvoiceSetting::set('footer_text', 'Thank you for choosing DukanHisab. For assistance, contact support@dukanhisab.com.');
        InvoiceSetting::set('invoice_prefix', 'DH-2026-');
        InvoiceSetting::set('tax_settings', '18');

        // 3. Populate 15 SaaS client users & shops
        $usersData = [
            ['name' => 'Rajesh Sharma', 'email' => 'rajesh@sharmastore.com', 'mobile' => '9876543210', 'shop' => 'Sharma Provision Store', 'plan' => 'premium'],
            ['name' => 'Amit Patel', 'email' => 'amit@patelgrocery.com', 'mobile' => '9876543211', 'shop' => 'Patel Grocery & Daily', 'plan' => 'premium'],
            ['name' => 'Priya Nair', 'email' => 'priya@nairboutique.com', 'mobile' => '9876543212', 'shop' => 'Nair Designer Boutique', 'plan' => 'free'],
            ['name' => 'Mahesh Verma', 'email' => 'mahesh@vermahardware.com', 'mobile' => '9876543213', 'shop' => 'Verma Hardware & Sanitary', 'plan' => 'premium'],
            ['name' => 'Suresh Gupta', 'email' => 'suresh@guptasweets.com', 'mobile' => '9876543214', 'shop' => 'Gupta Sweets & Bakers', 'plan' => 'premium'],
            ['name' => 'Sunita Rao', 'email' => 'sunita@raofashions.com', 'mobile' => '9876543215', 'shop' => 'Rao Fashions & Style', 'plan' => 'free'],
            ['name' => 'Vikram Singh', 'email' => 'vikram@singhmotors.com', 'mobile' => '9876543216', 'shop' => 'Singh Auto Spares', 'plan' => 'premium'],
            ['name' => 'Karan Johar', 'email' => 'karan@joharelectronics.com', 'mobile' => '9876543217', 'shop' => 'Johar Electronic Hub', 'plan' => 'premium'],
            ['name' => 'Deepak Sen', 'email' => 'deepak@senpharmacy.com', 'mobile' => '9876543218', 'shop' => 'Sen Medicos & Wellness', 'plan' => 'premium'],
            ['name' => 'Nisha Joshi', 'email' => 'nisha@joshisarees.com', 'mobile' => '9876543219', 'shop' => 'Joshi Saree Kendra', 'plan' => 'free'],
            ['name' => 'Arun Kumar', 'email' => 'arun@kumarstationers.com', 'mobile' => '9876543220', 'shop' => 'Kumar Stationers', 'plan' => 'free'],
            ['name' => 'Vijay Yadav', 'email' => 'vijay@yadavmilk.com', 'mobile' => '9876543221', 'shop' => 'Yadav Dairy Farm', 'plan' => 'premium'],
            ['name' => 'Meera Bai', 'email' => 'meera@meeracrafts.com', 'mobile' => '9876543222', 'shop' => 'Meera Handloom & Crafts', 'plan' => 'free'],
            ['name' => 'Rohan Das', 'email' => 'rohan@dasjewellers.com', 'mobile' => '9876543223', 'shop' => 'Das Jewellers', 'plan' => 'premium'],
            ['name' => 'Neha Gupta', 'email' => 'neha@guptamart.com', 'mobile' => '9876543224', 'shop' => 'Gupta Super Mart', 'plan' => 'premium'],
        ];

        // Seed users & shops with distributed creation dates over last 20 days
        foreach ($usersData as $index => $u) {
            $createdDaysAgo = 20 - $index;
            $createdAt = Carbon::now()->subDays($createdDaysAgo);

            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'mobile' => $u['mobile'],
                'password' => Hash::make('password123'),
                'status' => 'active',
                'last_login_at' => Carbon::now()->subHours(rand(1, 48)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $plan = $u['plan'] === 'premium' ? $premiumPlan : $freePlan;

            $user->update(['active_plan_id' => $plan->id]);

            $shop = Shop::create([
                'owner_id' => $user->id,
                'name' => $u['shop'],
                'email' => 'billing@' . explode('@', $user->email)[1],
                'mobile' => $user->mobile,
                'address' => 'SaaS Avenue, Plot ' . (10 + $index) . ', Metro City',
                'gst_number' => '27AAAAA' . (1111 + $index) . 'A1Z' . $index,
                'status' => 'active',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Add subscription
            Subscription::create([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $createdAt,
                'ends_at' => $plan->slug === 'premium' ? $createdAt->copy()->addDays(30) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Add payment histories for premium users
            if ($plan->slug === 'premium') {
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'shop_id' => $shop->id,
                    'plan_id' => $plan->id,
                    'amount' => $plan->price,
                    'payment_gateway' => $index % 2 === 0 ? 'razorpay' : 'stripe',
                    'transaction_id' => 'tx_' . Str_random(12),
                    'status' => 'successful',
                    'payment_date' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Create a refund simulation for one payment
                if ($index === 14) {
                    $refund = Refund::create([
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount,
                        'reason' => 'Duplicate subscription billed by mistake.',
                        'status' => 'successful',
                        'refund_date' => $createdAt->copy()->addDay(),
                        'created_at' => $createdAt->copy()->addDay(),
                    ]);
                    $payment->update(['status' => 'refunded']);
                    $user->update(['active_plan_id' => $freePlan->id]);
                }
            }

            // Create a failed payment simulation
            if ($index % 4 === 0) {
                Payment::create([
                    'user_id' => $user->id,
                    'shop_id' => $shop->id,
                    'plan_id' => $premiumPlan->id,
                    'amount' => $premiumPlan->price,
                    'payment_gateway' => 'razorpay',
                    'transaction_id' => 'tx_fail_' . Str_random(12),
                    'status' => 'failed',
                    'payment_date' => $createdAt->copy()->addHours(2),
                    'created_at' => $createdAt->copy()->addHours(2),
                ]);
            }
        }

        // 4. Seed Support Tickets
        $supportTickets = [
            [
                'user_id' => 1,
                'subject' => 'Unable to generate GST Invoice',
                'message' => 'Whenever I try to print an invoice, the tax configuration defaults to 18% and does not let me set CGST/SGST separate split headers. Please guide.',
                'status' => 'open',
            ],
            [
                'user_id' => 2,
                'subject' => 'Billing barcode scanner delay',
                'message' => 'The barcode scanner sync is lagging on the mobile app. It takes 2-3 seconds to fetch product details.',
                'status' => 'resolved',
                'admin_reply' => 'Hi, please update your app version to 2.1.0 in settings. We optimized item cache lookups which solves scan delays.',
                'replied_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => 4,
                'subject' => 'Request database migration restore point',
                'message' => 'I deleted 3 items by mistake. Can you please restore my database backup from yesterday?',
                'status' => 'inProgress',
            ],
            [
                'user_id' => 5,
                'subject' => 'Feature request: Whatsapp integration',
                'message' => 'Would love to send invoices directly to customer phone numbers using official WhatsApp APIs. Any plans for this?',
                'status' => 'open',
            ]
        ];

        foreach ($supportTickets as $st) {
            SupportTicket::create([
                'user_id' => $st['user_id'],
                'subject' => $st['subject'],
                'message' => $st['message'],
                'status' => $st['status'],
                'admin_reply' => $st['admin_reply'] ?? null,
                'replied_at' => $st['replied_at'] ?? null,
                'created_at' => Carbon::now()->subDays(5),
            ]);
        }

        // 5. Seed Audit Logs
        $auditLogs = [
            ['action' => 'Admin panel dashboard session opened', 'payload' => ['ip' => '127.0.0.1']],
            ['action' => 'Modified system configuration minimum app version requirement', 'payload' => ['old' => '1.8.0', 'new' => '1.9.0']],
            ['action' => 'Bulk announcement campaign dispatched', 'payload' => ['segment' => 'all']],
            ['action' => 'Invoice tax rules adjusted globally', 'payload' => ['vat_gst' => '18']],
        ];

        foreach ($auditLogs as $index => $al) {
            AuditLog::create([
                'admin_id' => 1,
                'action' => $al['action'],
                'payload' => $al['payload'],
                'ip_address' => '192.168.1.55',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
                'created_at' => Carbon::now()->subDays(6 - $index),
            ]);
        }
    }
}

// Helper local function to generate random strings for mock payments
if (!function_exists('Str_random')) {
    function Str_random($length = 16) {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1, 10))), 0, $length);
    }
}
