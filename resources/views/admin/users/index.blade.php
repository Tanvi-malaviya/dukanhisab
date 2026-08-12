@extends('layouts.admin')

@section('title', 'Users & Shops Management')
@section('page_title', 'Users & Shops Management')
@section('page_subtitle')

@section('content')
    <div class="space-y-3">

        <!-- Metrics Cards Grid -->
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Users -->
            <div class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Users</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['total']) }}</h3>
                    <span
                        class="inline-flex items-center text-[10px] text-primary font-bold bg-primary/10 px-2 py-0.5 rounded-md">
                        Registered Clients
                    </span>
                </div>
                <span class="p-3 rounded-2xl bg-primary/10 border border-primary/20 shadow-2xs">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </span>
            </div>

            <!-- Active Users -->
            <div class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Users</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['active']) }}</h3>
                    <span
                        class="inline-flex items-center text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                        {{ round(($stats['active'] / max(1, $stats['total'])) * 100) }}% Active Ratio
                    </span>
                </div>
                <span class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-2xs">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>

            <!-- Total Shops -->
            <div class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Shops</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['total_shops']) }}</h3>
                    <span
                        class="inline-flex items-center text-[10px] text-amber-700 font-bold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                        Registered Shops
                    </span>
                </div>
                <span class="p-3 rounded-2xl bg-amber-50 border border-amber-200 shadow-2xs">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                </span>
            </div>

            <!-- Premium Ratio -->
            <div class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Premium Users</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['premium']) }}</h3>
                    <span
                        class="inline-flex items-center text-[10px] text-rose-700 font-bold bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md">
                        {{ round(($stats['premium'] / max(1, $stats['total'])) * 100) }}% Premium Ratio
                    </span>
                </div>
                <span class="p-3 rounded-2xl bg-rose-50 border border-rose-200 shadow-2xs">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <x-search-filter :action="route('admin.users.index')" placeholder="Search by name, email, mobile, or shop name...">
            <div class="w-full md:w-48">
                <select name="status" onchange="this.form.submit()"
                    class="block w-full px-3.5 py-2 bg-slate-50 border border-slate-200 focus:border-primary focus:outline-none rounded-xl text-sm text-slate-700 font-medium">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <x-slot name="actions">
                <x-button type="button" onclick="openAddModal()" variant="primary"
                    class="flex items-center gap-1.5 whitespace-nowrap cursor-pointer shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                    Add User
                </x-button>
            </x-slot>
        </x-search-filter>

        <!-- 3 CARDS PER ROW - ENTIRE CARD CLICKABLE COMPACT DESIGN -->
        @if($users->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @foreach($users as $user)
                    <div onclick="window.location.href='{{ route('admin.users.show', $user->id) }}'"
                        class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs hover:shadow-md hover:border-primary/50 transition-all flex flex-col justify-between space-y-3 cursor-pointer group">

                        <!-- Top User Profile Header with Action Icons & Status -->
                        <div class="flex items-start justify-between gap-2.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- PERFECT ROUND AVATAR -->
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name ?: 'User' }}"
                                        class="w-10 h-10 rounded-full object-cover border border-primary/30 shrink-0 aspect-square shadow-2xs">
                                @else
                                    <span
                                        class="w-10 h-10 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-extrabold text-sm uppercase shrink-0 aspect-square shadow-2xs">
                                        {{ substr($user->name ?: 'US', 0, 2) }}
                                    </span>
                                @endif
                                <!-- USER DETAILS -->
                                <div class="space-y-0.5 min-w-0">
                                    <a href="{{ route('admin.users.show', $user->id) }}" onclick="event.stopPropagation()"
                                        class="font-bold text-slate-800 text-sm group-hover:text-primary transition-colors truncate block"
                                        title="{{ $user->name ?: 'User #' . $user->id }}">
                                        {{ $user->name ?: 'User #' . $user->id }}
                                    </a>
                                    <p class="text-[11px] text-slate-500 font-medium truncate block" title="{{ $user->email }}">
                                        {{ $user->email }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $user->mobile ?? 'No Mobile' }}</p>
                                </div>
                            </div>

                            <!-- Top Right: Status & Impersonate Icon -->
                            <div class="flex items-center gap-1 shrink-0">
                                <span
                                    class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider mr-0.5 {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $user->status }}
                                </span>

                                <!-- Impersonate Icon -->
                                @if($user->status === 'active')
                                    <a href="{{ route('admin.users.login_as', $user->id) }}" onclick="event.stopPropagation()"
                                        class="w-7 h-7 rounded-lg bg-slate-100 border border-slate-200 text-primary hover:bg-primary hover:text-white transition-all flex items-center justify-center"
                                        title="Login as User">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Associated Shops Section & Action Buttons -->
                        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-black bg-teal-50 text-teal-700 border border-teal-200 shadow-2xs">
                                {{ $user->shops->count() }} {{ Str::plural('Shop', $user->shops->count()) }}
                            </span>

                            <div class="flex items-center gap-3">
                                <button type="button"
                                    onclick="event.stopPropagation(); openEditModal({{ json_encode($user->only(['id', 'name', 'email', 'mobile', 'status', 'avatar'])) }})"
                                    class="text-xs text-primary font-medium hover:underline cursor-pointer">
                                    Edit User
                                </button>
                                <button type="button"
                                    onclick="event.stopPropagation(); confirmDelete('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name ?: 'User' }}')"
                                    class="text-xs text-rose-600 font-medium hover:underline cursor-pointer">
                                    Delete
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state title="No users found"
                message="We couldn't find any user accounts matching your current search criteria or status filter."
                resetUrl="{{ route('admin.users.index') }}" />
        @endif

        <x-pagination :records="$users" />

    </div>

    <!-- USER MODAL 1: Edit Details Dialog -->
    <div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
            <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Edit Client Details</h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data" class="p-5 space-y-3">
                @csrf

                @if($errors->any() && session('modal_open') === 'edit')
                    <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
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
                        <input type="text" name="name" id="edit_name" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" id="edit_email" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                        <input type="text" name="mobile" id="edit_mobile" pattern="[0-9]{10}" maxlength="10"
                            title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Account Status</label>
                        <select name="status" id="edit_status" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Profile Image (Avatar)</label>
                    <div
                        class="flex items-center gap-4 p-3 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all">
                        <!-- Current Avatar Preview -->
                        <div id="edit_avatar_preview_container" class="flex-shrink-0">
                            <img id="edit_avatar_preview" src="" alt="Avatar"
                                class="w-12 h-12 rounded-full object-cover border border-border-dark hidden">
                            <span id="edit_avatar_placeholder"
                                class="w-12 h-12 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-sm uppercase">
                                ??
                            </span>
                        </div>
                        <!-- Upload Input -->
                        <div class="flex-1">
                            <input type="file" name="avatar" id="edit_avatar" accept="image/*"
                                class="block w-full text-xs text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none"
                                onchange="previewEditAvatar(this)">
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

    <!-- USER MODAL 2: Reset Password Dialog -->
    <div id="passwordModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closePasswordModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Reset Account Password</h3>
                <button onclick="closePasswordModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="passwordForm" method="POST" class="p-6 space-y-4">
                @csrf

                <p class="text-xs text-slate-400">Set a new password for <span class="text-white font-semibold"
                        id="pwd_user_name">User</span>.</p>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">New Password</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters"
                        class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Re-enter password"
                        class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closePasswordModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Reset Password</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- USER MODAL 3: Add User Dialog -->
    <div id="addModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
            <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Add New User</h3>
                <button onclick="closeAddModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data"
                class="p-5 space-y-3">
                @csrf

                @if($errors->any() && session('modal_open') === 'add')
                    <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
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
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Full Name"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@example.com"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" pattern="[0-9]{10}" maxlength="10"
                            title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Account Status</label>
                        <select name="status" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Profile Image (Avatar)</label>
                    <div
                        class="flex items-center gap-4 p-3 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all">
                        <!-- Preview Container -->
                        <div class="flex-shrink-0">
                            <img id="add_avatar_preview" src="" alt="Avatar Preview"
                                class="w-12 h-12 rounded-full object-cover border border-border-dark hidden">
                            <span id="add_avatar_placeholder"
                                class="w-12 h-12 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-sm">
                                +
                            </span>
                        </div>
                        <!-- Upload Input -->
                        <div class="flex-1">
                            <input type="file" name="avatar" id="add_avatar" accept="image/*"
                                class="block w-full text-xs text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none"
                                onchange="previewAddAvatar(this)">
                            <p class="text-[10px] text-slate-500 mt-1">Accepts JPEG, PNG, JPG, GIF, WEBP. Max 2MB.</p>
                            <p id="add_avatar_error" class="text-[10px] text-danger mt-1 hidden"></p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Password</label>
                        <input type="password" name="password" required placeholder="Min 8 chars"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required placeholder="Re-enter password"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closeAddModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Create User Account</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- SHOP MODAL 1: Add New Shop -->
    <div id="addShopModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddShopModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
            <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Add New Shop</h3>
                <button onclick="closeAddShopModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.shops.store') }}" method="POST" enctype="multipart/form-data"
                class="p-5 space-y-3">
                @csrf

                @if($errors->any() && session('modal_open') === 'add_shop')
                    <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
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
                        <select name="owner_id" id="add_shop_owner_id" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="">Select Owner...</option>
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ old('owner_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}
                                    ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Status</label>
                        <select name="status" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="active" {{ old('status') === 'active' || !old('status') ? 'selected' : '' }}>Active
                            </option>
                            <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" pattern="[0-9]{10}" maxlength="10"
                            title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">GSTIN (GST Number)</label>
                        <input type="text" name="gst_number" value="{{ old('gst_number') }}"
                            placeholder="e.g. 24AAAAA1121A1Z1"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Address</label>
                        <textarea name="address" rows="2"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white h-[58px] resize-none">{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Logo</label>
                        <div
                            class="flex items-center gap-3 p-2 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all h-[58px]">
                            <!-- Preview Container -->
                            <div class="flex-shrink-0">
                                <img id="add_logo_preview" src="" alt="Logo Preview"
                                    class="w-8 h-8 rounded-lg object-cover border border-border-dark hidden">
                                <span id="add_logo_placeholder"
                                    class="w-8 h-8 rounded-lg bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs">
                                    +
                                </span>
                            </div>
                            <!-- Upload Input -->
                            <div class="flex-1 min-w-0">
                                <input type="file" name="logo" id="add_logo" accept="image/*"
                                    class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none"
                                    onchange="previewAddLogo(this)">
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

    <!-- SHOP MODAL 2: Edit Shop -->
    <div id="editShopModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditShopModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
            <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Edit Shop</h3>
                <button onclick="closeEditShopModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="editShopForm" method="POST" enctype="multipart/form-data" class="p-5 space-y-3">
                @csrf
                @method('PUT')

                @if($errors->any() && session('modal_open') === 'edit_shop')
                    <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
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
                        <select name="owner_id" id="edit_shop_owner_id" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="">Select Owner...</option>
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Status</label>
                        <select name="status" id="edit_shop_status" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Name</label>
                        <input type="text" name="name" id="edit_shop_name" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" id="edit_shop_email"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                        <input type="text" name="mobile" id="edit_shop_mobile" pattern="[0-9]{10}" maxlength="10"
                            title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">GSTIN (GST Number)</label>
                        <input type="text" name="gst_number" id="edit_shop_gst_number" placeholder="e.g. 24AAAAA1121A1Z1"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Address</label>
                        <textarea name="address" id="edit_shop_address" rows="2"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white h-[58px] resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Logo</label>
                        <div
                            class="flex items-center gap-3 p-2 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all h-[58px]">
                            <!-- Current Logo Preview -->
                            <div id="edit_logo_preview_container" class="flex-shrink-0">
                                <img id="edit_logo_preview" src="" alt="Logo"
                                    class="w-8 h-8 rounded-lg object-cover border border-border-dark hidden">
                                <span id="edit_logo_placeholder"
                                    class="w-8 h-8 rounded-lg bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs uppercase">
                                    ??
                                </span>
                            </div>
                            <!-- Upload Input -->
                            <div class="flex-1 min-w-0">
                                <input type="file" name="logo" id="edit_logo" accept="image/*"
                                    class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none"
                                    onchange="previewEditLogo(this)">
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

    <!-- SHOP MODAL 3: Subscription Plan Override -->
    <div id="overrideModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closePlanOverrideModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Override Subscription Tier</h3>
                <button onclick="closePlanOverrideModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="overrideForm" method="POST" class="p-6 space-y-4">
                @csrf

                <p class="text-xs text-slate-400">Configure manually active plan for <span class="text-white font-semibold"
                        id="override_shop_name">Shop</span>.</p>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Select Subscription
                        Plan</label>
                    <select name="plan_id" required
                        class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} (₹{{ $plan->price }}/{{ $plan->billing_period }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Duration (Days)</label>
                    <input type="number" name="duration_days" value="30" min="1" required
                        class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closePlanOverrideModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Activate Subscription</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- SHOP MODAL 4: AJAX Shop Details Card -->
    <div id="detailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDetailsModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-2xl shadow-2xl relative z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Tenant Parameters</h3>
                <button onclick="closeDetailsModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6" id="detailsContent">
                <p class="text-sm text-slate-400 text-center py-4">Querying details from server...</p>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
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
                placeholder.innerText = (user.name || 'US').substring(0, 2).toUpperCase();
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

        function openAddShopModal() {
            document.getElementById('add_shop_owner_id').value = '';
            document.getElementById('addShopModal').classList.remove('hidden');
        }

        function openAddShopForUser(userId) {
            document.getElementById('add_shop_owner_id').value = userId;
            document.getElementById('addShopModal').classList.remove('hidden');
        }

        function closeAddShopModal() {
            document.getElementById('addShopModal').classList.add('hidden');
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

            document.getElementById('editShopModal').classList.remove('hidden');
        }

        function closeEditShopModal() {
            document.getElementById('editShopModal').classList.add('hidden');
        }

        function openPlanOverrideModal(userId, userName) {
            document.getElementById('overrideForm').action = "{{ route('admin.users.subscription', ['id' => ':id']) }}".replace(':id', userId);
            document.getElementById('override_shop_name').innerText = userName;
            document.getElementById('overrideModal').classList.remove('hidden');
        }

        function closePlanOverrideModal() {
            document.getElementById('overrideModal').classList.add('hidden');
        }

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
                                                            <p class="text-slate-400">Name: <span class="text-white font-semibold">${data.name}</span></p>
                                                            <p class="text-slate-400">Email: <span class="text-white font-semibold">${data.email || 'N/A'}</span></p>
                                                            <p class="text-slate-400">Mobile: <span class="text-white font-semibold">${data.mobile || 'N/A'}</span></p>
                                                            <p class="text-slate-400">GSTIN: <span class="text-white font-semibold font-mono">${data.gst_number || 'N/A'}</span></p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Ownership & Billing</h4>
                                                        <div class="space-y-2 text-xs">
                                                            <p class="text-slate-400">Owner: <span class="text-white font-semibold">${data.owner ? data.owner.name : 'Unknown'}</span></p>
                                                            <p class="text-slate-400">Current Plan: <span class="text-white font-semibold">${planName}</span></p>
                                                            <p class="text-slate-400">Status: <span class="capitalize font-semibold ${data.status === 'active' ? 'text-success' : 'text-danger'}">${data.status}</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Registered Address</h4>
                                                <p class="text-xs text-slate-300 bg-secondary/30 p-3 rounded-xl border border-border-dark">${data.address || 'No location details provided.'}</p>
                                            </div>
                                        `;
                    document.getElementById('detailsContent').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('detailsContent').innerHTML = '<p class="text-sm text-danger text-center py-4">Failed to fetch details.</p>';
                });
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }

        function previewAddAvatar(input) {
            const previewImg = document.getElementById('add_avatar_preview');
            const placeholder = document.getElementById('add_avatar_placeholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewEditAvatar(input) {
            const previewImg = document.getElementById('edit_avatar_preview');
            const placeholder = document.getElementById('edit_avatar_placeholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewAddLogo(input) {
            const previewImg = document.getElementById('add_logo_preview');
            const placeholder = document.getElementById('add_logo_placeholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewEditLogo(input) {
            const previewImg = document.getElementById('edit_logo_preview');
            const placeholder = document.getElementById('edit_logo_placeholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if(session('modal_open') === 'add')
                openAddModal();
            @elseif(session('modal_open') === 'edit' && session('edit_user_data'))
                openEditModal({!! json_encode(session('edit_user_data')) !!});
            @elseif(session('modal_open') === 'add_shop')
                openAddShopModal();
            @elseif(session('modal_open') === 'edit_shop' && session('edit_shop_data'))
                openEditShopModal({!! json_encode(session('edit_shop_data')) !!});
            @endif
            });
    </script>
@endsection