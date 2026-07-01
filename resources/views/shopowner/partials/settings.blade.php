{{-- SETTINGS PANEL --}}
<div x-show="page === 'settings'" class="space-y-6"
    x-data="{
        shopUpdateForm: {
            name: shop ? shop.name : '',
            owner_name: user ? user.name : '',
            mobile: shop ? shop.mobile : '',
            gst_number: shop ? shop.gst_number : '',
            address: shop ? shop.address : ''
        },
        logoPreview: '',
        logoFile: null,
        onSettingsLogoChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.logoFile = file;
                this.logoPreview = URL.createObjectURL(file);
            }
        }
    }"
    x-init="
    $watch('page', value => {
        if(value === 'settings' && shop) {
            shopUpdateForm = { name: shop.name, owner_name: user ? user.name : '', mobile: shop.mobile || '', gst_number: shop.gst_number || '', address: shop.address || '' };
            logoPreview = '';
            logoFile = null;
        }
    })
">
    <div>
        <h3 class="text-xl font-extrabold text-slate-800 dark:text-white">Shop Settings & Preferences</h3>
        <p class="text-xs text-slate-500">Manage shop profile, branding, and contact details</p>
    </div>

    {{-- Shop Profile Settings --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
        <form @submit.prevent="submitShopProfileUpdate(logoFile)" class="flex flex-col lg:flex-row gap-8">
            
            {{-- Left Side: Logo Upload card --}}
            <div class="w-full lg:w-1/3 flex flex-col items-center justify-center p-6 border border-slate-100 dark:border-gray-700/50 rounded-2xl bg-slate-50/50 dark:bg-gray-900/20 text-center shrink-0">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Shop Branding</span>
                
                <div class="relative w-28 h-28 rounded-full border-2 border-dashed border-slate-300 dark:border-gray-600 flex items-center justify-center overflow-hidden bg-white dark:bg-gray-700 shadow-md group transition-all hover:border-primary">
                    <!-- Preview selected file -->
                    <template x-if="logoPreview">
                        <img :src="logoPreview" class="w-full h-full object-cover">
                    </template>
                    <!-- Or existing shop logo -->
                    <template x-if="!logoPreview">
                        <img :src="shop && shop.logo ? '/storage/' + shop.logo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(shopUpdateForm.name || 'Dukan') + '&background=0d9488&color=fff'" class="w-full h-full object-cover">
                    </template>
                    
                    <!-- Hover Edit Overlay -->
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 cursor-pointer">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-[9px] text-slate-200 font-bold uppercase tracking-wider">Change Logo</span>
                    </div>
                    
                    <input type="file" accept="image/*" @change="onSettingsLogoChange($event)" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>
                
                <h5 class="text-sm font-bold text-slate-800 dark:text-white mt-4" x-text="shopUpdateForm.name || 'My Shop'"></h5>
                <p class="text-[10px] text-slate-400 mt-1 uppercase font-semibold tracking-widest" x-text="'GSTIN: ' + (shopUpdateForm.gst_number || 'None')"></p>
                <p class="text-xs text-slate-400 mt-2 max-w-[200px]">Format: JPG, PNG. Max size 2MB.</p>
            </div>

            {{-- Right Side: Form Inputs --}}
            <div class="flex-1 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Shop Name</label>
                        <input type="text" required x-model="shopUpdateForm.name"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Owner Name</label>
                        <input type="text" required x-model="shopUpdateForm.owner_name"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Mobile Number</label>
                        <input type="text" required x-model="shopUpdateForm.mobile" maxlength="10"
                            x-on:input="shopUpdateForm.mobile = shopUpdateForm.mobile.replace(/\D/g, '').slice(0, 10)"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">GSTIN Number (Optional)</label>
                        <input type="text" placeholder="22AAAAA0000A1Z5" x-model="shopUpdateForm.gst_number"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Shop Address</label>
                    <textarea rows="3" placeholder="Street Address, City, State..." x-model="shopUpdateForm.address"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all"></textarea>
                </div>
                <div class="pt-2 flex justify-end">
                    <button type="submit" :disabled="loading"
                        class="w-full sm:w-auto px-6 py-2.5 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white text-sm font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <template x-if="loading">
                            <div class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                        </template>
                        <span x-text="loading ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>