@extends('layouts.admin')

@section('title', 'User Management')
@section('page_title', 'User Management')
@section('page_subtitle')

@section('content')
<div class="space-y-2">

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Users -->
        <div class="card-purchase border p-4 rounded-xl flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Users</p>
                <h3 class="text-2xl font-bold text-white mt-0.5">{{ number_format($stats['total']) }}</h3>
                <span class="inline-flex items-center text-[9px] text-info font-medium mt-1 bg-info/10 px-1.5 py-0.5 rounded">
                    Registered Clients
                </span>
            </div>
            <span class="p-2 card-icon rounded-lg border border-blue-200 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Active Users -->
        <div class="card-sale  p-4 rounded-xl flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Active Users</p>
                <h3 class="text-2xl font-bold text-white mt-0.5">{{ number_format($stats['active']) }}</h3>
                <span class="inline-flex items-center text-[9px] text-success font-medium mt-1 bg-success/10 px-1.5 py-0.5 rounded">
                    {{ round(($stats['active'] / max(1, $stats['total'])) * 100) }}% Active Ratio
                </span>
            </div>
            <span class="p-2 card-icon rounded-lg border border-green-200 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Premium Users -->
        <div class="card-customer-due border p-4 rounded-xl flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Premium Users</p>
                <h3 class="text-2xl font-bold text-white mt-0.5">{{ number_format($stats['premium']) }}</h3>
                <span class="inline-flex items-center text-[9px] text-warning font-medium mt-1 bg-warning/10 px-1.5 py-0.5 rounded">
                    {{ round(($stats['premium'] / max(1, $stats['total'])) * 100) }}% Premium Ratio
                </span>
            </div>
            <span class="p-2 card-icon rounded-lg border border-amber-200 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Suspended Users -->
        <div class="card-supplier-due border p-4 rounded-xl flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Suspended Users</p>
                <h3 class="text-2xl font-bold text-white mt-0.5">{{ number_format($stats['suspended']) }}</h3>
                <span class="inline-flex items-center text-[9px] text-danger font-medium mt-1 bg-danger/10 px-1.5 py-0.5 rounded">
                    Access Revoked
                </span>
            </div>
            <span class="p-2 card-icon rounded-lg border border-red-200 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
            </span>
        </div>
    </div>

    <x-search-filter :action="route('admin.users.index')" placeholder="Search by name, email, or mobile...">
        <div class="w-full md:w-48">
            <select name="status" class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-700">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>
        <x-slot name="actions">
            <x-button type="button" onclick="openAddModal()" variant="primary" class="flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add User
            </x-button>
        </x-slot>
    </x-search-filter>

    <!-- Users Table Card -->
    <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                        <th class="px-6 py-4">Client Details</th>
                        <th class="px-6 py-4">Plan Status</th>
                        <th class="px-6 py-4">Acc Status</th>
                        <th class="px-6 py-4">Telemetry</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                    @forelse($users as $user)
                    <tr class="hover:bg-secondary/10 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover border border-border-dark flex-shrink-0">
                                @else
                                    <span class="w-10 h-10 rounded-full bg-secondary text-slate-200 border border-border-dark flex items-center justify-center font-bold text-sm uppercase flex-shrink-0">
                                        {{ substr($user->name, 0, 2) }}
                                    </span>
                                @endif
                                <div>
                                    <p class="font-semibold text-white">
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="text-primary hover:text-secondary-hover hover:underline transition-all">
                                            {{ $user->name }}
                                        </a>
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    <p class="text-xs text-slate-500 font-mono">{{ $user->mobile ?? 'No Mobile' }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if($user->shops->count() > 0)
                                @php
                                    $firstShop = $user->shops->first();
                                @endphp
                                @if($firstShop->activePlan)
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold {{ $firstShop->activePlan->slug === 'premium' ? 'bg-warning/20 text-warning border border-warning/30' : 'bg-slate-500/20 text-slate-400 border border-slate-500/30' }}">
                                        {{ $firstShop->activePlan->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-500">No Plan</span>
                                @endif
                            @else
                                <span class="text-xs text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs space-y-1 text-slate-500">
                            <p>Created: <span class="text-slate-400 font-mono">{{ $user->created_at->format('Y-m-d') }}</span></p>
                            <p>Last login: <span class="text-slate-400 font-mono">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span></p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2.5">
                                <!-- View Details Link -->
                                <a href="{{ route('admin.users.show', $user->id) }}" class="p-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors" title="View User Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>

                                <!-- Impersonate Link -->
                                <a href="{{ route('admin.users.login_as', $user->id) }}" class="p-1.5 rounded-lg bg-info/10 text-info hover:bg-info hover:text-white transition-colors" title="Login as User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"></path></svg>
                                </a>

                                <!-- Edit details toggle -->
                                <button onclick="openEditModal({{ json_encode($user->only(['id', 'name', 'email', 'mobile', 'status', 'avatar'])) }})" 
                                    class="p-1.5 rounded-lg bg-secondary/60 hover:bg-secondary text-white/20 transition-colors cursor-pointer" title="Edit User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>

                                <!-- Reset Password toggle -->
                                <!-- <button onclick="openPasswordModal({{ $user->id }}, '{{ $user->name }}')" 
                                    class="p-1.5 rounded-lg bg-warning/10 text-warning hover:bg-warning hover:text-white transition-colors cursor-pointer" title="Reset Password">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-5 4v5m0 0l-2-2m2 2l2-2M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                                </button> -->


                                <!-- Delete account -->
                                <button type="button" onclick="confirmDelete('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')" 
                                    class="p-1.5 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-white transition-colors cursor-pointer" title="Delete User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">No users found matching filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :records="$users" />
    </div>

</div>

<!-- Modal 1: Edit Details Dialog -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
    
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
        <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Edit Client Details</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data" class="p-5 space-y-3">
            @csrf
            
            @if($errors->any() && session('modal_open') === 'edit')
                <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                    <p class="font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Failed to update user details:
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
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Name</label>
                    <input type="text" name="name" id="edit_name" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" id="edit_email" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                    <input type="text" name="mobile" id="edit_mobile" pattern="[0-9]{10}" maxlength="10" title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Account Status</label>
                    <select name="status" id="edit_status" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Profile Image (Avatar)</label>
                <div class="flex items-center gap-4 p-3 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all">
                    <!-- Current Avatar Preview -->
                    <div id="edit_avatar_preview_container" class="flex-shrink-0">
                        <img id="edit_avatar_preview" src="" alt="Avatar" class="w-12 h-12 rounded-full object-cover border border-border-dark hidden">
                        <span id="edit_avatar_placeholder" class="w-12 h-12 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-sm uppercase">
                            ??
                        </span>
                    </div>
                    <!-- Upload Input -->
                    <div class="flex-1">
                        <input type="file" name="avatar" id="edit_avatar" accept="image/*" class="block w-full text-xs text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none" onchange="previewEditAvatar(this)">
                        <p class="text-[10px] text-slate-500 mt-1">Accepts JPEG, PNG, JPG, GIF, WEBP. Max 2MB.</p>
                        <p id="edit_avatar_error" class="text-[10px] text-danger mt-1 hidden"></p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeEditModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Reset Password Dialog -->
<div id="passwordModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closePasswordModal()"></div>
    
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Reset Account Password</h3>
            <button onclick="closePasswordModal()" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="passwordForm" method="POST" class="p-6 space-y-4">
            @csrf
            
            <p class="text-xs text-slate-400">Set a new password for <span class="text-white font-semibold" id="pwd_user_name">User</span>.</p>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">New Password</label>
                <input type="password" name="password" required placeholder="Minimum 8 characters" class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Confirm New Password</label>
                <input type="password" name="password_confirmation" required placeholder="Re-enter password" class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closePasswordModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Reset Password</x-button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Add User Dialog -->
<div id="addModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddModal()"></div>
    
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
        <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Add New User</h3>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-3">
            @csrf
            
            @if($errors->any() && session('modal_open') === 'add')
                <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                    <p class="font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Failed to create user:
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
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Full Name" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@example.com" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" pattern="[0-9]{10}" maxlength="10" title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Account Status</label>
                    <select name="status" required class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Profile Image (Avatar)</label>
                <div class="flex items-center gap-4 p-3 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all">
                    <!-- Preview Container -->
                    <div class="flex-shrink-0">
                        <img id="add_avatar_preview" src="" alt="Avatar Preview" class="w-12 h-12 rounded-full object-cover border border-border-dark hidden">
                        <span id="add_avatar_placeholder" class="w-12 h-12 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-sm">
                            +
                        </span>
                    </div>
                    <!-- Upload Input -->
                    <div class="flex-1">
                        <input type="file" name="avatar" id="add_avatar" accept="image/*" class="block w-full text-xs text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none" onchange="previewAddAvatar(this)">
                        <p class="text-[10px] text-slate-500 mt-1">Accepts JPEG, PNG, JPG, GIF, WEBP. Max 2MB.</p>
                        <p id="add_avatar_error" class="text-[10px] text-danger mt-1 hidden"></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Min 8 chars" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Re-enter password" class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeAddModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Create User</x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        // Reset file input & error
        document.getElementById('add_avatar').value = '';
        document.getElementById('add_avatar_preview').src = '';
        document.getElementById('add_avatar_preview').classList.add('hidden');
        document.getElementById('add_avatar_placeholder').classList.remove('hidden');
        document.getElementById('add_avatar_error').classList.add('hidden');
        document.getElementById('add_avatar_error').innerText = '';

        document.getElementById('addModal').classList.remove('hidden');
    }

    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }

    function openEditModal(user) {
        document.getElementById('editForm').action = "{{ route('admin.users.update', ['id' => ':id']) }}".replace(':id', user.id);
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_mobile').value = user.mobile || '';
        document.getElementById('edit_status').value = user.status;
        
        @if(session('modal_open') === 'edit')
            @if(old('name')) document.getElementById('edit_name').value = "{!! addslashes(old('name')) !!}"; @endif
            @if(old('email')) document.getElementById('edit_email').value = "{!! addslashes(old('email')) !!}"; @endif
            @if(old('mobile')) document.getElementById('edit_mobile').value = "{!! addslashes(old('mobile')) !!}"; @endif
            @if(old('status')) document.getElementById('edit_status').value = "{!! addslashes(old('status')) !!}"; @endif
        @endif

        // Reset file input & error
        document.getElementById('edit_avatar').value = '';
        document.getElementById('edit_avatar_error').classList.add('hidden');
        document.getElementById('edit_avatar_error').innerText = '';

        // Handle avatar preview
        const previewImg = document.getElementById('edit_avatar_preview');
        const placeholder = document.getElementById('edit_avatar_placeholder');
        if (user.avatar) {
            previewImg.src = "/storage/" + user.avatar;
            previewImg.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            previewImg.src = "";
            previewImg.classList.add('hidden');
            placeholder.classList.remove('hidden');
            placeholder.innerText = user.name.substring(0, 2).toUpperCase();
        }

        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openPasswordModal(id, name) {
       document.getElementById('passwordForm').action = "{{ route('admin.users.reset_password', ['id' => ':id']) }}".replace(':id', id);
        document.getElementById('pwd_user_name').innerText = name;
        
        document.getElementById('passwordModal').classList.remove('hidden');
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').classList.add('hidden');
    }

    function previewAddAvatar(input) {
        const previewImg = document.getElementById('add_avatar_preview');
        const placeholder = document.getElementById('add_avatar_placeholder');
        const errorEl = document.getElementById('add_avatar_error');
        
        errorEl.classList.add('hidden');
        errorEl.innerText = '';

        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Check file size (max 2MB = 2048 * 1024 bytes)
            if (file.size > 2 * 1024 * 1024) {
                errorEl.innerText = "File size exceeds 2MB limit.";
                errorEl.classList.remove('hidden');
                input.value = ''; // Reset input
                previewImg.src = '';
                previewImg.classList.add('hidden');
                placeholder.classList.remove('hidden');
                return;
            }

            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                errorEl.innerText = "Invalid file type. Only JPEG, PNG, JPG, GIF, WEBP allowed.";
                errorEl.classList.remove('hidden');
                input.value = ''; // Reset input
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

    function previewEditAvatar(input) {
        const previewImg = document.getElementById('edit_avatar_preview');
        const placeholder = document.getElementById('edit_avatar_placeholder');
        const errorEl = document.getElementById('edit_avatar_error');
        
        errorEl.classList.add('hidden');
        errorEl.innerText = '';

        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Check file size (max 2MB = 2048 * 1024 bytes)
            if (file.size > 2 * 1024 * 1024) {
                errorEl.innerText = "File size exceeds 2MB limit.";
                errorEl.classList.remove('hidden');
                input.value = ''; // Reset input
                return;
            }

            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                errorEl.innerText = "Invalid file type. Only JPEG, PNG, JPG, GIF, WEBP allowed.";
                errorEl.classList.remove('hidden');
                input.value = ''; // Reset input
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
        @if(session('modal_open') === 'add')
            openAddModal();
        @elseif(session('modal_open') === 'edit' && session('edit_user_data'))
            openEditModal({!! json_encode(session('edit_user_data')) !!});
        @endif
    });
</script>
@endsection
