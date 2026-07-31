@extends('layouts.admin')

@section('title', 'Shops Directory')
@section('page_title', 'Shops Directory')
@section('page_subtitle')

@section('content')
<div class="space-y-2">
    
    <x-search-filter :action="route('admin.shops.index')" placeholder="Search shops, owners..." :show-reset="false">
        <div class="w-full md:w-48">
            <select name="status" onchange="this.form.submit()" class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-700">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>

        <div class="w-full md:w-48">
            <select name="plan" onchange="this.form.submit()" class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-700">
                <option value="">All Plans</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>

        <x-slot name="actions">
            <x-button type="button" onclick="openAddShopModal()" variant="primary" class="flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Shop
            </x-button>
        </x-slot>
    </x-search-filter>

    <!-- Shops Table -->
    <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                        <th class="px-4 py-3">Shop Details</th>
                        <th class="px-4 py-3">Owner Name</th>
                        <th class="px-4 py-3">GST Number</th>
                        <th class="px-4 py-3">Current Plan</th>
                        <th class="px-4 py-3">Shop Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                    @forelse($shops as $shop)
                    <tr class="hover:bg-secondary/10 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($shop->logo)
                                    <img src="{{ asset('storage/' . $shop->logo) }}" alt="{{ $shop->name }}" class="w-10 h-10 rounded-xl object-cover border border-border-dark flex-shrink-0">
                                @else
                                    <span class="w-10 h-10 rounded-xl bg-secondary text-slate-200 border border-border-dark flex items-center justify-center font-bold text-sm uppercase flex-shrink-0">
                                        {{ substr($shop->name, 0, 2) }}
                                    </span>
                                @endif
                                <div>
                                    <p class="font-semibold text-white truncate max-w-[180px]" title="{{ $shop->name }}">{{ $shop->name }}</p>
                                    <p class="text-xs text-slate-500 font-mono">{{ $shop->mobile ?? $shop->email }}</p>
                                    <p class="text-[11px] text-slate-500 truncate max-w-[200px]" title="{{ $shop->address ?? 'No Address' }}">{{ $shop->address ?? 'No Address' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-white truncate max-w-[140px]" title="{{ $shop->owner->name }}">{{ $shop->owner->name }}</p>
                            <p class="text-xs text-slate-500 truncate max-w-[160px]" title="{{ $shop->owner->email }}">{{ $shop->owner->email }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">
                            {{ $shop->gst_number ?? 'Not Configured' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($shop->activePlan)
                                <div class="space-y-0.5">
                                    <span class="inline-flex px-2.5 py-0.5 rounded text-[11px] font-semibold {{ $shop->activePlan->slug === 'premium' ? 'bg-warning/20 text-warning border border-warning/30' : 'bg-slate-500/20 text-slate-400 border border-slate-500/30' }}">
                                        {{ $shop->activePlan->name }}
                                    </span>
                                    @if($shop->currentSubscription && $shop->currentSubscription->status === 'expired')
                                        <div class="text-[10px] text-danger font-semibold flex items-center gap-1">
                                            <span class="w-1 h-1 rounded-full bg-danger"></span>
                                            Expired
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-500">None</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $shop->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">
                                {{ ucfirst($shop->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- View Details Ajax -->
                                <button onclick="viewShopDetails({{ $shop->id }})" class="p-1.5 rounded-lg bg-info/10 text-info hover:bg-info hover:text-white transition-colors cursor-pointer" title="View Shop Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>

                                <!-- Manual Override Sub plan -->
                                <button onclick="openPlanOverrideModal({{ $shop->id }}, '{{ addslashes($shop->owner->name) }}')" class="p-1.5 rounded-lg bg-warning/10 text-warning hover:bg-warning hover:text-white transition-colors cursor-pointer" title="Update Subscription Plan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                </button>

                                <!-- Edit Shop Details -->
                                <button onclick="openEditShopModal(this)" data-shop="{{ json_encode($shop->only(['id', 'owner_id', 'name', 'email', 'mobile', 'address', 'gst_number', 'status', 'logo'])) }}" class="p-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors cursor-pointer" title="Edit Shop Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>

                                <!-- Suspend Shop -->
                                <form action="{{ route('admin.shops.toggle', $shop->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg {{ $shop->status === 'suspended' ? 'bg-success/10 text-success hover:bg-success hover:text-white' : 'bg-danger/10 text-danger hover:bg-danger hover:text-white' }} transition-colors cursor-pointer" title="{{ $shop->status === 'suspended' ? 'Activate Shop' : 'Suspend Shop' }}">
                                        @if($shop->status === 'suspended')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state
                            colspan="6"
                            title="No shops found"
                            message="We couldn't find any shop records matching your current filter criteria."
                            resetUrl="{{ route('admin.shops.index') }}"
                        />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :records="$shops" />
    </div>

</div>

<!-- Modal 1: AJAX Shop Details Card -->
<div id="detailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDetailsModal()"></div>
    
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-2xl shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Tenant Parameters</h3>
            <button onclick="closeDetailsModal()" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 space-y-6" id="detailsContent">
            <!-- Populated via JS -->
            <p class="text-sm text-slate-400 text-center py-4">Querying details from server...</p>
        </div>
    </div>
</div>

<!-- Modal 2: Subscription Plan Override -->
<div id="overrideModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closePlanOverrideModal()"></div>
    
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Override Subscription Tier</h3>
            <button onclick="closePlanOverrideModal()" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="overrideForm" method="POST" class="p-6 space-y-4">
            @csrf
            
            <p class="text-xs text-slate-400">Configure manually active plan for <span class="text-white font-semibold" id="override_shop_name">Shop</span>.</p>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Select Subscription Plan</label>
                <select name="plan_id" required class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} (₹{{ $plan->price }}/{{ $plan->billing_period }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Duration (Days)</label>
                <input type="number" name="duration_days" value="30" min="1" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closePlanOverrideModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Activate Subscription</x-button>
            </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Add New Shop -->
<div id="addShopModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddShopModal()"></div>
    
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
        <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Add New Shop</h3>
            <button onclick="closeAddShopModal()" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('admin.shops.store') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-3">
            @csrf
            
            @if($errors->any() && session('modal_open') === 'add_shop')
                <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                    <p class="font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Failed to create shop:
                    </p>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Owner</label>
                    <select name="owner_id" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="">Select Owner...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('owner_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Status</label>
                    <select name="status" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="active" {{ old('status') === 'active' || !old('status') ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" pattern="[0-9]{10}" maxlength="10" title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">GSTIN (GST Number)</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number') }}" placeholder="e.g. 24AAAAA1121A1Z1" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Address</label>
                    <textarea name="address" rows="2" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white h-[58px] resize-none">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Logo</label>
                    <div class="flex items-center gap-3 p-2 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all h-[58px]">
                        <!-- Preview Container -->
                        <div class="flex-shrink-0">
                            <img id="add_logo_preview" src="" alt="Logo Preview" class="w-8 h-8 rounded-lg object-cover border border-border-dark hidden">
                            <span id="add_logo_placeholder" class="w-8 h-8 rounded-lg bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs">
                                +
                            </span>
                        </div>
                        <!-- Upload Input -->
                        <div class="flex-1 min-w-0">
                            <input type="file" name="logo" id="add_logo" accept="image/*" class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none" onchange="previewAddLogo(this)">
                            <p class="text-[9px] text-slate-500 leading-tight truncate">Max 2MB. JPG, PNG, WEBP.</p>
                            <p id="add_logo_error" class="text-[9px] text-danger mt-0.5 hidden leading-none"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeAddShopModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Create Shop</x-button>
            </div>
        </form>
    </div>
</div>
<!-- Modal 4: Edit Shop -->
<div id="editShopModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditShopModal()"></div>
    
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
        <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Edit Shop</h3>
            <button onclick="closeEditShopModal()" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editShopForm" method="POST" enctype="multipart/form-data" class="p-5 space-y-3">
            @csrf
            @method('PUT')
            
            @if($errors->any() && session('modal_open') === 'edit_shop')
                <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                    <p class="font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Failed to update shop:
                    </p>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Owner</label>
                    <select name="owner_id" id="edit_shop_owner_id" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="">Select Owner...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Status</label>
                    <select name="status" id="edit_shop_status" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Name</label>
                    <input type="text" name="name" id="edit_shop_name" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" id="edit_shop_email" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                    <input type="text" name="mobile" id="edit_shop_mobile" pattern="[0-9]{10}" maxlength="10" title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">GSTIN (GST Number)</label>
                    <input type="text" name="gst_number" id="edit_shop_gst_number" placeholder="e.g. 24AAAAA1121A1Z1" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Address</label>
                    <textarea name="address" id="edit_shop_address" rows="2" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white h-[58px] resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Logo</label>
                    <div class="flex items-center gap-3 p-2 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all h-[58px]">
                        <!-- Current Logo Preview -->
                        <div id="edit_logo_preview_container" class="flex-shrink-0">
                            <img id="edit_logo_preview" src="" alt="Logo" class="w-8 h-8 rounded-lg object-cover border border-border-dark hidden">
                            <span id="edit_logo_placeholder" class="w-8 h-8 rounded-lg bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs uppercase">
                                ??
                            </span>
                        </div>
                        <!-- Upload Input -->
                        <div class="flex-1 min-w-0">
                            <input type="file" name="logo" id="edit_logo" accept="image/*" class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none" onchange="previewEditLogo(this)">
                            <p class="text-[9px] text-slate-500 leading-tight truncate">Max 2MB. JPG, PNG, WEBP.</p>
                            <p id="edit_logo_error" class="text-[9px] text-danger mt-0.5 hidden leading-none"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeEditShopModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Update Shop</x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function viewShopDetails(shopId) {
        document.getElementById('detailsContent').innerHTML = '<p class="text-sm text-slate-400 text-center py-4">Querying details from server...</p>';
        document.getElementById('detailsModal').classList.remove('hidden');

      fetch("{{ route('admin.shops.show', ['id' => ':id']) }}".replace(':id', shopId))
            .then(res => res.json())
            .then(data => {
                let planName = 'None';
                if (data.active_plan) {
                    planName = data.active_plan.name;
                    let latestSub = data.subscriptions && data.subscriptions.length > 0 
                        ? data.subscriptions.reduce((prev, current) => (prev.id > current.id) ? prev : current)
                        : null;
                    if (latestSub && latestSub.status === 'expired') {
                        planName += ` <span class="text-danger font-semibold bg-danger/10 px-1.5 py-0.5 rounded text-[10px] ml-1">Expired</span>`;
                    }
                }

                let logoHtml = '';
                if (data.logo) {
                    logoHtml = `<img src="/storage/${data.logo}" alt="${data.name}" class="w-16 h-16 rounded-xl object-cover border border-border-dark mb-4 md:mb-0">`;
                } else {
                    logoHtml = `<span class="w-16 h-16 rounded-xl bg-secondary text-slate-200 border border-border-dark flex items-center justify-center font-bold text-lg uppercase mb-4 md:mb-0">${data.name.substring(0, 2)}</span>`;
                }

                let html = `
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="flex-shrink-0 flex justify-center items-start">
                            ${logoHtml}
                        </div>
                        <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Shop Parameters</h4>
                                <div class="space-y-2 text-xs">
                                    <p><span class="text-slate-400">Name:</span> <span class="text-white font-medium">${data.name}</span></p>
                                    <p><span class="text-slate-400">Email:</span> <span class="text-white">${data.email || 'No email'}</span></p>
                                    <p><span class="text-slate-400">Mobile:</span> <span class="text-white">${data.mobile || 'No mobile'}</span></p>
                                    <p><span class="text-slate-400">Address:</span> <span class="text-white">${data.address || 'No address'}</span></p>
                                    <p><span class="text-slate-400">GSTIN:</span> <span class="text-white font-mono">${data.gst_number || 'None'}</span></p>
                                    <p><span class="text-slate-400">Status:</span> <span class="text-white">${data.status}</span></p>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Account Owner</h4>
                                <div class="space-y-2 text-xs">
                                    <p><span class="text-slate-400">Name:</span> <span class="text-white font-medium">${data.owner.name}</span></p>
                                    <p><span class="text-slate-400">Email:</span> <span class="text-white">${data.owner.email}</span></p>
                                    <p><span class="text-slate-400">Plan:</span> <span class="text-white">${planName}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('detailsContent').innerHTML = html;
            });
    }

    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }

    function openPlanOverrideModal(id, name) {
        document.getElementById('overrideForm').action = "/admin/shops/" + id + "/subscription";
        document.getElementById('override_shop_name').innerText = name;
        document.getElementById('overrideModal').classList.remove('hidden');
    }

    function closePlanOverrideModal() {
        document.getElementById('overrideModal').classList.add('hidden');
    }

    function clearModalFields(modal) {
        if (!modal) return;
        const form = modal.querySelector('form');
        if (form) {
            form.querySelectorAll('input:not([type="hidden"]), textarea').forEach(input => {
                input.value = '';
            });
            form.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
            });
        }
        const errAlerts = modal.querySelectorAll('.bg-danger\\/10');
        errAlerts.forEach(el => el.remove());
        const previewImg = modal.querySelector('#add_logo_preview');
        const placeholder = modal.querySelector('#add_logo_placeholder');
        if (previewImg) { previewImg.src = ''; previewImg.classList.add('hidden'); }
        if (placeholder) { placeholder.classList.remove('hidden'); }
    }

    function openAddShopModal(clearForm = false) {
        const modal = document.getElementById('addShopModal');
        if (modal) {
            if (clearForm) {
                clearModalFields(modal);
            }
            // Reset file input & error
            if (document.getElementById('add_logo')) document.getElementById('add_logo').value = '';
            if (document.getElementById('add_logo_preview')) {
                document.getElementById('add_logo_preview').src = '';
                document.getElementById('add_logo_preview').classList.add('hidden');
            }
            if (document.getElementById('add_logo_placeholder')) document.getElementById('add_logo_placeholder').classList.remove('hidden');
            if (document.getElementById('add_logo_error')) {
                document.getElementById('add_logo_error').classList.add('hidden');
                document.getElementById('add_logo_error').innerText = '';
            }

            modal.classList.remove('hidden');
        }
    }

    function closeAddShopModal() {
        const modal = document.getElementById('addShopModal');
        if (modal) {
            modal.classList.add('hidden');
            clearModalFields(modal);
        }
    }

    function openEditShopModal(elementOrShop) {
        let shop;
        if (elementOrShop instanceof HTMLElement) {
            shop = JSON.parse(elementOrShop.getAttribute('data-shop'));
        } else {
            shop = elementOrShop;
        }

        document.getElementById('editShopForm').action = "/admin/shops/" + shop.id;
        document.getElementById('edit_shop_owner_id').value = shop.owner_id;
        document.getElementById('edit_shop_name').value = shop.name;
        document.getElementById('edit_shop_email').value = shop.email || '';
        document.getElementById('edit_shop_mobile').value = shop.mobile || '';
        document.getElementById('edit_shop_gst_number').value = shop.gst_number || '';
        document.getElementById('edit_shop_address').value = shop.address || '';
        document.getElementById('edit_shop_status').value = shop.status;

        // Reset file input & error
        document.getElementById('edit_logo').value = '';
        document.getElementById('edit_logo_error').classList.add('hidden');
        document.getElementById('edit_logo_error').innerText = '';

        // Handle logo preview
        const previewImg = document.getElementById('edit_logo_preview');
        const placeholder = document.getElementById('edit_logo_placeholder');
        if (shop.logo) {
            previewImg.src = "/storage/" + shop.logo;
            previewImg.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            previewImg.src = "";
            previewImg.classList.add('hidden');
            placeholder.classList.remove('hidden');
            placeholder.innerText = shop.name.substring(0, 2).toUpperCase();
        }

        @if(session('modal_open') === 'edit_shop')
            @if(old('owner_id')) document.getElementById('edit_shop_owner_id').value = "{!! addslashes(old('owner_id')) !!}"; @endif
            @if(old('name')) document.getElementById('edit_shop_name').value = "{!! addslashes(old('name')) !!}"; @endif
            @if(old('email')) document.getElementById('edit_shop_email').value = "{!! addslashes(old('email')) !!}"; @endif
            @if(old('mobile')) document.getElementById('edit_shop_mobile').value = "{!! addslashes(old('mobile')) !!}"; @endif
            @if(old('gst_number')) document.getElementById('edit_shop_gst_number').value = "{!! addslashes(old('gst_number')) !!}"; @endif
            @if(old('address')) document.getElementById('edit_shop_address').value = "{!! addslashes(old('address')) !!}"; @endif
            @if(old('status')) document.getElementById('edit_shop_status').value = "{!! addslashes(old('status')) !!}"; @endif
        @endif

        document.getElementById('editShopModal').classList.remove('hidden');
    }

    function closeEditShopModal() {
        document.getElementById('editShopModal').classList.add('hidden');
    }

    function previewAddLogo(input) {
        const previewImg = document.getElementById('add_logo_preview');
        const placeholder = document.getElementById('add_logo_placeholder');
        const errorEl = document.getElementById('add_logo_error');
        
        errorEl.classList.add('hidden');
        errorEl.innerText = '';

        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            if (file.size > 2 * 1024 * 1024) {
                errorEl.innerText = "File size exceeds 2MB limit.";
                errorEl.classList.remove('hidden');
                input.value = '';
                previewImg.src = '';
                previewImg.classList.add('hidden');
                placeholder.classList.remove('hidden');
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                errorEl.innerText = "Invalid file type. Only JPEG, PNG, JPG, GIF, WEBP allowed.";
                errorEl.classList.remove('hidden');
                input.value = '';
                previewImg.src = '';
                previewImg.classList.add('hidden');
                placeholder.classList.remove('hidden');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    function previewEditLogo(input) {
        const previewImg = document.getElementById('edit_logo_preview');
        const placeholder = document.getElementById('edit_logo_placeholder');
        const errorEl = document.getElementById('edit_logo_error');
        
        errorEl.classList.add('hidden');
        errorEl.innerText = '';

        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            if (file.size > 2 * 1024 * 1024) {
                errorEl.innerText = "File size exceeds 2MB limit.";
                errorEl.classList.remove('hidden');
                input.value = '';
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                errorEl.innerText = "Invalid file type. Only JPEG, PNG, JPG, GIF, WEBP allowed.";
                errorEl.classList.remove('hidden');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('modal_open') === 'add_shop')
            openAddShopModal();
        @elseif(session('modal_open') === 'edit_shop' && session('edit_shop_data'))
            openEditShopModal({!! json_encode(session('edit_shop_data')) !!});
        @endif
    });
</script>
@endsection
