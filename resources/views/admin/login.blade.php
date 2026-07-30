<!DOCTYPE html>
<html lang="en" class="h-full bg-bg-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DukanHisab Super Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Background Design Orbs -->
    <div class="absolute w-[40rem] h-[40rem] rounded-full bg-primary/10 blur-[120px] -top-40 -left-40"></div>
    <div class="absolute w-[40rem] h-[40rem] rounded-full bg-info/10 blur-[120px] -bottom-40 -right-40"></div>

    <div class="w-full max-w-sm bg-card-dark/60 backdrop-blur-xl border border-border-dark p-6 rounded-2xl shadow-2xl relative z-10">
        
        <!-- Logo Header -->
        <div class="text-center mb-6">
            <span class="inline-flex p-2.5 rounded-2xl bg-primary/10 text-primary mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </span>
            <h1 class="text-xl font-bold tracking-tight text-white">Dukan<span class="text-primary">Hisab</span></h1>
            <p class="text-xs text-slate-400 mt-1.5">Super Admin Control Center</p>
        </div>

        <!-- Session Status alerts -->
        @if(session('success'))
            <div class="mb-3.5 p-3 rounded-lg bg-success/10 border border-success/30 text-success text-xs flex items-center gap-2 alert-box">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="ml-auto text-current opacity-70 hover:opacity-100 transition-opacity">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-3.5 p-3 rounded-lg bg-danger/10 border border-danger/30 text-danger text-xs flex items-center gap-2 alert-box">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="ml-auto text-current opacity-70 hover:opacity-100 transition-opacity">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                    </span>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder="admin@dukanhisab.com" 
                        class="block w-full pl-9 pr-3.5 py-2.5 bg-secondary/40 border border-border-dark focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none transition-all">
                </div>
                @error('email')
                    <span class="text-xs text-danger mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 h-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </span>
                    <input type="password" name="password" id="password" required placeholder="••••••••" 
                        class="block w-full pl-9 pr-10 py-2.5 bg-secondary/40 border border-border-dark focus:border-primary focus:ring-1 focus:ring-primary rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none transition-all">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 w-10 flex items-center justify-center text-slate-400 hover:text-slate-200 transition-colors focus:outline-none bg-transparent h-full">
                        <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <span class="text-xs text-danger mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-0.5">
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" value="1" class="w-3.5 h-3.5 rounded text-primary focus:ring-primary bg-secondary/40 border-border-dark">
                    <label for="remember" class="ml-2 text-xs text-slate-400 font-medium select-none cursor-pointer">Remember me</label>
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 px-4 bg-primary hover:bg-primary-hover active:scale-[0.98] text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-primary/20 mt-3 cursor-pointer">
                Enter Dashboard
            </button>
        </form>

        <div class="mt-6 text-center text-slate-500 text-[10px]">
            &copy; {{ date('Y') }} DukanHisab SaaS Inc. All rights reserved.
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }

        // Auto-dismiss alerts after 4 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-box');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 4000);
    </script>
</body>
</html>
