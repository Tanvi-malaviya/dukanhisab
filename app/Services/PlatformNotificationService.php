<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PlatformNotificationService
{
    public const EVENT_USER_REGISTERED = 'user_registered';
    public const EVENT_SHOP_ADDED = 'shop_added';
    public const EVENT_WEBSITE_PURCHASED = 'website_purchased';
    public const EVENT_WHATSAPP_SERVICE_PURCHASED = 'whatsapp_service_purchased';
    public const EVENT_WHATSAPP_LOW_CREDITS = 'whatsapp_low_credits';
    public const EVENT_PLAN_PURCHASED = 'plan_purchased';
    public const EVENT_PLAN_UPGRADED = 'plan_upgraded';
    public const EVENT_PLAN_DOWNGRADED = 'plan_downgraded';
    public const EVENT_PLAN_CANCELLED = 'plan_cancelled';

    /**
     * All 9 lifecycle notification definitions for WhatsApp and Email.
     */
    public const TEMPLATES = [
        self::EVENT_USER_REGISTERED => [
            'name' => 'User Registration Welcome',
            'whatsapp' => "🎉 *Welcome to DukanHisab, {{name}}!*\n\nThank you for joining us! 💚\n\nDukanHisab helps you manage your business records in one place, including sales, purchases, products, inventory, customers, suppliers, expenses and reports.\n\n🚀 Ready to get started? Set up your shop and start managing your business digitally.\n\n📱 *Download App:*\nhttps://play.google.com/store/apps/details?id=com.app.dukanhisab\n\n💻 *Web Dashboard:*\nhttps://dukanhisab.in/shop/login\n\n*DukanHisab — Apni Dukaan Ka Hisab, Ab Ek App Mein!* 🏪",
            'email_subject' => 'Welcome to DukanHisab, {{name}}!',
            'email_body' => "Hello {{name}},\n\nWelcome to **DukanHisab!** 🎉\n\nThank you for registering. We're excited to help you manage your business more efficiently.\n\nWith DukanHisab, you can manage your sales, purchases, products, inventory, customer and supplier records, expenses and business reports from one place.\n\n**Your next step:** Add your shop details and products to get started.\n\n**Download the Mobile App:**\nhttps://play.google.com/store/apps/details?id=com.app.dukanhisab\n\n**Access the Web Dashboard:**\nhttps://dukanhisab.in/shop/login\n\nWe wish you success on your business journey!\n\nRegards,\n**Team DukanHisab**\n*Apni Dukaan Ka Hisab, Ab Ek App Mein!*",
        ],

        self::EVENT_SHOP_ADDED => [
            'name' => 'Shop Added Successfully',
            'whatsapp' => "🎉 *Your Shop Has Been Added Successfully!*\n\nHello {{name}}, 👋\n\nYour shop *{{shop_name}}* is now added to DukanHisab. 🏪\n\nYou're ready to organize your business records in one place!\n\n✅ Add your products and prices\n✅ Record sales and purchases\n✅ Manage inventory\n✅ Maintain customer and supplier accounts\n✅ Track expenses and view reports\n\n🚀 *Next Step:* Add your products and start recording your daily transactions.\n\n📱 App: https://play.google.com/store/apps/details?id=com.app.dukanhisab\n\n💻 Dashboard: https://dukanhisab.in/shop/login\n\nWishing you growth and success!\n\n*Team DukanHisab* 💚",
            'email_subject' => '{{shop_name}} Has Been Added to DukanHisab!',
            'email_body' => "Hello {{name}},\n\nCongratulations! 🎉\n\nYour shop **{{shop_name}}** has been successfully added to DukanHisab.\n\nYou can now begin setting up your business records.\n\n**Recommended next steps:**\n\n1. Add your products and selling prices.\n2. Record your opening stock, where applicable.\n3. Add your customers and suppliers.\n4. Start recording sales, purchases and expenses.\n\n**Open DukanHisab:**\n\nMobile App: https://play.google.com/store/apps/details?id=com.app.dukanhisab\n\nWeb Dashboard: https://dukanhisab.in/shop/login\n\nThank you for choosing DukanHisab. We're happy to support your business journey!\n\nRegards,\n**Team DukanHisab**",
        ],

        self::EVENT_WEBSITE_PURCHASED => [
            'name' => 'Shop Website Plan Purchased',
            'whatsapp' => "🌐 *Thank You for Choosing a DukanHisab Shop Website!*\n\nHello {{name}}, 👋\n\nWe've received your shop website purchase for *{{shop_name}}*.\n\n📋 *Purchase Details*\n• Plan: {{website_plan}}\n• Amount: {{amount}}\n• Order ID: {{order_id}}\n• Payment Status: {{payment_status}}\n\nOnce your payment is confirmed, we'll proceed with the website activation or setup according to your plan.\n\n💻 Manage your shop:\nhttps://dukanhisab.in/shop/login\n\nNeed help? Contact the DukanHisab support team.\n\n*Team DukanHisab* 💚",
            'email_subject' => 'DukanHisab Shop Website Purchase — {{shop_name}}',
            'email_body' => "Hello {{name}},\n\nThank you for purchasing a shop website plan for **{{shop_name}}**!\n\nHere are your purchase details:\n\n- Website Plan: {{website_plan}}\n- Amount: {{amount}}\n- Order ID: {{order_id}}\n- Payment Status: {{payment_status}}\n- Purchase Date: {{purchase_date}}\n\nOnce payment is confirmed, your website will proceed to activation or setup based on the selected plan.\n\nYou can access your business dashboard here:\n\nhttps://dukanhisab.in/shop/login\n\nThank you for choosing DukanHisab to help bring your shop online.\n\nRegards,\n**Team DukanHisab**",
        ],

        self::EVENT_WHATSAPP_SERVICE_PURCHASED => [
            'name' => 'WhatsApp Service Pack Purchased',
            'whatsapp' => "🎉 *WhatsApp Service Purchase Received!*\n\nHello {{name}},\n\nThank you for choosing the WhatsApp service through DukanHisab! 💚\n\n📋 *Purchase Details*\n• Service: {{service_name}}\n• Plan: {{whatsapp_plan}}\n• Amount: {{amount}}\n• Order ID: {{order_id}}\n• Status: {{payment_status}}\n\nAfter payment confirmation, your purchased service or credits will be activated according to your plan.\n\nYou can manage your service from your DukanHisab dashboard:\n\n💻 https://dukanhisab.in/shop/login\n\n*Team DukanHisab*",
            'email_subject' => 'Your DukanHisab WhatsApp Service Purchase',
            'email_body' => "Hello {{name}},\n\nThank you for purchasing a WhatsApp service through DukanHisab.\n\n**Purchase Summary**\n\n- Service: {{service_name}}\n- Plan: {{whatsapp_plan}}\n- Amount: {{amount}}\n- Order ID: {{order_id}}\n- Payment Status: {{payment_status}}\n- Purchase Date: {{purchase_date}}\n\nYour service or credits will be activated after successful payment confirmation, according to your selected plan.\n\nYou can manage your WhatsApp service through your dashboard:\n\nhttps://dukanhisab.in/shop/login\n\nThank you for choosing DukanHisab!\n\nRegards,\n**Team DukanHisab**",
        ],

        self::EVENT_WHATSAPP_LOW_CREDITS => [
            'name' => 'WhatsApp Low Credits Warning',
            'whatsapp' => "⚠️ *Your WhatsApp Message Credits Are Running Low!*\n\nHello {{name}},\n\nYour DukanHisab WhatsApp account has fewer than 50 message credits remaining.\n\n📊 *Current Balance:* {{remaining_credits}} credits\n\nTo avoid interruptions to your WhatsApp messaging, please recharge your credits before they run out.\n\n💻 *Recharge & Manage Credits:*\nhttps://dukanhisab.in/shop/login\n\nStay connected with your customers without interruption!\n\n*Team DukanHisab* 💚",
            'email_subject' => 'Action Needed: Your WhatsApp Credits Are Running Low',
            'email_body' => "Hello {{name}},\n\nThis is a reminder that your DukanHisab WhatsApp account has fewer than 50 message credits remaining.\n\n**Your current balance:** {{remaining_credits}} credits\n\nWhen your available credits run out, messages that require those credits may stop sending until you recharge.\n\nWe recommend topping up your balance in advance to avoid interruptions.\n\n**Recharge Your WhatsApp Credits:**\nhttps://dukanhisab.in/shop/login\n\nThank you for using DukanHisab.\n\nRegards,\n**Team DukanHisab**",
        ],

        self::EVENT_PLAN_PURCHASED => [
            'name' => 'Subscription Plan Purchase Confirmed',
            'whatsapp' => "🎉 *Your DukanHisab Plan Purchase Is Confirmed!*\n\nHello {{name}},\n\nThank you for upgrading your business management experience with DukanHisab! 💚\n\n📋 *Subscription Details*\n• Plan: {{plan_name}}\n• Amount: {{amount}}\n• Valid Until: {{expiry_date}}\n• Order ID: {{order_id}}\n• Status: {{subscription_status}}\n\nYour plan benefits will be available according to the confirmed subscription details.\n\n🚀 Manage your subscription:\nhttps://dukanhisab.in/shop/login\n\nThank you for choosing DukanHisab!\n\n*Team DukanHisab*",
            'email_subject' => 'Your DukanHisab {{plan_name}} Plan Is Confirmed',
            'email_body' => "Hello {{name}},\n\nThank you for purchasing the **{{plan_name}} plan** on DukanHisab!\n\nYour subscription details are below:\n\n- Plan: {{plan_name}}\n- Amount Paid: {{amount}}\n- Purchase Date: {{purchase_date}}\n- Start Date: {{start_date}}\n- Valid Until: {{expiry_date}}\n- Order ID: {{order_id}}\n- Subscription Status: {{subscription_status}}\n\nYour plan benefits are available according to the subscription details shown in your account.\n\n**Manage Your Subscription:**\nhttps://dukanhisab.in/shop/login\n\nWe appreciate your trust in DukanHisab and look forward to supporting your business.\n\nRegards,\n**Team DukanHisab**",
        ],

        self::EVENT_PLAN_UPGRADED => [
            'name' => 'Subscription Plan Upgraded',
            'whatsapp' => "🚀 *Your DukanHisab Plan Has Been Upgraded!*\n\nHello {{name}},\n\nYour subscription has been upgraded successfully! 🎉\n\n📋 *Plan Details*\n• Previous Plan: {{old_plan}}\n• New Plan: {{new_plan}}\n• Amount Charged: {{amount}}\n• Effective From: {{effective_date}}\n• Valid Until: {{expiry_date}}\n\nYou can now access the features included in your new plan, according to its terms.\n\n💻 Manage your plan:\nhttps://dukanhisab.in/shop/login\n\nThank you for growing with DukanHisab! 💚\n\n*Team DukanHisab*",
            'email_subject' => 'Your DukanHisab Plan Has Been Upgraded',
            'email_body' => "Hello {{name}},\n\nGood news! Your DukanHisab subscription has been upgraded successfully.\n\n**Upgrade Summary**\n\n- Previous Plan: {{old_plan}}\n- New Plan: {{new_plan}}\n- Amount Charged: {{amount}}\n- Effective Date: {{effective_date}}\n- Subscription Valid Until: {{expiry_date}}\n\nThe features included in your new plan will be available according to its effective date and subscription terms.\n\nYou can review your subscription here:\n\nhttps://dukanhisab.in/shop/login\n\nThank you for continuing your journey with DukanHisab!\n\nRegards,\n**Team DukanHisab**",
        ],

        self::EVENT_PLAN_DOWNGRADED => [
            'name' => 'Subscription Plan Downgraded',
            'whatsapp' => "ℹ️ *Your DukanHisab Plan Change Is Confirmed*\n\nHello {{name}},\n\nYour request to change your subscription has been confirmed.\n\n📋 *Plan Details*\n• Current Plan: {{old_plan}}\n• New Plan: {{new_plan}}\n• Effective Date: {{effective_date}}\n• Current Plan Valid Until: {{current_expiry_date}}\n\n{{downgrade_effect_note}}\n\nPlease review the features and limits of your new plan before the change takes effect.\n\n💻 Manage your subscription:\nhttps://dukanhisab.in/shop/login\n\nNeed help? Contact DukanHisab Support.\n\n*Team DukanHisab*",
            'email_subject' => 'Confirmation of Your DukanHisab Plan Change',
            'email_body' => "Hello {{name}},\n\nThis email confirms your request to change your DukanHisab subscription from **{{old_plan}}** to **{{new_plan}}**.\n\n**Plan Change Details**\n\n- Current Plan: {{old_plan}}\n- New Plan: {{new_plan}}\n- Requested Date: {{request_date}}\n- Effective Date: {{effective_date}}\n- Current Plan Valid Until: {{current_expiry_date}}\n\n{{downgrade_effect_note}}\n\nPlease review the new plan's features, limits and billing terms before the effective date. If your current plan remains active until the end of its paid period, its benefits will continue until then.\n\n**Manage Your Subscription:**\nhttps://dukanhisab.in/shop/login\n\nIf you did not request this change, please contact our support team.\n\nRegards,\n**Team DukanHisab**",
        ],

        self::EVENT_PLAN_CANCELLED => [
            'name' => 'Subscription Plan Cancelled',
            'whatsapp' => "ℹ️ *Your DukanHisab Subscription Cancellation*\n\nHello {{name}},\n\nWe've received your request to cancel your *{{plan_name}}* subscription.\n\n📋 *Cancellation Details*\n• Plan: {{plan_name}}\n• Request Date: {{cancellation_date}}\n• Access Until: {{access_end_date}}\n• Cancellation Status: {{cancellation_status}}\n\n{{cancellation_effect_note}}\n\nYou can review your subscription or explore available plans here:\n\n💻 https://dukanhisab.in/shop/login\n\nWe're sorry to see you leave and thank you for being part of DukanHisab. 💚\n\nIf you need assistance, our support team is here to help.\n\n*Team DukanHisab*",
            'email_subject' => 'Confirmation of Your DukanHisab Subscription Cancellation',
            'email_body' => "Hello {{name}},\n\nThis email confirms that we have received your request to cancel your DukanHisab subscription.\n\n**Cancellation Details**\n\n- Plan: {{plan_name}}\n- Cancellation Request Date: {{cancellation_date}}\n- Effective Cancellation Date: {{effective_cancellation_date}}\n- Access Until: {{access_end_date}}\n- Cancellation Status: {{cancellation_status}}\n\n{{cancellation_effect_note}}\n\nIf your cancellation is scheduled for the end of your paid period, your current plan benefits will remain available until the stated access end date. If cancellation takes effect immediately, access will change according to the applicable plan terms.\n\nYou can review your account and available plans here:\n\nhttps://dukanhisab.in/shop/login\n\nThank you for choosing DukanHisab. If you have feedback about your experience, we'd appreciate hearing from you.\n\nRegards,\n**Team DukanHisab**",
        ],
    ];

    /**
     * Dispatch notification for a lifecycle event across WhatsApp and/or Email.
     * Never throws exceptions — errors are safely logged.
     */
    public function dispatch(
        string $event,
        User $user,
        array $variables = [],
        ?int $shopId = null,
        array $channels = ['whatsapp', 'email']
    ): array {
        if (!isset(self::TEMPLATES[$event])) {
            Log::warning("PlatformNotificationService: Unknown event {$event}");
            return ['whatsapp' => false, 'email' => false];
        }

        $template = self::TEMPLATES[$event];

        // Standard default variables if not explicitly provided
        $defaults = [
            'name' => $user->name ?: 'Customer',
            'email' => $user->email,
            'mobile' => $user->mobile,
            'purchase_date' => now()->format('d M Y'),
            'request_date' => now()->format('d M Y'),
            'effective_date' => now()->format('d M Y'),
            'cancellation_date' => now()->format('d M Y'),
            'effective_cancellation_date' => now()->format('d M Y'),
            'payment_status' => 'Confirmed',
            'subscription_status' => 'Active',
            'cancellation_status' => 'Confirmed',
            'downgrade_effect_note' => 'Your new plan benefits will become effective on the scheduled date.',
            'cancellation_effect_note' => 'Your plan benefits will remain active until the end of your current billing period.',
        ];

        $vars = array_merge($defaults, array_filter($variables, fn ($v) => $v !== null && $v !== ''));

        $results = ['whatsapp' => false, 'email' => false];

        // 1. Dispatch WhatsApp message
        if (in_array('whatsapp', $channels, true) && !empty($user->mobile)) {
            $message = $this->replacePlaceholders($template['whatsapp'], $vars);
            $results['whatsapp'] = $this->sendWhatsApp(
                phone: (string) $user->mobile,
                message: $message,
                user: $user,
                shopId: $shopId,
                event: $event,
                variables: $vars
            );
        }

        // 2. Dispatch Email
        if (in_array('email', $channels, true) && !empty($user->email)) {
            $subject = $this->replacePlaceholders($template['email_subject'], $vars);
            $body = $this->replacePlaceholders($template['email_body'], $vars);
            $results['email'] = $this->sendEmail(
                email: (string) $user->email,
                subject: $subject,
                body: $body,
                name: (string) $vars['name']
            );
        }

        return $results;
    }

    /**
     * Send WhatsApp notification safely.
     * Uses approved Meta template if available, or direct text as fallback.
     */
    public function sendWhatsApp(
        string $phone,
        string $message,
        ?User $user = null,
        ?int $shopId = null,
        ?string $event = null,
        array $variables = []
    ): bool {
        if (!WhatsAppClient::isEnabled()) {
            return false;
        }

        try {
            $client = WhatsAppClient::fromSettings();
            if (!$client->isConfigured()) {
                return false;
            }

            // Check if there is an approved Meta template for this event
            $template = !empty($event)
                ? \App\Models\WhatsAppTemplate::where('key', $event)->where('language', 'en')->first()
                : null;

            $isMetaApproved = $template
                && $template->meta_status === 'APPROVED'
                && !empty($template->meta_template_name);

            if ($isMetaApproved) {
                $components = $template->buildComponents($variables);
                $providerId = $client->sendTemplate($phone, $template->meta_template_name, $template->meta_language_code, $components);
                $templateNameUsed = $template->meta_template_name;
            } else {
                $providerId = $client->sendText($phone, $message);
                $templateNameUsed = $template?->meta_template_name ?? ($event ?? 'plain_text');
            }

            // If shop context is available, log to WhatsAppMessageLog
            $targetShopId = $shopId ?? $user?->shops()->first()?->id;
            if ($targetShopId) {
                try {
                    WhatsAppMessageLog::create([
                        'shop_id' => $targetShopId,
                        'event' => $event ?? 'platform_notification',
                        'recipient_type' => 'customer',
                        'recipient_id' => $user?->id,
                        'phone' => WhatsAppClient::normalizePhone($phone) ?? $phone,
                        'template_name' => $templateNameUsed,
                        'language' => $template?->language ?? 'en',
                        'payload' => ['text' => $message, 'values' => $variables],
                        'status' => 'sent',
                        'provider_message_id' => $providerId,
                        'sent_at' => now(),
                    ]);
                } catch (\Throwable $logEx) {
                    Log::warning('WhatsAppMessageLog recording failed: ' . $logEx->getMessage());
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("Platform WhatsApp notification failed to {$phone}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Email notification safely.
     */
    public function sendEmail(string $email, string $subject, string $body, string $name = 'User'): bool
    {
        try {
            $htmlContent = $this->markdownToHtml($body);

            Mail::send('emails.platform-notification', [
                'subject' => $subject,
                'htmlContent' => $htmlContent,
                'name' => $name,
            ], function ($message) use ($email, $subject, $name) {
                $message->to($email, $name)->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error("Platform Email notification failed to {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replaces {{variable}} placeholders with values.
     */
    public function replacePlaceholders(string $text, array $variables): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($matches) use ($variables) {
            $key = $matches[1];
            return isset($variables[$key]) ? (string) $variables[$key] : $matches[0];
        }, $text);
    }

    /**
     * Converts basic markdown body text into clean, safe email HTML.
     */
    public function markdownToHtml(string $text): string
    {
        // Escape raw HTML entities first
        $html = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Strong: **text**
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);

        // Italic: *text* (avoiding bullet points)
        $html = preg_replace('/(?<!\w)\*([^\*\n]+?)\*(?!\w)/', '<em>$1</em>', $html);

        // Bullet lists: Lines starting with "- " or "• "
        $html = preg_replace('/^[•\-]\s+(.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/((?:<li>.+?<\/li>\s*)+)/s', '<ul style="margin: 12px 0 16px 0; padding-left: 20px;">$1</ul>', $html);

        // Numbered lists: Lines starting with "1. "
        $html = preg_replace('/^\d+\.\s+(.+)$/m', '<li class="num-item">$1</li>', $html);
        $html = preg_replace('/((?:<li class="num-item">.+?<\/li>\s*)+)/s', '<ol style="margin: 12px 0 16px 0; padding-left: 20px;">$1</ol>', $html);
        $html = str_replace(' class="num-item"', '', $html);

        // URLs to clickable links
        $html = preg_replace(
            '/(https?:\/\/[^\s<]+)/',
            '<a href="$1" style="color: #0f766e; font-weight: 500; word-break: break-all;">$1</a>',
            $html
        );

        // Double line break to paragraphs, single line break to <br>
        $paragraphs = preg_split('/\n{2,}/', $html);
        $formatted = [];
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            if (str_starts_with($p, '<ul') || str_starts_with($p, '<ol')) {
                $formatted[] = $p;
            } else {
                $formatted[] = '<p style="margin: 0 0 14px 0;">' . nl2br($p) . '</p>';
            }
        }

        return implode("\n", $formatted);
    }

    /* -------------------------------------------------------------------------- */
    /*                         SPECIFIC LIFECYCLE HELPERS                         */
    /* -------------------------------------------------------------------------- */

    /**
     * 1.1 User registers successfully
     */
    public function notifyUserRegistered(User $user): array
    {
        return $this->dispatch(self::EVENT_USER_REGISTERED, $user, [
            'name' => $user->name ?: 'Entrepreneur',
        ]);
    }

    /**
     * 1.2 Shop added successfully
     */
    public function notifyShopAdded(User $user, Shop|string $shop): array
    {
        $shopName = $shop instanceof Shop ? $shop->name : (string) $shop;
        $shopId = $shop instanceof Shop ? $shop->id : null;

        return $this->dispatch(self::EVENT_SHOP_ADDED, $user, [
            'name' => $user->name ?: 'Shop Owner',
            'shop_name' => $shopName,
        ], $shopId);
    }

    /**
     * 2.1 Shop website plan purchased
     */
    public function notifyWebsitePurchased(User $user, array $details = []): array
    {
        $shop = $user->shops()->first();
        $shopName = $details['shop_name'] ?? $shop?->name ?? 'Your Shop';

        return $this->dispatch(self::EVENT_WEBSITE_PURCHASED, $user, array_merge([
            'name' => $user->name ?: 'Customer',
            'shop_name' => $shopName,
            'website_plan' => $details['website_plan'] ?? 'Shop Online Website',
            'amount' => isset($details['amount']) ? '₹' . number_format((float) $details['amount'], 2) : '₹0.00',
            'order_id' => $details['order_id'] ?? 'N/A',
            'payment_status' => $details['payment_status'] ?? 'Confirmed',
            'purchase_date' => $details['purchase_date'] ?? now()->format('d M Y'),
        ], $details), $shop?->id);
    }

    /**
     * 3.1 WhatsApp service pack purchased
     */
    public function notifyWhatsAppServicePurchased(User $user, array $details = []): array
    {
        $shop = $user->shops()->first();

        return $this->dispatch(self::EVENT_WHATSAPP_SERVICE_PURCHASED, $user, array_merge([
            'name' => $user->name ?: 'Customer',
            'service_name' => $details['service_name'] ?? 'WhatsApp Messaging',
            'whatsapp_plan' => $details['whatsapp_plan'] ?? $details['pack_name'] ?? 'Credit Pack',
            'amount' => isset($details['amount']) ? '₹' . number_format((float) $details['amount'], 2) : '₹0.00',
            'order_id' => $details['order_id'] ?? 'N/A',
            'payment_status' => $details['payment_status'] ?? 'Confirmed',
            'purchase_date' => $details['purchase_date'] ?? now()->format('d M Y'),
        ], $details), $shop?->id);
    }

    /**
     * 3.2 WhatsApp message credits fall below 50
     */
    public function notifyWhatsAppLowCredits(User $user, int $remainingCredits, ?Shop $shop = null): array
    {
        return $this->dispatch(self::EVENT_WHATSAPP_LOW_CREDITS, $user, [
            'name' => $user->name ?: 'Customer',
            'remaining_credits' => (string) $remainingCredits,
        ], $shop?->id);
    }

    /**
     * 4.1 Plan purchase confirmed
     */
    public function notifyPlanPurchased(User $user, array $details = []): array
    {
        $shop = $user->shops()->first();

        return $this->dispatch(self::EVENT_PLAN_PURCHASED, $user, array_merge([
            'name' => $user->name ?: 'Customer',
            'plan_name' => $details['plan_name'] ?? 'Premium',
            'amount' => isset($details['amount']) ? '₹' . number_format((float) $details['amount'], 2) : '₹0.00',
            'purchase_date' => $details['purchase_date'] ?? now()->format('d M Y'),
            'start_date' => $details['start_date'] ?? now()->format('d M Y'),
            'expiry_date' => $details['expiry_date'] ?? 'Lifetime',
            'order_id' => $details['order_id'] ?? 'N/A',
            'subscription_status' => $details['subscription_status'] ?? 'Active',
        ], $details), $shop?->id);
    }

    /**
     * 4.2 Plan upgraded
     */
    public function notifyPlanUpgraded(User $user, array $details = []): array
    {
        $shop = $user->shops()->first();

        return $this->dispatch(self::EVENT_PLAN_UPGRADED, $user, array_merge([
            'name' => $user->name ?: 'Customer',
            'old_plan' => $details['old_plan'] ?? 'Free',
            'new_plan' => $details['new_plan'] ?? 'Premium',
            'amount' => isset($details['amount']) ? '₹' . number_format((float) $details['amount'], 2) : '₹0.00',
            'effective_date' => $details['effective_date'] ?? now()->format('d M Y'),
            'expiry_date' => $details['expiry_date'] ?? 'Lifetime',
        ], $details), $shop?->id);
    }

    /**
     * 4.3 Plan downgraded
     */
    public function notifyPlanDowngraded(User $user, array $details = []): array
    {
        $shop = $user->shops()->first();

        return $this->dispatch(self::EVENT_PLAN_DOWNGRADED, $user, array_merge([
            'name' => $user->name ?: 'Customer',
            'old_plan' => $details['old_plan'] ?? 'Premium',
            'new_plan' => $details['new_plan'] ?? 'Free',
            'request_date' => $details['request_date'] ?? now()->format('d M Y'),
            'effective_date' => $details['effective_date'] ?? now()->format('d M Y'),
            'current_expiry_date' => $details['current_expiry_date'] ?? now()->format('d M Y'),
            'downgrade_effect_note' => $details['downgrade_effect_note'] ?? 'Your new plan limits will take effect on the effective date.',
        ], $details), $shop?->id);
    }

    /**
     * 4.4 Plan cancelled
     */
    public function notifyPlanCancelled(User $user, array $details = []): array
    {
        $shop = $user->shops()->first();

        return $this->dispatch(self::EVENT_PLAN_CANCELLED, $user, array_merge([
            'name' => $user->name ?: 'Customer',
            'plan_name' => $details['plan_name'] ?? 'Premium',
            'cancellation_date' => $details['cancellation_date'] ?? now()->format('d M Y'),
            'effective_cancellation_date' => $details['effective_cancellation_date'] ?? now()->format('d M Y'),
            'access_end_date' => $details['access_end_date'] ?? now()->format('d M Y'),
            'cancellation_status' => $details['cancellation_status'] ?? 'Cancelled',
            'cancellation_effect_note' => $details['cancellation_effect_note'] ?? 'Your paid access will continue until the stated access end date.',
        ], $details), $shop?->id);
    }
}
