@extends('layouts.admin')

@section('title', 'Subscription Plans')
@section('page_title', 'Subscription Plans ') 

@section('content')
<div class="space-y-2">
    
    <x-search-filter :action="route('admin.subscriptions.index')" placeholder="Search user name, email, mobile..." :show-reset="false">
        <x-slot name="actions">
            <x-button type="button" onclick="openCreatePlanModal()" variant="primary" class="flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Subscription Plan
            </x-button>
        </x-slot>
    </x-search-filter>

    <!-- Subscription Plans Matrix -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        <div class="bg-card-dark border border-border-dark rounded-2xl p-6 relative flex flex-col justify-between hover:border-primary/50 transition-all shadow-sm">
            @if($plan->slug === 'yearly')
                <div class="absolute top-4 right-4 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-warning/20 text-warning border border-warning/30">Popular</div>
            @endif

            <div class="space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-400 mt-1">{{ $plan->description ?? 'Standard plan functionality' }}</p>
                    </div>
                </div>

                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-extrabold text-white">₹{{ number_format($plan->price, 0) }}</span>
                    <span class="text-xs text-slate-400 font-medium">/ {{ $plan->billing_period }}</span>
                </div>

                <!-- Features checklist -->
                <ul class="space-y-2 text-xs text-slate-300 pt-2 border-t border-border-dark">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Max Shops: <strong class="text-white">{{ $plan->features['max_shops'] ?? 1 }}</strong> {{ ($plan->features['max_shops'] ?? 1) > 1 ? 'Shops' : 'Shop' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Device Limit: <strong class="text-white">{{ $plan->features['max_devices'] ?? 1 }}</strong> {{ ($plan->features['max_devices'] ?? 1) > 1 ? 'Devices Login' : 'Single Device Login' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 {{ !empty($plan->features['advanced_reports']) ? 'text-success' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ !empty($plan->features['advanced_reports']) ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"></path></svg>
                        <span class="{{ !empty($plan->features['advanced_reports']) ? '' : 'line-through text-slate-500' }}">Advanced Analytics Reports</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 {{ !empty($plan->features['backup']) ? 'text-success' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ !empty($plan->features['backup']) ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"></path></svg>
                        <span class="{{ !empty($plan->features['backup']) ? '' : 'line-through text-slate-500' }}">Automated Cloud Backups</span>
                    </li>
                </ul>
            </div>

            <div class="pt-6 mt-4 border-t border-border-dark flex items-center justify-between">
                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold {{ $plan->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">
                    {{ ucfirst($plan->status) }}
                </span>
                
                <div class="flex items-center gap-3">
                    <button onclick="openEditPlanModal(this)" data-plan="{{ json_encode($plan) }}" class="text-xs text-primary font-medium hover:underline cursor-pointer">Edit Plan</button>
                    @if($plan->slug !== 'free')
                        <button onclick="confirmDelete('{{ route('admin.subscriptions.plan.destroy', $plan->id) }}', '{{ $plan->name }}')" class="text-xs text-danger font-medium hover:underline cursor-pointer">
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Subscriptions Ledger Table -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-white">Subscriptions Ledger</h2>
        
        <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                            <th class="px-6 py-4">User Details</th>
                            <th class="px-6 py-4">Billing Plan</th>
                            <th class="px-6 py-4">Coverage Dates</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                        @forelse($history as $sub)
                        <tr class="hover:bg-secondary/10 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-white">{{ $sub->user ? $sub->user->name : 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $sub->user ? ($sub->user->email ?? $sub->user->mobile) : 'No user linked' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold text-white">{{ $sub->plan ? $sub->plan->name : 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono space-y-0.5">
                                <p><span class="text-slate-500">Starts:</span> {{ $sub->starts_at ? $sub->starts_at->format('Y-m-d') : 'N/A' }}</p>
                                <p><span class="text-slate-500">Expires:</span> {{ $sub->ends_at ? $sub->ends_at->format('Y-m-d') : 'Lifetime' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sub->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">
                                    {{ ucfirst($sub->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($sub->status === 'active')
                                    <!-- Extend Subscription -->
                                    <button onclick="openExtendSubModal({{ $sub->id }}, '{{ addslashes($sub->user ? $sub->user->name : 'User') }}')" class="p-1 text-xs text-warning hover:underline cursor-pointer">
                                        Extend
                                    </button>
                                    
                                    <!-- Expire manual trigger -->
                                    <button type="button" onclick="confirmExpire('{{ route('admin.subscriptions.expire', $sub->id) }}', '{{ addslashes($sub->user ? $sub->user->name : 'User') }}')" class="p-1 text-xs text-danger hover:underline cursor-pointer">
                                        Expire
                                    </button>
                                    @else
                                     <!-- Reactivate Subscription -->
                                     <button type="button" onclick="confirmReactivate('{{ route('admin.subscriptions.reactivate', $sub->id) }}', '{{ addslashes($sub->user ? $sub->user->name : 'User') }}')" class="p-1 text-xs text-success hover:underline cursor-pointer">
                                          Reactivate
                                      </button>
                                     @endif
                                 </div>
                            </td>
                        </tr>
                        @empty
                            <x-empty-state
                                colspan="5"
                                title="No subscription records found"
                                message="We couldn't find any subscription logs matching your current filters."
                                resetUrl="{{ route('admin.subscriptions.index') }}"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :records="$history" />
        </div>
    </div>

</div>

<!-- Modal 1: Create Plan -->
<div id="createPlanModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCreatePlanModal()"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Create Subscription Plan</h3>
            <button onclick="closeCreatePlanModal()" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <form action="{{ route('admin.subscriptions.plan.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Plan Name</label>
                <input type="text" name="name" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Description</label>
                <input type="text" name="description" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Price (INR)</label>
                    <input type="number" step="0.01" name="price" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Billing Cycle</label>
                    <select name="billing_period" required class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="free">Free</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Max Shops</label>
                    <input type="number" name="features[max_shops]" value="1" min="1" class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Max Devices</label>
                    <input type="number" name="features[max_devices]" value="1" min="1" class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeCreatePlanModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Create Plan</x-button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Plan -->
<div id="editPlanModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditPlanModal()"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-4 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Edit Subscription Plan</h3>
            <button onclick="closeEditPlanModal()" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <form id="editPlanForm" method="POST" class="p-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Plan Name</label>
                <input type="text" name="name" id="edit_plan_name" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Description</label>
                <input type="text" name="description" id="edit_plan_desc" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Price (INR)</label>
                    <input type="number" step="0.01" name="price" id="edit_plan_price" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Billing Cycle</label>
                    <select name="billing_period" id="edit_plan_period" required class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="free">Free</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Max Shops</label>
                    <input type="number" name="features[max_shops]" id="edit_plan_max_shops" min="1" class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Max Devices</label>
                    <input type="number" name="features[max_devices]" id="edit_plan_max_devices" min="1" class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Status</label>
                <select name="status" id="edit_plan_status" required class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeEditPlanModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Extend Subscription -->
<div id="extendSubModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeExtendSubModal()"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Extend Subscription Validity</h3>
            <button onclick="closeExtendSubModal()" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <form id="extendSubForm" method="POST" class="p-6 space-y-4">
            @csrf
            <p class="text-xs text-slate-400">Extend subscription coverage for <span class="text-white font-semibold" id="extend_shop_name">User</span>.</p>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Validity Addition (Days)</label>
                <input type="number" name="days" value="30" min="1" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeExtendSubModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Extend Validity</x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreatePlanModal() {
        document.getElementById('createPlanModal').classList.remove('hidden');
    }
    function closeCreatePlanModal() {
        document.getElementById('createPlanModal').classList.add('hidden');
    }
    function openEditPlanModal(elementOrPlan) {
        let plan;
        if (elementOrPlan instanceof HTMLElement) {
            plan = JSON.parse(elementOrPlan.getAttribute('data-plan'));
        } else {
            plan = elementOrPlan;
        }

        document.getElementById('editPlanForm').action = "{{ route('admin.subscriptions.plan.update', ['id' => ':id']) }}".replace(':id', plan.id);
        document.getElementById('edit_plan_name').value = plan.name;
        document.getElementById('edit_plan_desc').value = plan.description;
        document.getElementById('edit_plan_price').value = plan.price;
        document.getElementById('edit_plan_period').value = plan.billing_period;
        document.getElementById('edit_plan_max_shops').value = plan.features ? (plan.features.max_shops || 1) : 1;
        document.getElementById('edit_plan_max_devices').value = plan.features ? plan.features.max_devices : 1;
        document.getElementById('edit_plan_status').value = plan.status;
        document.getElementById('editPlanModal').classList.remove('hidden');
    }
    function closeEditPlanModal() {
        document.getElementById('editPlanModal').classList.add('hidden');
    }
    function openExtendSubModal(id, shopName) {
        document.getElementById('extendSubForm').action = "{{ route('admin.subscriptions.extend', ['id' => ':id']) }}".replace(':id', id);
        document.getElementById('extend_shop_name').innerText = shopName;
        document.getElementById('extendSubModal').classList.remove('hidden');
    }
    function closeExtendSubModal() {
        document.getElementById('extendSubModal').classList.add('hidden');
    }
    function confirmExpire(actionUrl, shopName) {
        confirmAction({
            actionUrl: actionUrl,
            title: 'Expire Subscription',
            message: `Are you sure you want to expire the subscription for "${shopName}" immediately?`,
            buttonText: 'Expire Immediately',
            variant: 'danger',
            method: 'POST'
        });
    }
    function confirmReactivate(actionUrl, shopName) {
        confirmAction({
            actionUrl: actionUrl,
            title: 'Reactivate Subscription',
            message: `Are you sure you want to reactivate the subscription for "${shopName}" using the remaining coverage days?`,
            buttonText: 'Reactivate Now',
            variant: 'success',
            method: 'POST'
        });
    }
</script>
@endsection
