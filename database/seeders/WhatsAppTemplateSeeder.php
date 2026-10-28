<?php

namespace Database\Seeders;

use App\Models\WhatsAppTemplate;
use Illuminate\Database\Seeder;

class WhatsAppTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ==========================================
            // 1. SALE INVOICE (Customer)
            // ==========================================
            [
                'key' => 'sale_invoice',
                'language' => 'en',
                'meta_template_name' => 'sale_invoice_en',
                'meta_language_code' => 'en_US',
                'body' => "Hello *{{1}}*,\n\nYour invoice *{{2}}* from *{{3}}* for *{{4}}* is ready. Current pending balance: *{{5}}*.\n\nThank you for shopping with us! For any queries, contact: *{{6}}* for assistance.",
                'variables' => ['party_name', 'invoice_no', 'shop_name', 'amount', 'due_amount', 'shop_mobile'],
                'has_document' => true,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'sale_invoice',
                'language' => 'gu',
                'meta_template_name' => 'sale_invoice_gu',
                'meta_language_code' => 'gu',
                'body' => "નમસ્તે *{{1}}*,\n\n*{{3}}* તરફથી આપનું બિલ *{{2}}* (રકમ: *{{4}}*) બની ગયું છે. હાલની બાકી રકમ: *{{5}}*.\n\nકોઈપણ પ્રશ્ન માટે સંપર્ક કરો: *{{6}}*. અમારી સાથે ખરીદી કરવા બદલ આભાર!",
                'variables' => ['party_name', 'invoice_no', 'shop_name', 'amount', 'due_amount', 'shop_mobile'],
                'has_document' => true,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'sale_invoice',
                'language' => 'hi',
                'meta_template_name' => 'sale_invoice_hi',
                'meta_language_code' => 'hi',
                'body' => "नमस्ते *{{1}}*,\n\n*{{3}}* से आपका बिल *{{2}}* (राशि: *{{4}}*) तैयार हो गया है। कुल बकाया राशि: *{{5}}*।\n\nकिसी भी सहायता के लिए संपर्क करें: *{{6}}*। हमारे साथ खरीदारी के लिए धन्यवाद!",
                'variables' => ['party_name', 'invoice_no', 'shop_name', 'amount', 'due_amount', 'shop_mobile'],
                'has_document' => true,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 2. PAYMENT RECEIVED (Customer)
            // ==========================================
            [
                'key' => 'payment_received',
                'language' => 'en',
                'meta_template_name' => 'payment_received_en',
                'meta_language_code' => 'en_US',
                'body' => "Dear *{{1}}*,\n\nWe have received payment of *{{2}}* on *{{3}}* for *{{4}}*. Remaining balance: *{{5}}*.\n\nThank you!",
                'variables' => ['party_name', 'amount', 'date', 'shop_name', 'due_amount'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'payment_received',
                'language' => 'gu',
                'meta_template_name' => 'payment_received_gu',
                'meta_language_code' => 'gu',
                'body' => "નમસ્તે *{{1}}*,\n\n*{{4}}* માં તારીખ *{{3}}* ના રોજ આપની *{{2}}* ની ચુકવણી મળી ગયેલ છે. હવે બાકી રકમ: *{{5}}*.\n\nઆભાર!",
                'variables' => ['party_name', 'amount', 'date', 'shop_name', 'due_amount'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'payment_received',
                'language' => 'hi',
                'meta_template_name' => 'payment_received_hi',
                'meta_language_code' => 'hi',
                'body' => "नमस्ते *{{1}}*,\n\n*{{4}}* में दिनांक *{{3}}* को आपका *{{2}}* का भुगतान प्राप्त हो गया है। शेष बकाया राशि: *{{5}}*।\n\nधन्यवाद!",
                'variables' => ['party_name', 'amount', 'date', 'shop_name', 'due_amount'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 3. DUE REMINDER (Customer)
            // ==========================================
            [
                'key' => 'due_reminder',
                'language' => 'en',
                'meta_template_name' => 'due_reminder_en',
                'meta_language_code' => 'en_US',
                'body' => "Dear *{{1}}*,\n\nThis is a gentle payment reminder from *{{2}}* that your pending balance is *{{3}}*. Kindly clear the payment at the earliest.\n\nFor any queries, please contact *{{4}}*. Thank you!",
                'variables' => ['party_name', 'shop_name', 'due_amount', 'shop_mobile'],
                'has_document' => false,
                'has_pay_button' => true,
                'status' => 'active',
            ],
            [
                'key' => 'due_reminder',
                'language' => 'gu',
                'meta_template_name' => 'due_reminder_gu',
                'meta_language_code' => 'gu',
                'body' => "નમસ્તે *{{1}}*,\n\n*{{2}}* તરફથી વિનંતી કે આપણી પાસે *{{3}}* ની રકમ બાકી છે. કૃપા કરીને વહેલી તકે ચુકવણી કરવા નમ્ર વિનંતી.\n\nકોઈપણ પ્રશ્ન માટે સંપર્ક કરો: *{{4}}*. આભાર!",
                'variables' => ['party_name', 'shop_name', 'due_amount', 'shop_mobile'],
                'has_document' => false,
                'has_pay_button' => true,
                'status' => 'active',
            ],
            [
                'key' => 'due_reminder',
                'language' => 'hi',
                'meta_template_name' => 'due_reminder_hi',
                'meta_language_code' => 'hi',
                'body' => "नमस्ते *{{1}}*,\n\n*{{2}}* की तरफ से विनम्र अनुस्मारक कि आपकी कुल बकाया राशि *{{3}}* है। कृपया जल्द से जल्द भुगतान करने की कृपा करें।\n\nकिसी भी जानकारी के लिए संपर्क करें: *{{4}}*। धन्यवाद!",
                'variables' => ['party_name', 'shop_name', 'due_amount', 'shop_mobile'],
                'has_document' => false,
                'has_pay_button' => true,
                'status' => 'active',
            ],

            // ==========================================
            // 4. PURCHASE RECORD (Supplier)
            // ==========================================
            [
                'key' => 'purchase_record',
                'language' => 'en',
                'meta_template_name' => 'purchase_record_en',
                'meta_language_code' => 'en_US',
                'body' => "Hello *{{1}}*,\n\nPurchase entry *{{2}}* of *{{3}}* has been recorded at *{{4}}* on *{{5}}*.\n\nThank you!",
                'variables' => ['party_name', 'invoice_no', 'amount', 'shop_name', 'date'],
                'has_document' => true,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'purchase_record',
                'language' => 'gu',
                'meta_template_name' => 'purchase_record_gu',
                'meta_language_code' => 'gu',
                'body' => "નમસ્તે *{{1}}*,\n\n*{{4}}* ખાતે તારીખ *{{5}}* ના રોજ આપનું ખરીદી બિલ *{{2}}* (રકમ: *{{3}}*) નોંધવામાં આવ્યું છે.\n\nઆભાર!",
                'variables' => ['party_name', 'invoice_no', 'amount', 'shop_name', 'date'],
                'has_document' => true,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'purchase_record',
                'language' => 'hi',
                'meta_template_name' => 'purchase_record_hi',
                'meta_language_code' => 'hi',
                'body' => "नमस्ते *{{1}}*,\n\n*{{4}}* में दिनांक *{{5}}* को आपका खरीद बिल *{{2}}* (राशि: *{{3}}*) दर्ज कर लिया गया है।\n\nधन्यवाद!",
                'variables' => ['party_name', 'invoice_no', 'amount', 'shop_name', 'date'],
                'has_document' => true,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 5. SUPPLIER PAYMENT (Supplier)
            // ==========================================
            [
                'key' => 'supplier_payment',
                'language' => 'en',
                'meta_template_name' => 'supplier_payment_en',
                'meta_language_code' => 'en_US',
                'body' => "Dear *{{1}}*,\n\n*{{2}}* has made a payment of *{{3}}* to you on *{{4}}*. Remaining balance: *{{5}}*.\n\nThank you!",
                'variables' => ['party_name', 'shop_name', 'amount', 'date', 'due_amount'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'supplier_payment',
                'language' => 'gu',
                'meta_template_name' => 'supplier_payment_gu',
                'meta_language_code' => 'gu',
                'body' => "નમસ્તે *{{1}}*,\n\n*{{2}}* તરફથી આપને તારીખ *{{4}}* ના રોજ *{{3}}* ની ચુકવણી કરવામાં આવેલ છે. બાકી રકમ: *{{5}}*.\n\nઆભાર!",
                'variables' => ['party_name', 'shop_name', 'amount', 'date', 'due_amount'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'supplier_payment',
                'language' => 'hi',
                'meta_template_name' => 'supplier_payment_hi',
                'meta_language_code' => 'hi',
                'body' => "नमस्ते *{{1}}*,\n\n*{{2}}* द्वारा आपको दिनांक *{{4}}* को *{{3}}* का भुगतान कर दिया गया है। शेष बकाया: *{{5}}*।\n\nधन्यवाद!",
                'variables' => ['party_name', 'shop_name', 'amount', 'date', 'due_amount'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 6. SUPPLIER DUE (Supplier)
            // ==========================================
            [
                'key' => 'supplier_due',
                'language' => 'en',
                'meta_template_name' => 'supplier_due_en',
                'meta_language_code' => 'en_US',
                'body' => "Hello *{{1}}*,\n\nAccount statement from *{{2}}*: Our current pending balance with you is *{{3}}* as of *{{4}}*.\n\nFor any queries, please contact *{{5}}*. Thank you!",
                'variables' => ['party_name', 'shop_name', 'due_amount', 'date', 'shop_mobile'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'supplier_due',
                'language' => 'gu',
                'meta_template_name' => 'supplier_due_gu',
                'meta_language_code' => 'gu',
                'body' => "નમસ્તે *{{1}}*,\n\n*{{2}}* તરફથી ખાતાનું સ્ટેટમેન્ટ: તારીખ *{{4}}* મુજબ આપણી બાકી રકમ *{{3}}* છે.\n\nકોઈપણ પ્રશ્ન માટે સંપર્ક કરો: *{{5}}*. આભાર!",
                'variables' => ['party_name', 'shop_name', 'due_amount', 'date', 'shop_mobile'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            [
                'key' => 'supplier_due',
                'language' => 'hi',
                'meta_template_name' => 'supplier_due_hi',
                'meta_language_code' => 'hi',
                'body' => "नमस्ते *{{1}}*,\n\n*{{2}}* की तरफ से खाते का विवरण: दिनांक *{{4}}* के अनुसार हमारी बकाया राशि *{{3}}* है।\n\nकिसी भी सहायता के लिए संपर्क करें: *{{5}}*। धन्यवाद!",
                'variables' => ['party_name', 'shop_name', 'due_amount', 'date', 'shop_mobile'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],
            // ==========================================
            // 7. USER REGISTRATION WELCOME (Platform)
            // ==========================================
            [
                'key' => 'user_registered',
                'language' => 'en',
                'meta_template_name' => 'user_registered_en',
                'meta_language_code' => 'en_US',
                'body' => "🎉 *Welcome to DukanHisab, {{1}}!*\n\nThank you for joining us! 💚\n\nDukanHisab helps you manage your business records in one place, including sales, purchases, products, inventory, customers, suppliers, expenses and reports.\n\n🚀 Ready to get started? Set up your shop and start managing your business digitally.\n\n📱 *Download App:*\nhttps://play.google.com/store/apps/details?id=com.app.dukanhisab\n\n💻 *Web Dashboard:*\nhttps://dukanhisab.in/shop/login\n\n*DukanHisab — Apni Dukaan Ka Hisab, Ab Ek App Mein!* 🏪",
                'variables' => ['name'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 8. SHOP ADDED (Platform)
            // ==========================================
            [
                'key' => 'shop_added',
                'language' => 'en',
                'meta_template_name' => 'shop_added_en',
                'meta_language_code' => 'en_US',
                'body' => "🎉 *Your Shop Has Been Added Successfully!*\n\nHello {{1}}, 👋\n\nYour shop *{{2}}* is now added to DukanHisab. 🏪\n\nYou're ready to organize your business records in one place!\n\n✅ Add your products and prices\n✅ Record sales and purchases\n✅ Manage inventory\n✅ Maintain customer and supplier accounts\n✅ Track expenses and view reports\n\n🚀 *Next Step:* Add your products and start recording your daily transactions.\n\n📱 App: https://play.google.com/store/apps/details?id=com.app.dukanhisab\n\n💻 Dashboard: https://dukanhisab.in/shop/login\n\nWishing you growth and success!\n\n*Team DukanHisab* 💚",
                'variables' => ['name', 'shop_name'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 9. SHOP WEBSITE PURCHASED (Platform)
            // ==========================================
            [
                'key' => 'website_purchased',
                'language' => 'en',
                'meta_template_name' => 'website_purchased_en',
                'meta_language_code' => 'en_US',
                'body' => "🌐 *Thank You for Choosing a DukanHisab Shop Website!*\n\nHello {{1}}, 👋\n\nWe've received your shop website purchase for *{{2}}*.\n\n📋 *Purchase Details*\n• Plan: {{3}}\n• Amount: {{4}}\n• Order ID: {{5}}\n• Payment Status: {{6}}\n\nOnce your payment is confirmed, we'll proceed with the website activation or setup according to your plan.\n\n💻 Manage your shop:\nhttps://dukanhisab.in/shop/login\n\nNeed help? Contact the DukanHisab support team.\n\n*Team DukanHisab* 💚",
                'variables' => ['name', 'shop_name', 'website_plan', 'amount', 'order_id', 'payment_status'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 10. WHATSAPP SERVICE PURCHASED (Platform)
            // ==========================================
            [
                'key' => 'whatsapp_service_purchased',
                'language' => 'en',
                'meta_template_name' => 'whatsapp_service_purchased_en',
                'meta_language_code' => 'en_US',
                'body' => "🎉 *WhatsApp Service Purchase Received!*\n\nHello {{1}},\n\nThank you for choosing the WhatsApp service through DukanHisab! 💚\n\n📋 *Purchase Details*\n• Service: {{2}}\n• Plan: {{3}}\n• Amount: {{4}}\n• Order ID: {{5}}\n• Status: {{6}}\n\nAfter payment confirmation, your purchased service or credits will be activated according to your plan.\n\nYou can manage your service from your DukanHisab dashboard:\n\n💻 https://dukanhisab.in/shop/login\n\n*Team DukanHisab*",
                'variables' => ['name', 'service_name', 'whatsapp_plan', 'amount', 'order_id', 'payment_status'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 11. WHATSAPP LOW CREDITS (Platform)
            // ==========================================
            [
                'key' => 'whatsapp_low_credits',
                'language' => 'en',
                'meta_template_name' => 'whatsapp_low_credits_en',
                'meta_language_code' => 'en_US',
                'body' => "⚠️ *Your WhatsApp Message Credits Are Running Low!*\n\nHello {{1}},\n\nYour DukanHisab WhatsApp account has fewer than 50 message credits remaining.\n\n📊 *Current Balance:* {{2}} credits\n\nTo avoid interruptions to your WhatsApp messaging, please recharge your credits before they run out.\n\n💻 *Recharge & Manage Credits:*\nhttps://dukanhisab.in/shop/login\n\nStay connected with your customers without interruption!\n\n*Team DukanHisab* 💚",
                'variables' => ['name', 'remaining_credits'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 12. PLAN PURCHASED (Platform)
            // ==========================================
            [
                'key' => 'plan_purchased',
                'language' => 'en',
                'meta_template_name' => 'plan_purchased_en',
                'meta_language_code' => 'en_US',
                'body' => "🎉 *Your DukanHisab Plan Purchase Is Confirmed!*\n\nHello {{1}},\n\nThank you for upgrading your business management experience with DukanHisab! 💚\n\n📋 *Subscription Details*\n• Plan: {{2}}\n• Amount: {{3}}\n• Valid Until: {{4}}\n• Order ID: {{5}}\n• Status: {{6}}\n\nYour plan benefits will be available according to the confirmed subscription details.\n\n🚀 Manage your subscription:\nhttps://dukanhisab.in/shop/login\n\nThank you for choosing DukanHisab!\n\n*Team DukanHisab*",
                'variables' => ['name', 'plan_name', 'amount', 'expiry_date', 'order_id', 'subscription_status'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 13. PLAN UPGRADED (Platform)
            // ==========================================
            [
                'key' => 'plan_upgraded',
                'language' => 'en',
                'meta_template_name' => 'plan_upgraded_en',
                'meta_language_code' => 'en_US',
                'body' => "🚀 *Your DukanHisab Plan Has Been Upgraded!*\n\nHello {{1}},\n\nYour subscription has been upgraded successfully! 🎉\n\n📋 *Plan Details*\n• Previous Plan: {{2}}\n• New Plan: {{3}}\n• Amount Charged: {{4}}\n• Effective From: {{5}}\n• Valid Until: {{6}}\n\nYou can now access the features included in your new plan, according to its terms.\n\n💻 Manage your plan:\nhttps://dukanhisab.in/shop/login\n\nThank you for growing with DukanHisab! 💚\n\n*Team DukanHisab*",
                'variables' => ['name', 'old_plan', 'new_plan', 'amount', 'effective_date', 'expiry_date'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 14. PLAN DOWNGRADED (Platform)
            // ==========================================
            [
                'key' => 'plan_downgraded',
                'language' => 'en',
                'meta_template_name' => 'plan_downgraded_en',
                'meta_language_code' => 'en_US',
                'body' => "ℹ️ *Your DukanHisab Plan Change Is Confirmed*\n\nHello {{1}},\n\nYour request to change your subscription has been confirmed.\n\n📋 *Plan Details*\n• Current Plan: {{2}}\n• New Plan: {{3}}\n• Effective Date: {{4}}\n• Current Plan Valid Until: {{5}}\n\n{{6}}\n\nPlease review the features and limits of your new plan before the change takes effect.\n\n💻 Manage your subscription:\nhttps://dukanhisab.in/shop/login\n\nNeed help? Contact DukanHisab Support.\n\n*Team DukanHisab*",
                'variables' => ['name', 'old_plan', 'new_plan', 'effective_date', 'current_expiry_date', 'downgrade_effect_note'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],

            // ==========================================
            // 15. PLAN CANCELLED (Platform)
            // ==========================================
            [
                'key' => 'plan_cancelled',
                'language' => 'en',
                'meta_template_name' => 'plan_cancelled_en',
                'meta_language_code' => 'en_US',
                'body' => "ℹ️ *Your DukanHisab Subscription Cancellation*\n\nHello {{1}},\n\nWe've received your request to cancel your *{{2}}* subscription.\n\n📋 *Cancellation Details*\n• Plan: {{2}}\n• Request Date: {{3}}\n• Access Until: {{4}}\n• Cancellation Status: {{5}}\n\n{{6}}\n\nYou can review your subscription or explore available plans here:\n\n💻 https://dukanhisab.in/shop/login\n\nWe're sorry to see you leave and thank you for being part of DukanHisab. 💚\n\nIf you need assistance, our support team is here to help.\n\n*Team DukanHisab*",
                'variables' => ['name', 'plan_name', 'cancellation_date', 'access_end_date', 'cancellation_status', 'cancellation_effect_note'],
                'has_document' => false,
                'has_pay_button' => false,
                'status' => 'active',
            ],
        ];

        foreach ($templates as $data) {
            WhatsAppTemplate::updateOrCreate(
                ['key' => $data['key'], 'language' => $data['language']],
                $data
            );
        }
    }
}
