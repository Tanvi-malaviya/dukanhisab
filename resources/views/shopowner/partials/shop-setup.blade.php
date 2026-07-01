{{-- SHOP SETUP FLOW (IF LOGGED IN BUT NO SHOP) --}}
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden bg-slate-50 dark:bg-gray-900">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg relative z-10 text-center">
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Set Up Your Shop</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Complete your onboarding details to start using DukanHisab.</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg relative z-10 px-4 sm:px-0">
        <div class="glass-card py-8 px-6 shadow-xl border border-slate-200/50 dark:border-gray-700 rounded-2xl sm:px-10 bg-white dark:bg-gray-800">
            <form @submit.prevent="handleShopSetup()" class="space-y-4">

                {{-- Logo Upload --}}
                <div class="flex flex-col items-center gap-3">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Shop Logo</label>
                    <div class="relative w-24 h-24 rounded-full border-2 border-dashed border-slate-300 dark:border-gray-600 flex items-center justify-center overflow-hidden bg-slate-50 dark:bg-gray-700">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!logoPreview">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </template>
                        <input type="file" accept="image/*" @change="onLogoChange($event)" class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Shop Name</label>
                    <input type="text" required placeholder="My Store" x-model="setupForm.name" class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Owner Name</label>
                    <input type="text" required placeholder="Owner Name" x-model="setupForm.owner_name" class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Mobile Number</label>
                    <input type="text" required placeholder="Mobile" x-model="setupForm.mobile" maxlength="10" x-on:input="setupForm.mobile = setupForm.mobile.replace(/\D/g, '').slice(0, 10)" class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">GST Number (Optional)</label>
                    <input type="text" placeholder="22AAAAA0000A1Z5" x-model="setupForm.gst_number" class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>

                <button type="submit" class="w-full py-3 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all">Create Shop</button>
            </form>
        </div>
    </div>
</div>
