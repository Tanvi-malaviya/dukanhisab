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
                'body' => "Hello {{1}},\n\nYour invoice {{2}} from {{3}} for {{4}} is ready. Current pending balance: {{5}}.\n\nThank you for shopping with us!\nContact: {{6}}",
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
                'body' => "નમસ્તે {{1}},\n\n{{3}} તરફથી આપનું બિલ {{2}} (રકમ: {{4}}) બની ગયું છે. હાલની બાકી રકમ: {{5}}.\n\nઅમારી સાથે ખરીદી કરવા બદલ આભાર!\nસંપર્ક: {{6}}",
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
                'body' => "नमस्ते {{1}},\n\n{{3}} से आपका बिल {{2}} (राशि: {{4}}) तैयार हो गया है। कुल बकाया राशि: {{5}}।\n\nहमारे साथ खरीदारी करने के लिए धन्यवाद!\nसंपर्क: {{6}}",
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
                'body' => "Dear {{1}},\n\nWe have received payment of {{2}} on {{3}} for {{4}}. Remaining balance: {{5}}.\n\nThank you!",
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
                'body' => "નમસ્તે {{1}},\n\n{{4}} માં તારીખ {{3}} ના રોજ આપની {{2}} ની ચુકવણી મળી ગયેલ છે. હવે બાકી રકમ: {{5}}.\n\nઆભાર!",
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
                'body' => "नमस्ते {{1}},\n\n{{4}} में दिनांक {{3}} को आपका {{2}} का भुगतान प्राप्त हो गया है। शेष बकाया राशि: {{5}}।\n\nधन्यवाद!",
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
                'body' => "Dear {{1}},\n\nThis is a gentle payment reminder from {{2}} that your pending balance is {{3}}. Kindly clear the payment at the earliest.\n\nContact: {{4}}",
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
                'body' => "નમસ્તે {{1}},\n\n{{2}} તરફથી વિનંતી કે આપણી પાસે {{3}} ની રકમ બાકી છે. કૃપા કરીને વહેલી તકે ચુકવણી કરવા નમ્ર વિનંતી.\n\nસંપર્ક: {{4}}",
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
                'body' => "नमस्ते {{1}},\n\n{{2}} की तरफ से विनम्र अनुस्मारक कि आपकी कुल बकाया राशि {{3}} है। कृपया जल्द से जल्द भुगतान करने की कृपा करें।\n\nसंपर्क: {{4}}",
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
                'body' => "Hello {{1}},\n\nPurchase entry {{2}} of {{3}} has been recorded at {{4}} on {{5}}.\n\nThank you!",
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
                'body' => "નમસ્તે {{1}},\n\n{{4}} ખાતે તારીખ {{5}} ના રોજ આપનું ખરીદી બિલ {{2}} (રકમ: {{3}}) નોંધવામાં આવ્યું છે.\n\nઆભાર!",
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
                'body' => "नमस्ते {{1}},\n\n{{4}} में दिनांक {{5}} को आपका खरीद बिल {{2}} (राशि: {{3}}) दर्ज कर लिया गया है।\n\nधन्यवाद!",
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
                'body' => "Dear {{1}},\n\n{{2}} has made a payment of {{3}} to you on {{4}}. Remaining balance: {{5}}.\n\nThank you!",
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
                'body' => "નમસ્તે {{1}},\n\n{{2}} તરફથી આપને તારીખ {{4}} ના રોજ {{3}} ની ચુકવણી કરવામાં આવેલ છે. બાકી રકમ: {{5}}.\n\nઆભાર!",
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
                'body' => "नमस्ते {{1}},\n\n{{2}} द्वारा आपको दिनांक {{4}} को {{3}} का भुगतान कर दिया गया है। शेष बकाया: {{5}}।\n\nधन्यवाद!",
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
                'body' => "Hello {{1}},\n\nAccount statement from {{2}}: Our current pending balance with you is {{3}} as of {{4}}.\n\nContact: {{5}}",
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
                'body' => "નમસ્તે {{1}},\n\n{{2}} તરફથી ખાતાનું સ્ટેટમેન્ટ: તારીખ {{4}} મુજબ આપણી બાકી રકમ {{3}} છે.\n\nસંપર્ક: {{5}}",
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
                'body' => "नमस्ते {{1}},\n\n{{2}} की तरफ से खाते का विवरण: दिनांक {{4}} के अनुसार हमारी बकाया राशि {{3}} है।\n\nसंपर्क: {{5}}",
                'variables' => ['party_name', 'shop_name', 'due_amount', 'date', 'shop_mobile'],
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
