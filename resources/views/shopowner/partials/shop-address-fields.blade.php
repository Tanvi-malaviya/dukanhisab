{{--
    Shop email + address fields with pincode lookup — the same fields (and the same pincode → state,
    city, area auto-fill) as the app's shop setup screen. Used by first-time shop setup and Add Shop.

    @param string $form        Alpine expression of the form object (e.g. 'shopSetupForm')
    @param string $inputClass  classes for inputs/selects
    @param string $labelClass  classes for labels
--}}
<div>
    <label class="{{ $labelClass }}">Email (Optional)</label>
    <input type="email" placeholder="shop@example.com" x-model="{{ $form }}.email" class="{{ $inputClass }}">
</div>

<div>
    <label class="{{ $labelClass }}">Address (Optional)</label>
    <input type="text" placeholder="Shop no., street, landmark" x-model="{{ $form }}.address" class="{{ $inputClass }}">
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="{{ $labelClass }}">Pincode</label>
        <div class="relative">
            <input type="text" inputmode="numeric" maxlength="6" placeholder="e.g. 380001" x-model="{{ $form }}.pincode"
                x-on:input.debounce.400ms="lookupShopPincode({{ $form }})" class="{{ $inputClass }}">
            <svg x-show="{{ $form }}.pincodeLoading" class="animate-spin h-3.5 w-3.5 text-primary absolute right-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
        </div>
    </div>
    <div>
        <label class="{{ $labelClass }}">State</label>
        <input type="text" placeholder="State" x-model="{{ $form }}.state" class="{{ $inputClass }}">
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="{{ $labelClass }}">City</label>
        <input type="text" placeholder="City" x-model="{{ $form }}.city" class="{{ $inputClass }}">
    </div>
    <div>
        <label class="{{ $labelClass }}">Area</label>
        <select x-model="{{ $form }}.area" :disabled="!({{ $form }}.areas || []).length" class="{{ $inputClass }}">
            <option value="" x-text="({{ $form }}.areas || []).length ? 'Select area' : 'Enter pincode first'"></option>
            <template x-for="a in ({{ $form }}.areas || [])" :key="a">
                <option :value="a" x-text="a"></option>
            </template>
        </select>
    </div>
</div>
