{{-- SETTINGS PANEL --}}
<div x-show="page === 'settings'" class="space-y-6" x-data="{
        settingsTab: 'shop',
        showPreviewModal: false,
        shopUpdateForm: {
            name: shop ? shop.name : '',
            owner_name: user ? user.name : '',
            mobile: shop ? shop.mobile : '',
            email: shop ? shop.email : '',
            gst_number: shop ? shop.gst_number : '',
            address: shop ? shop.address : '',
            city: shop ? shop.city : '',
            state: shop ? shop.state : '',
            pincode: shop ? shop.pincode : '',
            currency: shop && shop.currency ? shop.currency : 'INR',
            upi_id: shop ? shop.upi_id : '',
            bank_details: shop ? shop.bank_details : '',
            invoice_footer: shop ? shop.invoice_footer : '',
            website_settings: (shop && shop.website_settings) ? Object.assign({ enabled: false, subdomain: '', theme_color: '#0F766E', seo_title: '', seo_description: '', social_facebook: '', social_instagram: '', social_twitter: '', social_whatsapp: '', show_catalog: true, show_contact: true, show_inquiry: true, about_us: '' }, shop.website_settings) : { enabled: false, subdomain: '', theme_color: '#0F766E', seo_title: '', seo_description: '', social_facebook: '', social_instagram: '', social_twitter: '', social_whatsapp: '', show_catalog: true, show_contact: true, show_inquiry: true, about_us: '' }
        },
        logoPreview: '',
        logoFile: null,
        onSettingsLogoChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.logoFile = file;
                this.logoPreview = URL.createObjectURL(file);
            }
        },
        signaturePreview: '',
        signatureFile: null,
        onSettingsSignatureChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.signatureFile = file;
                this.signaturePreview = URL.createObjectURL(file);
            }
        },
        shopImagePreview: '',
        shopImageFile: null,
        onSettingsShopImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.shopImageFile = file;
                this.shopImagePreview = URL.createObjectURL(file);
            }
        },
        userProfileForm: {
            name: user ? user.name : '',
            display_name: user ? user.display_name : '',
            mobile: user ? user.mobile : '',
            email: user ? user.email : '',
            date_of_birth: user ? user.date_of_birth : '',
            gender: user ? user.gender : '',
            currency: user && user.currency ? user.currency : 'INR',
            date_format: user && user.date_format ? user.date_format : 'DD/MM/YYYY',
            time_format: user && user.time_format ? user.time_format : '12h',
            notification_preferences: Object.assign({ email: true, sms: false, whatsapp: true, push: true }, (user && user.notification_preferences) ? user.notification_preferences : {})
        },
        profileAvatarPreview: '',
        profileAvatarFile: null,
        onProfileAvatarChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.profileAvatarFile = file;
                this.profileAvatarPreview = URL.createObjectURL(file);
            }
        },
        invoiceConfigForm: {
            auto_increment: true,
            date_format: 'DD/MM/YYYY',
            paper_size: 'A4',
            theme_color: '#0F766E',
            show_customer_address: true,
            show_customer_gst: true,
            show_hsn_code: false,
            show_discount: true,
            show_tax: true,
            show_sku: false,
            gst_enabled: true,
            round_off: true,
            tax_summary: true,
            show_upi_qr: true,
            show_bank_details: false,
            auto_print: false,
            whatsapp_share: true,
            pdf_download: true,
            email_invoice: false
        },
        bank_holder: '',
        bank_account: '',
        bank_ifsc: '',
        bank_name: '',
        updateBankDetailsString() {
            let parts = [];
            if (this.bank_holder) parts.push('Holder Name: ' + this.bank_holder);
            if (this.bank_account) parts.push('Account No: ' + this.bank_account);
            if (this.bank_ifsc) parts.push('IFSC: ' + this.bank_ifsc);
            if (this.bank_name) parts.push('Bank: ' + this.bank_name);
            this.shopUpdateForm.bank_details = parts.join('\n');
        },
        parseBankDetails() {
            const str = this.shopUpdateForm.bank_details || '';
            const holderMatch = str.match(/Holder Name:\s*(.*)/i);
            const accountMatch = str.match(/Account No:\s*(.*)/i);
            const ifscMatch = str.match(/IFSC:\s*(.*)/i);
            const bankMatch = str.match(/Bank:\s*(.*)/i);
            
            this.bank_holder = holderMatch ? holderMatch[1].trim() : '';
            this.bank_account = accountMatch ? accountMatch[1].trim() : '';
            this.bank_ifsc = ifscMatch ? ifscMatch[1].trim() : '';
            this.bank_name = bankMatch ? bankMatch[1].trim() : '';
            
            if (!this.bank_holder && !this.bank_account && !this.bank_ifsc && !this.bank_name && str) {
                this.bank_holder = str;
            }
        },
        saveInvoiceSettings() {
            this.submitShopProfileUpdate(this.logoFile, this.signatureFile);
            this.submitInvoiceSettingsUpdate(this.invoiceConfigForm);
        },
        saveWebsiteSettings() {
            this.submitShopProfileUpdate(this.logoFile, this.signatureFile, this.shopImageFile);
        },
        getStoreUrl() {
            if (!this.shopUpdateForm.name) return '';
            const slug = this.shopUpdateForm.name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            return window.location.origin + '/store/' + slug;
        },
        testPrintInvoice() {
            this.printHtmlBlock('invoicePreviewPrintArea', 'Invoice Preview');
        },
        previewDateTime() {
            const d = new Date();
            const dd = String(d.getDate()).padStart(2, '0');
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const yyyy = d.getFullYear();
            const fmt = this.invoiceConfigForm.date_format || 'DD/MM/YYYY';
            let datePart;
            if (fmt === 'MM/DD/YYYY') datePart = mm + '/' + dd + '/' + yyyy;
            else if (fmt === 'YYYY-MM-DD') datePart = yyyy + '-' + mm + '-' + dd;
            else datePart = dd + '/' + mm + '/' + yyyy;
            const timePart = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            return datePart + ', ' + timePart;
        },
        previewInvoiceNumber() {
            const d = new Date();
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return 'INV-' + yyyy + mm + dd + '-0001';
        }
    }" x-init="
    if (shop) {
        shopUpdateForm.bank_details = shop.bank_details || '';
        parseBankDetails();
    }
    $watch('page', value => {
        if(value === 'settings' && shop) {
            shopUpdateForm = {
                name: shop.name,
                owner_name: user ? user.name : '',
                mobile: shop.mobile || '',
                email: shop.email || '',
                gst_number: shop.gst_number || '',
                address: shop.address || '',
                city: shop.city || '',
                state: shop.state || '',
                pincode: shop.pincode || '',
                currency: shop.currency || 'INR',
                upi_id: shop.upi_id || '',
                bank_details: shop.bank_details || '',
                invoice_footer: shop.invoice_footer || '',
                website_settings: shop.website_settings ? Object.assign({ enabled: false, subdomain: '', theme_color: '#0F766E', seo_title: '', seo_description: '', social_facebook: '', social_instagram: '', social_twitter: '', social_whatsapp: '', show_catalog: true, show_contact: true, show_inquiry: true, about_us: '' }, shop.website_settings) : { enabled: false, subdomain: '', theme_color: '#0F766E', seo_title: '', seo_description: '', social_facebook: '', social_instagram: '', social_twitter: '', social_whatsapp: '', show_catalog: true, show_contact: true, show_inquiry: true, about_us: '' }
            };
            logoPreview = '';
            logoFile = null;
            signaturePreview = '';
            signatureFile = null;
            shopImagePreview = '';
            shopImageFile = null;
            parseBankDetails();
        }
        if (value === 'settings' && user) {
            userProfileForm = {
                name: user.name || '',
                display_name: user.display_name || '',
                mobile: user.mobile || '',
                email: user.email || '',
                date_of_birth: user.date_of_birth || '',
                gender: user.gender || '',
                currency: user.currency || 'INR',
                date_format: user.date_format || 'DD/MM/YYYY',
                time_format: user.time_format || '12h',
                notification_preferences: Object.assign({ email: true, sms: false, whatsapp: true, push: true }, user.notification_preferences || {})
            };
            profileAvatarPreview = '';
            profileAvatarFile = null;
        }
    });
    $watch('invoiceSettings', value => {
        if (value) {
            invoiceConfigForm = {
                auto_increment: !!value.auto_increment,
                date_format: value.date_format || 'DD/MM/YYYY',
                paper_size: value.paper_size || 'A4',
                theme_color: value.theme_color || '#0F766E',
                show_customer_address: !!value.show_customer_address,
                show_customer_gst: !!value.show_customer_gst,
                show_hsn_code: !!value.show_hsn_code,
                show_discount: !!value.show_discount,
                show_tax: !!value.show_tax,
                show_sku: !!value.show_sku,
                gst_enabled: !!value.gst_enabled,
                round_off: !!value.round_off,
                tax_summary: !!value.tax_summary,
                show_upi_qr: !!value.show_upi_qr,
                show_bank_details: !!value.show_bank_details,
                auto_print: !!value.auto_print,
                whatsapp_share: !!value.whatsapp_share,
                pdf_download: !!value.pdf_download,
                email_invoice: !!value.email_invoice
            };
        }
    });
