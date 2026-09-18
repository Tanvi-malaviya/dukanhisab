@extends('layouts.admin')

@section('title', 'Add-Ons')
@section('page_title', 'Add-Ons')

@section('content')
<div class="space-y-2">

    <x-search-filter :action="route('admin.addons.index')" placeholder="Search user name, email, mobile..." :show-reset="false">
        <x-slot name="actions">
            <x-button type="button" onclick="openCreateAddOnModal()" variant="primary" class="flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Add-On
            </x-button>
        </x-slot>
    </x-search-filter>

    <!-- Add-Ons Matrix -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($addOns as $addOn)
        <div class="bg-card-dark border border-border-dark rounded-2xl p-6 relative flex flex-col justify-between hover:border-primary/50 transition-all shadow-sm">
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-secondary/40 border border-border-dark overflow-hidden flex items-center justify-center shrink-0">
                        @if($addOn->image_url)
                            <img src="{{ $addOn->image_url }}" class="w-full h-full object-cover" alt="{{ $addOn->title }}">
                        @else
                            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-white">{{ $addOn->title }}</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-secondary/60 text-slate-300">{{ $addOn->type }}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">{{ $addOn->description ?? 'No description provided.' }}</p>
                    </div>
                </div>

                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-extrabold text-white">₹{{ number_format($addOn->price, 0) }}</span>
                    <span class="text-xs text-slate-400 font-medium">/ {{ $addOn->billing_period }} (Auto-Renewal)</span>
                </div>

                <p class="text-xs text-slate-300 pt-2 border-t border-border-dark">
                    @if($addOn->type === 'shop')
                        1 Purchase = 1 Extra Shop. Buying multiple purchases stacks the shop limit.
                    @else
                        Unlocks the public shop website / product showcase feature for 1 year.
                    @endif
                </p>
            </div>

            <div class="pt-6 mt-4 border-t border-border-dark flex items-center justify-between">
                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold {{ $addOn->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">
                    {{ ucfirst($addOn->status) }}
                </span>

                <button onclick="openEditAddOnModal(this)" data-addon="{{ json_encode($addOn) }}" class="text-xs text-primary font-medium hover:underline cursor-pointer">Edit Add-On</button>
            </div>
        </div>
        @empty
        <div class="md:col-span-2">
            <x-empty-state
                title="No add-ons configured yet"
                message="Create the Shop and Website add-ons to make them purchasable from the web panel and mobile app."
            />
        </div>
        @endforelse
    </div>

    <!-- Add-On Purchases Ledger -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-white">Add-On Purchases Ledger</h2>

        <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                            <th class="px-6 py-4">User Details</th>
                            <th class="px-6 py-4">Add-On</th>
                            <th class="px-6 py-4">Qty</th>
                            <th class="px-6 py-4">Coverage Dates</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                        @forelse($history as $row)
                        <tr class="hover:bg-secondary/10 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-white">{{ $row->user ? $row->user->name : 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $row->user ? ($row->user->email ?? $row->user->mobile) : 'No user linked' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold text-white">{{ $row->addOn ? $row->addOn->title : 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono">{{ $row->quantity }}</td>
                            <td class="px-6 py-4 text-xs font-mono space-y-0.5">
                                <p><span class="text-slate-500">Starts:</span> {{ $row->starts_at ? $row->starts_at->format('Y-m-d') : 'N/A' }}</p>
                                <p><span class="text-slate-500">Expires:</span> {{ $row->ends_at ? $row->ends_at->format('Y-m-d') : 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $row->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">
                                    {{ ucfirst($row->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($row->status === 'active')
                                    <button onclick="openExtendAddOnModal({{ $row->id }}, '{{ addslashes($row->user ? $row->user->name : 'User') }}')" class="p-1 text-xs text-warning hover:underline cursor-pointer">
                                        Extend
                                    </button>
                                    <button type="button" onclick="confirmExpireAddOn('{{ route('admin.addons.expire', $row->id) }}', '{{ addslashes($row->user ? $row->user->name : 'User') }}')" class="p-1 text-xs text-danger hover:underline cursor-pointer">
                                        Expire
                                    </button>
                                    @else
                                    <button onclick="openExtendAddOnModal({{ $row->id }}, '{{ addslashes($row->user ? $row->user->name : 'User') }}')" class="p-1 text-xs text-success hover:underline cursor-pointer">
                                        Reactivate
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <x-empty-state
                                colspan="6"
                                title="No add-on purchases found"
                                :message="request()->filled('search') ? 'We couldn\'t find any add-on purchase logs matching your current filters.' : 'No one has purchased the Shop or Website add-on yet.'"
                                :resetUrl="request()->filled('search') ? route('admin.addons.index') : null"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :records="$history" />
        </div>
    </div>

</div>

<!-- Modal 1: Create Add-On -->
<div id="createAddOnModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCreateAddOnModal()"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Add New Add-On</h3>
            <button onclick="closeCreateAddOnModal()" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <form action="{{ route('admin.addons.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Add-On Type</label>
                <select name="type" required class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                    <option value="shop">Shop (Increases Shop Limit)</option>
                    <option value="website">Website (Unlocks Web Storefront)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Title</label>
                <input type="text" name="title" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Description</label>
                <textarea name="description" rows="2" class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Price (INR / Yearly)</label>
                <input type="number" step="0.01" name="price" value="200" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Image</label>
                <input type="file" name="image" accept="image/*" class="block w-full text-xs text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-secondary/60 file:text-slate-200">
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeCreateAddOnModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Save Add-On</x-button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Add-On -->
<div id="editAddOnModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditAddOnModal()"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-4 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Edit Add-On</h3>
            <button onclick="closeEditAddOnModal()" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <form id="editAddOnForm" method="POST" enctype="multipart/form-data" class="p-4 space-y-3">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Title</label>
                <input type="text" name="title" id="edit_addon_title" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Description</label>
                <textarea name="description" id="edit_addon_desc" rows="2" class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Price (INR / Yearly)</label>
                <input type="number" step="0.01" name="price" id="edit_addon_price" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Image</label>
                <input type="file" name="image" accept="image/*" class="block w-full text-xs text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-secondary/60 file:text-slate-200">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Status</label>
                <select name="status" id="edit_addon_status" required class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeEditAddOnModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Extend Add-On -->
<div id="extendAddOnModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeExtendAddOnModal()"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Extend Add-On Validity</h3>
            <button onclick="closeExtendAddOnModal()" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <form id="extendAddOnForm" method="POST" class="p-6 space-y-4">
            @csrf
            <p class="text-xs text-slate-400">Extend add-on coverage for <span class="text-white font-semibold" id="extend_addon_user_name">User</span>.</p>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Validity Addition (Days)</label>
                <input type="number" name="days" value="365" min="1" required class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeExtendAddOnModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Extend Validity</x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateAddOnModal() {
        document.getElementById('createAddOnModal').classList.remove('hidden');
    }
    function closeCreateAddOnModal() {
        document.getElementById('createAddOnModal').classList.add('hidden');
    }
    function openEditAddOnModal(elementOrAddOn) {
        let addOn;
        if (elementOrAddOn instanceof HTMLElement) {
            addOn = JSON.parse(elementOrAddOn.getAttribute('data-addon'));
        } else {
            addOn = elementOrAddOn;
        }

        document.getElementById('editAddOnForm').action = "{{ route('admin.addons.update', ['id' => ':id']) }}".replace(':id', addOn.id);
        document.getElementById('edit_addon_title').value = addOn.title;
        document.getElementById('edit_addon_desc').value = addOn.description || '';
        document.getElementById('edit_addon_price').value = addOn.price;
        document.getElementById('edit_addon_status').value = addOn.status;
        document.getElementById('editAddOnModal').classList.remove('hidden');
    }
    function closeEditAddOnModal() {
        document.getElementById('editAddOnModal').classList.add('hidden');
    }
    function openExtendAddOnModal(id, userName) {
        document.getElementById('extendAddOnForm').action = "{{ route('admin.addons.extend', ['id' => ':id']) }}".replace(':id', id);
        document.getElementById('extend_addon_user_name').innerText = userName;
        document.getElementById('extendAddOnModal').classList.remove('hidden');
    }
    function closeExtendAddOnModal() {
        document.getElementById('extendAddOnModal').classList.add('hidden');
    }
    function confirmExpireAddOn(actionUrl, userName) {
        confirmAction({
            actionUrl: actionUrl,
            title: 'Expire Add-On',
            message: `Are you sure you want to expire this add-on for "${userName}" immediately?`,
            buttonText: 'Expire Immediately',
            variant: 'danger',
            method: 'POST'
        });
    }
</script>
@endsection
