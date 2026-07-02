@extends('layouts.admin')

@section('title', 'User Details - ' . $user->name)
@section('page_title', 'User Profile ')


@section('content')
    <div class="space-y-6">

        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <a href="{{ route('admin.users.index') }}" class="hover:text-primary transition-colors">Users Management</a>
            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-slate-700 font-semibold">{{ $user->name }}</span>
        </div>

        <!-- Page Header / Profile info -->
        <div
            class="bg-card-dark border border-border-dark p-6 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                        class="w-16 h-16 rounded-full object-cover border border-border-dark flex-shrink-0">
                @else
                    <span
                        class="w-16 h-16 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-2xl uppercase shadow-xs flex-shrink-0">
                        {{ substr($user->name, 0, 2) }}
                    </span>
                @endif
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold tracking-tight text-white">{{ $user->name }}</h1>
                        <span
                            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>
                    <!-- <p class="text-xs text-slate-500 mt-1">Client Account ID: <span
                                class="font-mono text-slate-400">#{{ $user->id }}</span></p> -->
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400 mt-2">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ $user->email }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            {{ $user->mobile ?? 'No Mobile Number' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            Joined {{ $user->created_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profile Quick Actions -->
            <div class="flex items-center gap-2">
                <!-- Edit details modal trigger -->
                <button
                    onclick="openEditModal({{ json_encode($user->only(['id', 'name', 'email', 'mobile', 'status', 'avatar'])) }})"
                    class="px-3.5 py-2 bg-primary text-white hover:bg-secondary text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-all border border-border-dark flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">Edit</span>
                </button>

                <!-- Impersonate -->
                @if($user->status === 'active')
                    <a href="{{ route('admin.users.login_as', $user->id) }}"
                        class="px-3.5 py-2 bg-info/10 hover:bg-info text-info hover:text-white rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3">
                            </path>
                        </svg>
                        <span class="hidden sm:inline">Login</span>
                    </a>
                @endif

                <!-- Reset Password -->
                <button onclick="openPasswordModal({{ $user->id }}, '{{ $user->name }}')"
                    class="px-3.5 py-2 bg-warning/10 hover:bg-warning text-warning hover:text-white rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m-5 4v5m0 0l-2-2m2 2l2-2M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                    </svg>
                    <span class="hidden sm:inline">Reset Password</span>
                </button>

                <!-- Delete -->
                <button type="button"
                    onclick="confirmDelete('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')"
                    class="px-3.5 py-2 bg-danger/10 hover:bg-danger text-danger hover:text-white rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">Delete</span>
                </button>
            </div>
        </div>

        <!-- Usage Statistics & Telemetry Grid -->
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">Overall Usage Telemetry</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Total Sales -->
                <div class="card-sales border p-4.5 rounded-xl flex items-center justify-between shadow-xs">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total Sales</p>
                        <h3 class="text-xl font-bold text-white mt-1">₹{{ number_format($overallStats->total_sales) }}</h3>
                    </div>
                    <span class="p-2 card-icon rounded-lg">
                        <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Total Transactions -->
                <div class="card-purchase border p-4.5 rounded-xl flex items-center justify-between shadow-xs">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Transactions</p>
                        <h3 class="text-xl font-bold text-white mt-1">{{ number_format($overallStats->total_transactions) }}
                        </h3>
                    </div>
                    <span class="p-2 card-icon rounded-lg">
                        <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Cash Balance -->
                <div class="card-cash border p-4.5 rounded-xl flex items-center justify-between shadow-xs">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Cash Balance</p>
                        <h3 class="text-xl font-bold text-white mt-1">₹{{ number_format($overallStats->cash_balance) }}</h3>
                    </div>
                    <span class="p-2 card-icon rounded-lg">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Bank Balance -->
                <div class="card-bank border p-4.5 rounded-xl flex items-center justify-between shadow-xs">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Bank Balance</p>
                        <h3 class="text-xl font-bold text-white mt-1">₹{{ number_format($overallStats->bank_balance) }}</h3>
                    </div>
                    <span class="p-2 card-icon rounded-lg">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Customer Due -->
                <div class="card-customer-due border p-4.5 rounded-xl flex items-center justify-between shadow-xs">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Customer Due</p>
                        <h3 class="text-xl font-bold text-white mt-1">₹{{ number_format($overallStats->customer_due) }}</h3>
                    </div>
                    <span class="p-2 card-icon rounded-lg">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Supplier Due -->
                <div class="card-supplier-due border p-4.5 rounded-xl flex items-center justify-between shadow-xs">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Supplier Due</p>
                        <h3 class="text-xl font-bold text-white mt-1">₹{{ number_format($overallStats->supplier_due) }}</h3>
                    </div>
                    <span class="p-2 card-icon rounded-lg">
                        <svg class="w-5 h-5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </span>
                </div>
            </div>
        </div>


    </div>

    <!-- Modal 1: Edit Details Dialog -->
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

    <!-- Modal 2: Reset Password Dialog -->
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

    <script>
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
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function openPasswordModal(id, name) {
            document.getElementById('passwordForm').action = "{{ route('admin.users.reset_password', ['id' => ':id']) }}".replace(':id', id);
            document.getElementById('pwd_user_name').innerText = name;

            document.getElementById('passwordModal').classList.remove('hidden');
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if(session('modal_open') === 'edit' && session('edit_user_data'))
                openEditModal({!! json_encode(session('edit_user_data')) !!});
            @endif
            });
    </script>
@endsection