">
    {{-- Tab Switcher --}}
    <div class="flex gap-2 border-b border-slate-200 dark:border-gray-700">
        <button type="button" @click="settingsTab = 'shop'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all"
            :class="settingsTab === 'shop' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
            Shop Settings
        </button>
        <button type="button" @click="settingsTab = 'profile'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all"
            :class="settingsTab === 'profile' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
            User Settings
        </button>
        <button type="button" @click="settingsTab = 'invoice'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all"
            :class="settingsTab === 'invoice' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
            Invoice Settings
        </button>
        <button type="button" @click="settingsTab = 'website'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all"
            :class="settingsTab === 'website' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
            Website Settings
        </button>
        <button type="button" @click="settingsTab = 'backup'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all"
            :class="settingsTab === 'backup' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
            Backup & Restore
        </button>
    </div>

    {{-- Shop Profile Settings --}}
    <div x-show="settingsTab === 'shop'"
        class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">

        {{-- My Shops Management Section --}}
        <div class="mb-8 pb-8 border-b border-slate-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">My Shops</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Manage and switch between your business profiles</p>
                </div>
                <button type="button" @click="openAddShopModal()"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary-hover text-white text-[11px] font-bold rounded-xl shadow-md transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Shop
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-300">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="s in (user && user.shops ? user.shops : [])" :key="s.id">
                    <div @click="switchShop(s)"
                        class="cursor-pointer relative p-4 rounded-2xl border transition-all duration-300 flex items-center gap-3.5 group hover:shadow-md hover:scale-[1.01]"
                        :class="shop && shop.id === s.id 
                            ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-sm' 
                            : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-slate-300 dark:hover:border-gray-600'">
                        
                        <div class="w-10 h-10 rounded-xl bg-teal-600 dark:bg-teal-500 text-white font-extrabold flex items-center justify-center text-sm uppercase shrink-0"
                            x-text="s.name.charAt(0)">
                        </div>
                        
                        <div class="overflow-hidden flex-1">
                            <h4 class="text-xs font-bold text-slate-800 dark:text-white truncate" x-text="s.name"></h4>
                            <p class="text-[10px] text-slate-400 mt-0.5 truncate" x-text="s.mobile"></p>
                        </div>
                        
                        <div class="shrink-0 flex items-center">
                            <span x-show="shop && shop.id === s.id" 
                                class="flex items-center justify-center w-5 h-5 rounded-full bg-primary text-white text-xs font-bold shadow-sm">✓</span>
                            <span x-show="!shop || shop.id !== s.id" 
                                class="w-4 h-4 rounded-full border border-slate-300 dark:border-gray-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <form @submit.prevent="submitShopProfileUpdate(logoFile, signatureFile)"
            class="flex flex-col lg:flex-row gap-6">

            {{-- Left Side: Logo Upload card (Compact) --}}
            <div
                class="w-full lg:w-1/4 flex flex-col items-center justify-start p-4 border border-slate-100 dark:border-gray-700/50 rounded-2xl bg-slate-50/50 dark:bg-gray-900/20 text-center shrink-0">
                <span
                    class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Shop
                    Branding</span>

                <div
                    class="relative w-22 h-22 rounded-full border-2 border-dashed border-slate-300 dark:border-gray-600 flex items-center justify-center overflow-hidden bg-white dark:bg-gray-700 shadow-md group transition-all hover:border-primary">
                    <!-- Preview selected file -->
                    <template x-if="logoPreview">
                        <img :src="logoPreview" class="w-full h-full object-cover">
                    </template>
                    <!-- Or existing shop logo -->
                    <template x-if="!logoPreview">
                        <img :src="shop && shop.logo ? '/storage/' + shop.logo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(shopUpdateForm.name || 'Dukan') + '&background=0d9488&color=fff'"
                            class="w-full h-full object-cover">
                    </template>

                    <!-- Hover Edit Overlay -->
                    <div
                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 cursor-pointer">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-[8px] text-slate-200 font-bold uppercase tracking-wider">Change Logo</span>
                    </div>

                    <input type="file" accept="image/*" @change="onSettingsLogoChange($event)"
                        class="absolute inset-0 opacity-0 cursor-pointer">
                </div>

                <h5 class="text-sm font-bold text-slate-800 dark:text-white mt-3"
                    x-text="shopUpdateForm.name || 'My Shop'"></h5>
                <p class="text-[9px] text-slate-400 mt-0.5 uppercase font-semibold tracking-widest"
                    x-text="'GSTIN: ' + (shopUpdateForm.gst_number || 'None')"></p>
                <p class="text-[10px] text-slate-400 mt-1 max-w-[200px]">Format: JPG, PNG. Max 2MB.</p>

                {{-- Shop Signature Upload (Optional) --}}
                <div
                    class="w-full mt-4 pt-3 border-t border-slate-100 dark:border-gray-700/50 flex flex-col items-center">
                    <span
                        class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Shop
                        Signature (Optional)</span>
                    <div
                        class="relative w-full h-14 rounded-xl border-2 border-dashed border-slate-300 dark:border-gray-600 flex items-center justify-center overflow-hidden bg-white dark:bg-gray-700 group transition-all hover:border-primary">
                        <template x-if="signaturePreview">
                            <img :src="signaturePreview" class="w-full h-full object-contain p-1">
                        </template>
                        <template x-if="!signaturePreview && shop && shop.signature">
                            <img :src="'/storage/' + shop.signature" class="w-full h-full object-contain p-1">
                        </template>
                        <template x-if="!signaturePreview && !(shop && shop.signature)">
                            <span class="text-[9px] text-slate-400">No signature uploaded</span>
                        </template>

                        <div
                            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                            <span class="text-[8px] text-slate-200 font-bold uppercase tracking-wider">Change</span>
                        </div>

                        <input type="file" accept="image/*" @change="onSettingsSignatureChange($event)"
                            class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                </div>
            </div>

            {{-- Right Side: Form Inputs (12-Column Grid Layout) --}}
            <div class="flex-1 space-y-3.5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Shop
                            Name</label>
                        <input type="text" required x-model="shopUpdateForm.name"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">GSTIN Number
                            (Optional)</label>
                        <input type="text" placeholder="22AAAAA0000A1Z5" x-model="shopUpdateForm.gst_number"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Mobile
                            Number</label>
                        <input type="text" required x-model="shopUpdateForm.mobile" maxlength="10"
                            x-on:input="shopUpdateForm.mobile = shopUpdateForm.mobile.replace(/\D/g, '').slice(0, 10)"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" x-model="shopUpdateForm.email"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-6">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Shop
                            Address</label>
                        <input type="text" placeholder="Street Address..." x-model="shopUpdateForm.address"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">City</label>
                        <input type="text" x-model="shopUpdateForm.city"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">State</label>
                        <input type="text" x-model="shopUpdateForm.state"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label
                            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Pincode</label>
                        <input type="text" maxlength="10" x-model="shopUpdateForm.pincode"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                </div>

                <div class="pt-1 flex justify-end">
                    <button type="submit" :disabled="loading"
                        class="w-full sm:w-auto px-5 py-2 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white text-sm font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <template x-if="loading">
                            <div
                                class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent">
                            </div>
                        </template>
                        <span x-text="loading ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- My Profile / Account Settings --}}
    <div x-show="settingsTab === 'profile'" class="space-y-6">
        <form @submit.prevent="submitUserProfileUpdate(profileAvatarFile)" class="space-y-6">

            {{-- Basic Information --}}
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                <h4 class="text-sm font-extrabold text-slate-800 dark:text-white mb-4">Basic Information</h4>
                <div class="flex flex-col lg:flex-row gap-8">

                    {{-- Profile Photo --}}
                    <div
                        class="w-full lg:w-1/3 flex flex-col items-center justify-center p-6 border border-slate-100 dark:border-gray-700/50 rounded-2xl bg-slate-50/50 dark:bg-gray-900/20 text-center shrink-0 relative">
                        <!-- Account Status (Top Right) -->
                        <div class="absolute top-3 right-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                :class="(user && user.status === 'suspended') ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                                x-text="user && user.status ? user.status : 'active'"></span>
                        </div>

                        <span
                            class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Profile
                            Photo</span>

                        <div
                            class="relative w-28 h-28 rounded-full border-2 border-dashed border-slate-300 dark:border-gray-600 flex items-center justify-center overflow-hidden bg-white dark:bg-gray-700 shadow-md group transition-all hover:border-primary">
                            <template x-if="profileAvatarPreview">
                                <img :src="profileAvatarPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!profileAvatarPreview">
                                <img :src="user && user.avatar ? '/storage/' + user.avatar : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(userProfileForm.name || 'User') + '&background=0d9488&color=fff'"
                                    class="w-full h-full object-cover">
                            </template>

                            <div
                                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 cursor-pointer">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-[9px] text-slate-200 font-bold uppercase tracking-wider">Change
                                    Photo</span>
                            </div>

                            <input type="file" accept="image/*" @change="onProfileAvatarChange($event)"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>

                        <h5 class="text-sm font-bold text-slate-800 dark:text-white mt-4"
                            x-text="userProfileForm.display_name || userProfileForm.name || 'My Account'"></h5>
                        <p class="text-xs text-slate-400 mt-2 max-w-[200px]">Format: JPG, PNG. Max size 2MB.</p>
                    </div>

                    {{-- Right Side: Basic Info Form Inputs --}}
                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Full
                                    Name</label>
                                <input type="text" required x-model="userProfileForm.name"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Display
                                    Name</label>
                                <input type="text" placeholder="How you'd like to be shown"
                                    x-model="userProfileForm.display_name"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Mobile
                                    Number</label>
                                <input type="text" x-model="userProfileForm.mobile" maxlength="10"
                                    x-on:input="userProfileForm.mobile = userProfileForm.mobile.replace(/\D/g, '').slice(0, 10)"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Email
                                    Address</label>
                                <input type="email" required x-model="userProfileForm.email"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Date
                                    of Birth (Optional)</label>
                                <input type="date" x-model="userProfileForm.date_of_birth"
                                    onclick="this.showPicker()"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all cursor-pointer">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Gender
                                    (Optional)</label>
                                <select x-model="userProfileForm.gender"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="">Prefer not to say</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-gray-700/50">
                            <div>
                                <span
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Registration
                                    Date</span>
                                <div class="px-3 py-2 bg-slate-50 dark:bg-gray-900/30 border border-slate-200 dark:border-gray-600 rounded-xl text-sm text-slate-600 dark:text-slate-300 font-semibold"
                                    x-text="user && user.created_at ? new Date(user.created_at).toLocaleDateString() : '-'">
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Last
                                    Login</span>
                                <div class="px-3 py-2 bg-slate-50 dark:bg-gray-900/30 border border-slate-200 dark:border-gray-600 rounded-xl text-sm text-slate-600 dark:text-slate-300 font-semibold"
                                    x-text="user && user.last_login_at ? new Date(user.last_login_at).toLocaleString() : '-'">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Preferences --}}
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                <h4 class="text-sm font-extrabold text-slate-800 dark:text-white mb-4">Preferences</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Currency</label>
                        <select x-model="userProfileForm.currency"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="INR">INR (₹)</option>
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Date
                            Format</label>
                        <select x-model="userProfileForm.date_format"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                            <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                            <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Time
                            Format</label>
                        <select x-model="userProfileForm.time_format"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="12h">12-hour</option>
                            <option value="24h">24-hour</option>
                        </select>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-gray-700/50">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-3">Notification
                        Preferences</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label
                            class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="userProfileForm.notification_preferences.email"
                                class="rounded border-slate-300 text-primary focus:ring-primary">
                            Email
                        </label>
                        <label
                            class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="userProfileForm.notification_preferences.sms"
                                class="rounded border-slate-300 text-primary focus:ring-primary">
                            SMS
                        </label>
                        <label
                            class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="userProfileForm.notification_preferences.whatsapp"
                                class="rounded border-slate-300 text-primary focus:ring-primary">
                            WhatsApp
                        </label>
                        <label
                            class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="userProfileForm.notification_preferences.push"
                                class="rounded border-slate-300 text-primary focus:ring-primary">
                            Push
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="loading"
                    class="w-full sm:w-auto px-6 py-2.5 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white text-sm font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <template x-if="loading">
                        <div
                            class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent">
                        </div>
                    </template>
                    <span x-text="loading ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </form>
    </div>

    {{-- Website Settings --}}
    <div x-show="settingsTab === 'website'" class="w-full space-y-6" x-cloak>
        <form @submit.prevent="saveWebsiteSettings()" class="w-full space-y-6">

            <!-- Quick Link / Status Banner -->
            <div
                class="bg-gradient-to-r from-primary/10 to-primary/5 dark:from-primary/20 dark:to-transparent p-5 rounded-2xl border border-primary/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h4 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full"
                            :class="shopUpdateForm.website_settings.enabled ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                        Business Website Status:
                        <span
                            x-text="shopUpdateForm.website_settings.enabled ? 'Live & Online' : 'Offline / Private'"></span>
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Launch a responsive, SEO-ready storefront web page to showcase your store and catalog.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" x-model="shopUpdateForm.website_settings.enabled" class="sr-only">
                        <div class="w-12 h-6.5 rounded-full p-1 transition-colors duration-200 flex items-center shadow-inner"
                            :class="shopUpdateForm.website_settings.enabled ? 'bg-primary justify-end' : 'bg-slate-300 dark:bg-gray-700 justify-start'">
                            <div class="w-4.5 h-4.5 bg-white rounded-full shadow-md"></div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Public URL Copy Card -->
            <template x-if="shopUpdateForm.website_settings.enabled && shopUpdateForm.name">
                <div
                    class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Your Store
                            Address</label>
                        <div class="text-sm font-bold text-primary truncate" x-text="getStoreUrl()"></div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button"
                            @click="navigator.clipboard.writeText(getStoreUrl()); showToast('Link copied to clipboard!')"
                            class="px-4 py-2 border border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 hover:bg-slate-100 dark:hover:bg-gray-600 text-xs font-bold rounded-xl transition-all text-slate-800 dark:text-white flex items-center gap-1.5 shadow-sm cursor-pointer">
                            Copy Link
                        </button>
                        <button type="button" @click="window.open(getStoreUrl(), '_blank')"
                            class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shadow-sm cursor-pointer">
                            Visit Site
                        </button>
                    </div>
                </div>
            </template>

            <!-- Main configuration grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 columns: Config Form -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Branding Card -->
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4 relative">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Branding</h4>
                            <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="px-2 py-0.5 text-[9px] font-bold text-amber-600 bg-amber-500/10 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                                Premium
                            </span>
                        </div>

                        <!-- Accent Color Picker & Presets -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">Theme
                                    Accent Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="shopUpdateForm.website_settings.theme_color"
                                        :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                        class="w-10 h-10 border-0 rounded-xl cursor-pointer p-0 overflow-hidden shadow-sm disabled:opacity-50">
                                    <input type="text" x-model="shopUpdateForm.website_settings.theme_color"
                                        :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                        class="w-24 px-2 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white text-center font-mono disabled:opacity-50">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">Color
                                    Presets</label>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button"
                                        @click="if (user && user.active_plan && user.active_plan.slug !== 'free') shopUpdateForm.website_settings.theme_color = '#0F766E'"
                                        class="w-6 h-6 rounded-full bg-[#0F766E] border border-white dark:border-gray-800 shadow-sm"
                                        :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                                        title="Teal"></button>
                                    <button type="button"
                                        @click="if (user && user.active_plan && user.active_plan.slug !== 'free') shopUpdateForm.website_settings.theme_color = '#1D4ED8'"
                                        class="w-6 h-6 rounded-full bg-[#1D4ED8] border border-white dark:border-gray-800 shadow-sm"
                                        :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                                        title="Sapphire Blue"></button>
                                    <button type="button"
                                        @click="if (user && user.active_plan && user.active_plan.slug !== 'free') shopUpdateForm.website_settings.theme_color = '#7C3AED'"
                                        class="w-6 h-6 rounded-full bg-[#7C3AED] border border-white dark:border-gray-800 shadow-sm"
                                        :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                                        title="Purple"></button>
                                    <button type="button"
                                        @click="if (user && user.active_plan && user.active_plan.slug !== 'free') shopUpdateForm.website_settings.theme_color = '#B91C1C'"
                                        class="w-6 h-6 rounded-full bg-[#B91C1C] border border-white dark:border-gray-800 shadow-sm"
                                        :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                                        title="Rose Red"></button>
                                    <button type="button"
                                        @click="if (user && user.active_plan && user.active_plan.slug !== 'free') shopUpdateForm.website_settings.theme_color = '#D97706'"
                                        class="w-6 h-6 rounded-full bg-[#D97706] border border-white dark:border-gray-800 shadow-sm"
                                        :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                                        title="Amber"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information Bio & SEO -->
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Store Profile & SEO Settings</h4>
                            <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="px-2 py-0.5 text-[9px] font-bold text-amber-600 bg-amber-500/10 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                                Premium (SEO)
                            </span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">About
                                Store / Business Bio</label>
                            <textarea rows="4" x-model="shopUpdateForm.website_settings.about_us"
                                placeholder="Describe what your shop does, what you sell, and your business philosophy. This will be featured prominently on your home page."
                                class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">SEO
                                    Page Title</label>
                                <input type="text" placeholder="Online Catalog & Store"
                                    x-model="shopUpdateForm.website_settings.seo_title"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all disabled:bg-slate-50 dark:disabled:bg-gray-900/50 disabled:text-slate-400">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">SEO
                                    Description</label>
                                <input type="text" placeholder="Browse our wide selection of items..."
                                    x-model="shopUpdateForm.website_settings.seo_description"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all disabled:bg-slate-50 dark:disabled:bg-gray-900/50 disabled:text-slate-400">
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Social Media & Communication</h4>
                            <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="px-2 py-0.5 text-[9px] font-bold text-amber-600 bg-amber-500/10 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                                Premium
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Facebook
                                    Profile Link</label>
                                <input type="url" placeholder="https://facebook.com/my-page"
                                    x-model="shopUpdateForm.website_settings.social_facebook"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all disabled:bg-slate-50 dark:disabled:bg-gray-900/50 disabled:text-slate-400">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Instagram
                                    Profile Link</label>
                                <input type="url" placeholder="https://instagram.com/my-page"
                                    x-model="shopUpdateForm.website_settings.social_instagram"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all disabled:bg-slate-50 dark:disabled:bg-gray-900/50 disabled:text-slate-400">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Twitter
                                    / X Link</label>
                                <input type="url" placeholder="https://twitter.com/my-page"
                                    x-model="shopUpdateForm.website_settings.social_twitter"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all disabled:bg-slate-50 dark:disabled:bg-gray-900/50 disabled:text-slate-400">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">WhatsApp
                                    Number Link</label>
                                <input type="text" placeholder="https://wa.me/919999999999"
                                    x-model="shopUpdateForm.website_settings.social_whatsapp"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all disabled:bg-slate-50 dark:disabled:bg-gray-900/50 disabled:text-slate-400">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right 1 column: Features Toggles & Options -->
                <div class="space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4">
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Active Storefront Features
                        </h4>

                        <div class="space-y-4 pt-1 border-b border-slate-100 dark:border-gray-700 pb-4">
                            <label
                                class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300 cursor-pointer">
                                <span>Show Product Catalog</span>
                                <input type="checkbox" x-model="shopUpdateForm.website_settings.show_catalog"
                                    class="rounded border-slate-300 text-primary focus:ring-primary">
                            </label>

                            <label
                                class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300 cursor-pointer">
                                <span>Show Address & Contact Details</span>
                                <input type="checkbox" x-model="shopUpdateForm.website_settings.show_contact"
                                    class="rounded border-slate-300 text-primary focus:ring-primary">
                            </label>
                        </div>

                        <!-- Shop Image Upload -->
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">Shop Cover
                                Image</label>
                            <div class="flex flex-col gap-3">
                                <!-- Preview block -->
                                <div
                                    class="relative w-full h-32 rounded-xl overflow-hidden bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 flex items-center justify-center">
                                    <template x-if="shopImagePreview">
                                        <img :src="shopImagePreview" class="w-full h-full object-contain bg-white">
                                    </template>
                                    <template
                                        x-if="!shopImagePreview && shop && shop.website_settings && shop.website_settings.shop_image">
                                        <img :src="'/storage/' + shop.website_settings.shop_image"
                                            class="w-full h-full object-contain bg-white">
                                    </template>
                                    <template
                                        x-if="!shopImagePreview && (!shop || !shop.website_settings || !shop.website_settings.shop_image)">
                                        <div
                                            class="text-center text-slate-400 dark:text-slate-500 flex flex-col items-center">
                                            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span class="text-[10px]">No cover uploaded</span>
                                        </div>
                                    </template>
                                </div>
                                <label
                                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''"
                                    class="relative cursor-pointer bg-white dark:bg-gray-700 hover:bg-slate-50 dark:hover:bg-gray-600/80 border border-slate-300 dark:border-gray-600 rounded-xl px-3 py-2 text-center text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center justify-center gap-1.5 mt-2">
                                    <span>Upload Shop Image</span>
                                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                                    </span>
                                    <input type="file" accept="image/*" @change="onSettingsShopImageChange"
                                        :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                        class="sr-only">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 dark:from-emerald-950/20 dark:to-transparent p-5 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 shadow-sm space-y-3">
                        <h4
                            class="text-xs font-extrabold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">
                            How it works</h4>
                        <p class="text-xs text-emerald-700/80 dark:text-emerald-300/80 leading-relaxed">
                            Once enabled, DukanHisab dynamically maps your stored business details, contact information,
                            and current catalog items from your inventory directly to your storefront. Customers can
                            view your menu, prices, and send direct orders via WhatsApp.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end pt-2">
                <button type="submit" :disabled="loading"
                    class="w-full sm:w-auto px-6 py-2.5 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white text-sm font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <template x-if="loading">
                        <div
                            class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent">
                        </div>
                    </template>
                    <span x-text="loading ? 'Saving Settings...' : 'Save Website Settings'"></span>
                </button>
            </div>
        </form>
    </div>

    {{-- Invoice Settings --}}
    <div x-show="settingsTab === 'invoice'" class="w-full">
        <form @submit.prevent="saveInvoiceSettings()" class="w-full space-y-6">

            {{-- General, Layout & Branding --}}
            <div
                class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4">
                <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Invoice Details & Branding</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Date
                            Format</label>
                        <select x-model="invoiceConfigForm.date_format"
                            class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                            <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                            <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                        </select>
                    </div>
                </div>


                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Paper
                            Size</label>
                        <div class="flex gap-1">
                            <template x-for="size in ['A4', '58mm', '80mm']" :key="size">
                                <button type="button" @click="invoiceConfigForm.paper_size = size"
                                    class="flex-1 py-1 px-2 rounded-lg text-xs font-bold border transition-all"
                                    :class="invoiceConfigForm.paper_size === size ? 'bg-primary text-white border-primary' : 'border-slate-300 dark:border-gray-600 text-slate-500 dark:text-slate-400 hover:border-primary'"
                                    x-text="size"></button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Theme
                            Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="invoiceConfigForm.theme_color"
                                class="h-8 w-10 rounded border border-slate-300 dark:border-gray-600 cursor-pointer bg-transparent">
                            <input type="text" x-model="invoiceConfigForm.theme_color"
                                class="flex-1 min-w-0 px-2.5 py-1 border border-slate-300 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        </div>
                    </div>
                    <div class="pt-4 flex items-center justify-between gap-4">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" x-model="invoiceConfigForm.auto_increment"
                                class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Auto Increment</span>
                        </label>
                        <button type="button" @click="showPreviewModal = true"
                            class="px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 border border-slate-300 dark:border-gray-600 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-200 transition-all flex items-center gap-1.5 shrink-0 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            Preview
                        </button>
                    </div>
                </div>

                {{-- Logo and Signature (Branding Row) --}}
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3 border-t border-slate-100 dark:border-gray-700/50">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Shop
                            Logo</label>
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-10 h-10 rounded border border-slate-200 dark:border-gray-600 overflow-hidden bg-slate-50 dark:bg-gray-900/30 shrink-0">
                                <img :src="logoPreview || (shop && shop.logo ? '/storage/' + shop.logo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(shopUpdateForm.name || 'Dukan') + '&background=0d9488&color=fff')"
                                    class="w-full h-full object-cover">
                            </div>
                            <input type="file" accept="image/*" @change="onSettingsLogoChange($event)"
                                class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2.5 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Signature</label>
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-10 h-10 rounded border border-slate-200 dark:border-gray-600 overflow-hidden bg-slate-50 dark:bg-gray-900/30 shrink-0 flex items-center justify-center">
                                <template x-if="signaturePreview || (shop && shop.signature)">
                                    <img :src="signaturePreview || ('/storage/' + shop.signature)"
                                        class="w-full h-full object-contain p-0.5">
                                </template>
                                <template x-if="!signaturePreview && !(shop && shop.signature)">
                                    <span class="text-[8px] text-slate-400">None</span>
                                </template>
                            </div>
                            <input type="file" accept="image/*" @change="onSettingsSignatureChange($event)"
                                class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2.5 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Invoice
                        Footer</label>
                    <input type="text" placeholder="Thank you for your business!"
                        x-model="shopUpdateForm.invoice_footer"
                        class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                </div>
            </div>

            {{-- Invoice Features & Preferences --}}
            <div
                class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4">
                <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Invoice Features & Preferences</h4>

                <div
                    class="grid grid-cols-1 md:grid-cols-4 gap-6 md:divide-x md:divide-slate-200 md:dark:divide-gray-700/50">
                    <!-- Column 1: Customer Info -->
                    <div class="space-y-2">
                        <h5
                            class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                            Customer Info</h5>
                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Show Address</span>
                                <input type="checkbox" x-model="invoiceConfigForm.show_customer_address"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Show GST
                                    Number</span>
                                <input type="checkbox" x-model="invoiceConfigForm.show_customer_gst"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                        </div>
                    </div>

                    <!-- Column 2: Products -->
                    <div class="space-y-2 md:pl-6">
                        <h5
                            class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                            Products</h5>
                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Show HSN
                                    Code</span>
                                <input type="checkbox" x-model="invoiceConfigForm.show_hsn_code"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Show
                                    Discount</span>
                                <input type="checkbox" x-model="invoiceConfigForm.show_discount"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Show Tax</span>
                                <input type="checkbox" x-model="invoiceConfigForm.show_tax"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Show SKU</span>
                                <input type="checkbox" x-model="invoiceConfigForm.show_sku"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                        </div>
                    </div>

                    <!-- Column 3: Print & Share -->
                    <div class="space-y-2 md:pl-6">
                        <h5
                            class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                            Print & Share</h5>
                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Auto Print</span>
                                <input type="checkbox" x-model="invoiceConfigForm.auto_print"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 flex items-center gap-1">
                                    WhatsApp Share
                                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500" title="Premium Feature">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                                    </span>
                                </span>
                                <input type="checkbox" x-model="invoiceConfigForm.whatsapp_share"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4 disabled:opacity-50">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 flex items-center gap-1">
                                    PDF Download
                                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500" title="Premium Feature">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                                    </span>
                                </span>
                                <input type="checkbox" x-model="invoiceConfigForm.pdf_download"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4 disabled:opacity-50">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 flex items-center gap-1">
                                    Email Invoice
                                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500" title="Premium Feature">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                                    </span>
                                </span>
                                <input type="checkbox" x-model="invoiceConfigForm.email_invoice"
                                    :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4 disabled:opacity-50">
                            </label>
                        </div>
                    </div>

                    <!-- Column 4: Tax Settings -->
                    <div class="space-y-2 md:pl-6">
                        <h5
                            class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                            Tax Settings</h5>
                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">GST
                                    Enable/Disable</span>
                                <input type="checkbox" x-model="invoiceConfigForm.gst_enabled"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Round Off</span>
                                <input type="checkbox" x-model="invoiceConfigForm.round_off"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                            <label class="flex items-center justify-between gap-3 cursor-pointer py-0.5">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Tax Summary</span>
                                <input type="checkbox" x-model="invoiceConfigForm.tax_summary"
                                    class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Payment options inside the same card (to minimize vertical space/cards) --}}
                <div class="pt-3 border-t border-slate-100 dark:border-gray-700/50">
                    <h5 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                        Payment Info</h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 mb-2">
                        <label class="flex items-center justify-between gap-3 py-1 cursor-pointer">
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-300 flex items-center gap-1">
                                Show UPI QR
                                <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500" title="Premium Feature">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                                </span>
                            </span>
                            <input type="checkbox" x-model="invoiceConfigForm.show_upi_qr"
                                :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                                class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4 disabled:opacity-50">
                        </label>
                        <label class="flex items-center justify-between gap-3 py-1 cursor-pointer">
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Show Bank
                                Details</span>
                            <input type="checkbox" x-model="invoiceConfigForm.show_bank_details"
                                class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div x-show="invoiceConfigForm.show_upi_qr" class="md:col-span-4">
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">UPI
                                ID</label>
                            <input type="text" placeholder="shopname@upi" x-model="shopUpdateForm.upi_id"
                                class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        </div>
                        <div x-show="invoiceConfigForm.show_bank_details"
                            class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3"
                            :class="!invoiceConfigForm.show_upi_qr && 'md:col-span-12'">
                            <div class="sm:col-span-2">
                                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Bank Account
                                    Details</span>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Account
                                    Holder Name</label>
                                <input type="text" placeholder="John Doe" x-model="bank_holder"
                                    @input="updateBankDetailsString()"
                                    class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Bank
                                    Name</label>
                                <input type="text" placeholder="State Bank of India" x-model="bank_name"
                                    @input="updateBankDetailsString()"
                                    class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Account
                                    Number</label>
                                <input type="text" placeholder="1234567890" x-model="bank_account"
                                    @input="updateBankDetailsString()"
                                    class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">IFSC
                                    Code</label>
                                <input type="text" placeholder="SBIN0001234" x-model="bank_ifsc"
                                    @input="updateBankDetailsString()"
                                    class="block w-full px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="loading"
                    class="w-full sm:w-auto px-6 py-2.5 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white text-sm font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <template x-if="loading">
                        <div
                            class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent">
                        </div>
                    </template>
                    <span x-text="loading ? 'Saving...' : 'Save Invoice Settings'"></span>
                </button>
            </div>
        </form>
    </div>

    {{-- Backup & Restore Settings --}}
    <div x-show="settingsTab === 'backup'"
        class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
        
        <!-- Locked State -->
        <div x-show="user && user.active_plan && user.active_plan.slug === 'free'"
            class="flex flex-col items-center justify-center text-center p-12 bg-slate-50 dark:bg-gray-900/30 rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 min-h-[350px]">
            <div class="w-16 h-16 bg-amber-500/10 text-amber-500 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Cloud Backup & Restore is Locked</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mb-6">
                Protect your shop data with encrypted cloud backups. Upgrade to Premium or Business to download, restore, and schedule automatic daily backups.
            </p>
            <button type="button" @click="navigateTo('subscription')"
                class="px-5 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                Upgrade to Premium
            </button>
        </div>

        <!-- Unlocked State -->
        <div x-show="!user || !user.active_plan || user.active_plan.slug !== 'free'" class="space-y-6">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                        </path>
                    </svg>
                    Backup & Restore Shop Data
                </h3>
                <p class="text-xs text-slate-400 mt-1">Export a complete JSON backup of your shop products, inventory,
                    customers, suppliers, sales, purchases, and settings, or restore from a previous backup file.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Export Card --}}
                <div
                    class="p-5 border border-slate-200 dark:border-gray-700 rounded-2xl bg-slate-50/50 dark:bg-gray-900/30 flex flex-col justify-between space-y-4">
                    <div>
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Download Data Backup</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Download a complete,
                            encrypted backup file containing all products, sales history, customer dues, supplier
                            records, expenses, and settings.</p>
                    </div>
                    <button type="button" @click="downloadShopBackup()"
                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Backup
                    </button>
                </div>

                {{-- Restore Card --}}
                <div
                    class="p-5 border border-slate-200 dark:border-gray-700 rounded-2xl bg-slate-50/50 dark:bg-gray-900/30 flex flex-col justify-between space-y-4">
                    <div>
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Restore Data Backup</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Select a previously saved
                            DukanHisab backup file to restore all your shop records and settings.</p>
                    </div>

                    <div class="space-y-2">
                        <input type="file" id="shop-restore-file-input" accept=".dhbak"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400">
                        <button type="button" @click="restoreShopBackup(document.getElementById('shop-restore-file-input'))"
                            class="w-full py-2.5 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                            </svg>
                            Restore From Backup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal Popup -->
    <template x-teleport="body">
        <div x-show="showPreviewModal" x-cloak
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]"
                @click.outside="showPreviewModal = false">

                <!-- Modal Header -->
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900">
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        Invoice Live Preview
                    </h3>
                    <button type="button" @click="showPreviewModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Content (Preview Area) -->
                <div class="p-6 overflow-y-auto bg-slate-50 dark:bg-gray-900/40 flex-1 flex justify-center items-start">
                    <div id="invoicePreviewPrintArea"
                        class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 shadow-md p-5 w-full max-w-sm overflow-hidden text-slate-800 dark:text-slate-200"
                        :class="invoiceConfigForm.paper_size === 'A4' ? '' : 'max-w-[280px]'">
                        {{-- Themed Header: Logo/Shop info left, INVOICE title + number + date right --}}
                        <div class="rounded-lg p-3.5 flex items-start justify-between gap-4 transition-all invoice-theme-header"
                            :class="getContrastColor(invoiceConfigForm.theme_color || '#0F766E')"
                            :style="'background-color: ' + (invoiceConfigForm.theme_color || '#0F766E')">
                            <div class="flex items-start gap-2">
                                <div x-show="logoPreview || (shop && shop.logo)"
                                    class="w-9 h-9 rounded overflow-hidden bg-white/20 flex items-center justify-center shrink-0">
                                    <img :src="logoPreview || (shop && shop.logo ? '/storage/' + shop.logo : '')"
                                        class="w-full h-full object-cover">
                                </div>
                                <div class="flex flex-col items-start gap-0.5">
                                    <span class="text-xs font-bold leading-tight"
                                        x-text="shopUpdateForm.name || 'My Shop'"></span>
                                    <p x-show="shopUpdateForm.mobile" class="text-[9px] opacity-90"
                                        x-text="'Mobile: ' + (shopUpdateForm.mobile || '')"></p>
                                    <p x-show="shopUpdateForm.address" class="text-[9px] opacity-90"
                                        x-text="shopUpdateForm.address || ''"></p>
                                    <p x-show="shopUpdateForm.gst_number" class="text-[9px] opacity-90"
                                        x-text="shopUpdateForm.gst_number ? 'GSTIN: ' + shopUpdateForm.gst_number : ''">
                                    </p>
                                </div>
                            </div>
                            <div
                                class="text-[9px] text-right space-y-0.5 leading-tight font-medium opacity-90 max-w-[55%]">
                                <h3 class="font-bold text-sm">INVOICE</h3>
                                <p class="font-bold"><span class="font-normal opacity-80">Invoice No:</span> <span
                                        x-text="previewInvoiceNumber()"></span></p>
                                <p class="opacity-90 mt-1"><span class="font-normal opacity-80">Date:</span> <span
                                        x-text="previewDateTime()"></span></p>
                            </div>
                        </div>

                        {{-- Bill To / Payment Details row --}}
                        <div class="grid grid-cols-2 gap-3 py-3 text-[10px]">
                            <div>
                                <p class="text-[8px] uppercase font-bold text-slate-400 tracking-wider">Bill To</p>
                                <p class="font-bold text-slate-800 dark:text-white">Walk-In Customer</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[8px] uppercase font-bold text-slate-400 tracking-wider">Payment Info</p>
                                <p class="font-bold text-slate-800 dark:text-white"><span
                                        class="text-slate-500 font-normal">Payment Status:</span> Paid</p>
                                <p class="font-bold text-slate-800 dark:text-white"><span
                                        class="text-slate-500 font-normal">Method:</span> Cash</p>
                            </div>
                        </div>

                        {{-- Item table --}}
                        <table class="w-full text-[9px] border-t border-slate-100 dark:border-gray-700">
                            <thead>
                                <tr class="text-slate-400">
                                    <th class="text-left py-1">Item</th>
                                    <template x-if="invoiceConfigForm.show_sku">
                                        <th class="text-left">SKU</th>
                                    </template>
                                    <template x-if="invoiceConfigForm.show_hsn_code">
                                        <th class="text-left">HSN</th>
                                    </template>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Qty</th>
                                    <template x-if="invoiceConfigForm.show_discount">
                                        <th class="text-right">Disc</th>
                                    </template>
                                    <template x-if="invoiceConfigForm.show_tax">
                                        <th class="text-right">Tax</th>
                                    </template>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-slate-600 dark:text-slate-300">
                                    <td class="py-1.5 font-semibold">Sample Item</td>
                                    <template x-if="invoiceConfigForm.show_sku">
                                        <td>SKU001</td>
                                    </template>
                                    <template x-if="invoiceConfigForm.show_hsn_code">
                                        <td>1234</td>
                                    </template>
                                    <td class="text-right">₹100.00</td>
                                    <td class="text-right">1</td>
                                    <template x-if="invoiceConfigForm.show_discount">
                                        <td class="text-right">0</td>
                                    </template>
                                    <template x-if="invoiceConfigForm.show_tax">
                                        <td class="text-right">18%</td>
                                    </template>
                                    <td class="text-right font-bold">₹100.00</td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- QR/Bank (left) + Totals (right), side by side like the real invoice --}}
                        <div
                            class="border-t border-slate-100 dark:border-gray-700 pt-3 mt-1 flex justify-between items-start gap-3 text-[10px]">
                            <div class="flex flex-col items-start gap-1.5 max-w-[50%]">
                                <template x-if="invoiceConfigForm.show_upi_qr">
                                    <div class="flex flex-col items-center gap-0.5">
                                        <template x-if="shopUpdateForm.upi_id">
                                            <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('upi://pay?pa=' + shopUpdateForm.upi_id + '&pn=' + (shopUpdateForm.name || 'Shop') + '&am=100.00&cu=INR')"
                                                class="w-16 h-16 bg-white p-0.5 rounded border border-slate-200 shadow-sm">
                                        </template>
                                        <template x-if="!shopUpdateForm.upi_id">
                                            <div
                                                class="w-16 h-16 border border-dashed border-slate-300 dark:border-gray-600 rounded flex items-center justify-center text-[6px] text-slate-400 text-center px-1">
                                                Enter UPI ID
                                            </div>
                                        </template>
                                        <span class="text-[7px] text-slate-400" x-text="shopUpdateForm.upi_id"></span>
                                    </div>
                                </template>
                                <template x-if="invoiceConfigForm.show_bank_details && shopUpdateForm.bank_details">
                                    <p class="text-[8px] text-slate-500 whitespace-pre-line leading-tight border-t border-dashed border-slate-200 dark:border-gray-700 pt-1 mt-1 w-full"
                                        x-text="shopUpdateForm.bank_details"></p>
                                </template>
                            </div>
                            <div class="flex flex-col items-end gap-0.5">
                                <div class="flex justify-between w-32"><span
                                        class="text-slate-500">Subtotal:</span><span
                                        class="font-semibold">₹100.00</span></div>
                                <template x-if="invoiceConfigForm.show_discount">
                                    <div class="flex justify-between w-32"><span
                                            class="text-slate-500">Discount:</span><span
                                            class="font-semibold">-₹0.00</span></div>
                                </template>
                                <template x-if="invoiceConfigForm.tax_summary && invoiceConfigForm.gst_enabled">
                                    <div class="flex justify-between w-32"><span class="text-slate-500">GST:</span><span
                                            class="font-semibold">₹18.00</span></div>
                                </template>
                                <template x-if="invoiceConfigForm.round_off">
                                    <div class="flex justify-between w-32"><span class="text-slate-500">Round
                                            Off:</span><span class="font-semibold">₹0.00</span></div>
                                </template>
                                <div
                                    class="flex justify-between w-32 text-xs font-bold border-t border-dashed border-slate-200 dark:border-gray-700 pt-1 mt-0.5">
                                    <span>Total:</span><span class="text-primary">₹100.00</span>
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-[9px] text-center text-slate-400"
                            x-text="shopUpdateForm.invoice_footer || 'Thank you for your business!'"></p>
                        <template x-if="signaturePreview || (shop && shop.signature)">
                            <img :src="signaturePreview || ('/storage/' + shop.signature)"
                                class="mt-2 h-8 ml-auto object-contain">
                        </template>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="px-5 py-4 border-t border-slate-100 dark:border-gray-700/50 bg-slate-50 dark:bg-gray-800 flex flex-col sm:flex-row gap-2 justify-end">
                    <button type="button" @click="testPrintInvoice()"
                        class="w-full sm:w-auto px-4 py-2 border border-primary text-primary hover:bg-primary/5 text-xs font-semibold rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z">
                            </path>
                        </svg>
                        Test Print
                    </button>
                    <button type="button" @click="showPreviewModal = false"
                        class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-semibold text-slate-600 dark:text-slate-200 rounded-xl transition-all shadow-sm">
                        Close
                    </button>
                </div>
    </template>
</div>