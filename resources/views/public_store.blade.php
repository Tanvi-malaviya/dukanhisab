<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>{{ $settings['seo_title'] ?? $shop->name . ' - Online Catalog' }}</title>
    <meta name="description" content="{{ $settings['seo_description'] ?? 'Browse our catalog of products and get in touch with us.' }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '{{ $settings['theme_color'] ?? "#0F766E" }}',
                            hover: '{{ $settings['theme_color'] ?? "#0F766E" }}dd',
                            light: '{{ $settings['theme_color'] ?? "#0F766E" }}15'
                        }
                    },
                    fontFamily: {
                        sans: ['"Outfit"', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(15, 118, 110, 0.2);
            border-radius: 100px;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 flex flex-col">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200/60 transition-all">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <!-- Brand / Logo -->
            <a href="#" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold overflow-hidden shadow-inner flex-shrink-0">
                    @if($shop->logo)
                        <img src="/storage/{{ $shop->logo }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-lg">{{ strtoupper(substr($shop->name, 0, 2)) }}</span>
                    @endif
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-slate-900 leading-tight">{{ $shop->name }}</span>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Verified Business</span>
                </div>
            </a>

            <!-- Quick Action: Contact -->
            <div class="flex items-center gap-2">
                @if($shop->mobile)
                    <a href="tel:{{ $shop->mobile }}" 
                       class="p-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary/20 border border-primary/20 transition-all flex items-center justify-center hover:scale-105" 
                       title="Call Shop">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $shop->mobile) }}" 
                       target="_blank"
                       class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-all flex items-center justify-center hover:scale-105" 
                       title="Chat on WhatsApp">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.116-2.905-6.993-1.876-1.878-4.36-2.911-7.004-2.912-5.445 0-9.87 4.42-9.873 9.864 0 1.69.444 3.34 1.288 4.793L1.085 22.91l6.562-1.722zM17.487 14.39c-.3-.15-1.774-.875-2.049-.976-.276-.1-.476-.15-.676.15-.2.3-.775.976-.95 1.176-.175.2-.35.225-.65.075-.3-.15-1.267-.467-2.413-1.49-1.146-1.023-1.92-2.285-2.145-2.686-.225-.4-.024-.615.126-.764.135-.135.3-.35.45-.525.15-.175.2-.3.3-.5.1-.2.05-.375-.025-.525-.075-.15-.676-1.628-.926-2.228-.244-.588-.493-.507-.676-.516-.175-.008-.375-.01-.575-.01-.2 0-.525.075-.8.376-.275.3-1.05 1.026-1.05 2.502 0 1.476 1.075 2.903 1.225 3.102.15.2 2.115 3.23 5.124 4.53.715.31 1.273.495 1.708.633.717.227 1.37.195 1.887.118.575-.085 1.775-.725 2.025-1.425.25-.7.25-1.3 1.75-1.425-.075-.125-.275-.225-.575-.375z"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Hero / Banner Section -->
    <section class="relative bg-gradient-to-b from-primary/10 via-primary/5 to-transparent pt-12 pb-10 px-4 text-center">
        <div class="max-w-3xl mx-auto space-y-4">
            <h1 class="text-4xl md:text-5xl font-black tracking-tight text-slate-900 leading-tight">
                Welcome to <span class="text-primary">{{ $shop->name }}</span>
            </h1>
            
            @if(isset($settings['about_us']) && $settings['about_us'])
                <p class="text-slate-600 max-w-xl mx-auto leading-relaxed text-sm md:text-base">
                    {{ $settings['about_us'] }}
                </p>
            @else
                <p class="text-slate-500 max-w-xl mx-auto leading-relaxed text-sm">
                    Discover our catalog of products and get in touch with us directly for orders, inquiries, or pricing details.
                </p>
            @endif

            <!-- Contact Badges -->
            <div class="flex flex-wrap justify-center gap-2.5 pt-2">
                @if($shop->city)
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white border border-slate-200/80 shadow-sm text-xs font-semibold text-slate-600">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $shop->city }}, {{ $shop->state ?? 'India' }}
                    </span>
                @endif
                @if($shop->email)
                    <a href="mailto:{{ $shop->email }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white border border-slate-200/80 shadow-sm text-xs font-semibold text-slate-600 hover:text-primary transition-colors">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ $shop->email }}
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 py-8 space-y-12">
        
        <!-- Product Catalog Section (Full Width) -->
        @if(!isset($settings['show_catalog']) || $settings['show_catalog'])
            <div id="productCatalogSection" class="space-y-6">
                <!-- Filters and Search -->
                <div class="bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                        <h2 class="text-xl font-extrabold text-slate-900">Products & Catalog</h2>
                        
                        <!-- Search Box -->
                        <div class="relative max-w-xs w-full">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input id="searchInput" type="text" placeholder="Search catalog..." 
                                   class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        </div>
                    </div>

                    <!-- Category Filter Tabs -->
                    @if($categories->count() > 0)
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar scroll-smooth">
                            <button onclick="filterCategory('All')" 
                                    class="category-tab px-4 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap bg-primary text-white" 
                                    data-category="All">
                                All Products
                            </button>
                            @foreach($categories as $categoryName)
                                <button onclick="filterCategory('{{ $categoryName }}')" 
                                        class="category-tab px-4 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200/75" 
                                        data-category="{{ $categoryName }}">
                                    {{ $categoryName }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Product Grid -->
                <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @forelse($products as $product)
                        <div class="product-card bg-white rounded-2xl border border-slate-200/80 hover:border-primary/30 p-5 flex flex-col shadow-sm hover:shadow-md transition-all group"
                             data-name="{{ strtolower($product->name) }}"
                             data-category="{{ $product->category->name ?? '' }}">
                            
                            <!-- Badges -->
                            <div class="flex justify-between items-center gap-2 mb-3">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    {{ $product->category->name ?? 'General' }}
                                </span>
                                @if($product->stock > 0)
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-[9px] font-bold text-emerald-600">In Stock</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-[9px] font-bold text-rose-600">Out of Stock</span>
                                @endif
                            </div>

                            <!-- Product Name -->
                            <h3 class="font-bold text-slate-800 text-sm group-hover:text-primary transition-colors flex-1 mb-2">
                                {{ $product->name }}
                            </h3>

                            <!-- Price details -->
                            <div class="flex items-end justify-between gap-4 pt-3 border-t border-slate-100">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Selling Price</span>
                                    <span class="font-extrabold text-base text-slate-900">₹{{ number_format($product->selling_price, 2) }}</span>
                                </div>

                                <!-- Inquiry Call button -->
                                @if($shop->mobile)
                                    <a href="tel:{{ $shop->mobile }}"
                                       class="inline-flex items-center justify-center p-2.5 rounded-xl bg-primary text-white hover:bg-primary-hover shadow-sm transition-all hover:scale-105"
                                       title="Call Shop">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-8 text-center text-slate-500">
                            No products are currently available in the catalog.
                        </div>
                    @endforelse
                </div>

                <!-- Client-side Pagination controls -->
                <div id="paginationControls" class="flex items-center justify-center gap-2 pt-4"></div>
            </div>
        @endif

        <!-- Section Divider -->
        <hr class="border-slate-200/60 my-4">

        <!-- Shop Cover Image & Info Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Side: Shop Cover Image -->
            <div class="lg:col-span-6">
                <div class="rounded-3xl overflow-hidden border border-slate-200/80 shadow-md h-80 bg-slate-100 dark:bg-gray-800">
                    @if(isset($settings['shop_image']) && $settings['shop_image'])
                        <img src="/storage/{{ $settings['shop_image'] }}" class="w-full h-full object-contain bg-white" alt="{{ $shop->name }}">
                    @else
                        <!-- Premium fallback abstract cover image -->
                        <div class="w-full h-full bg-gradient-to-br from-primary/20 via-primary/5 to-white flex items-center justify-center p-6">
                            <div class="text-center space-y-2">
                                <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center mx-auto text-2xl font-bold">
                                    {{ strtoupper(substr($shop->name, 0, 1)) }}
                                </div>
                                <h3 class="font-extrabold text-slate-800 text-lg">{{ $shop->name }}</h3>
                                <p class="text-xs text-slate-500 max-w-xs">Your trusted partner for quality products and service.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Side: Business Details, Inquiry & Follow Cards -->
            <div class="lg:col-span-6 space-y-6">
                <!-- Business Info Card -->
                <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-sm space-y-3">
                    <h3 class="font-extrabold text-slate-900 border-b border-slate-100 pb-1.5">Business Details</h3>
                    
                    <div class="space-y-3 text-sm">
                        @if($shop->address)
                            @php
                                $addressParts = array_filter([$shop->address, $shop->city, $shop->state]);
                                $addressStr = implode(', ', $addressParts);
                                if ($shop->pincode) {
                                    $addressStr .= ($addressStr ? ' - ' : '') . $shop->pincode;
                                }
                            @endphp
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($addressStr) }}" 
                               target="_blank" 
                               class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 group">
                                <span class="p-1.5 rounded-lg bg-primary/10 text-primary mt-0.5 flex-shrink-0 flex items-center justify-center border border-primary/20 transition-all group-hover:bg-primary group-hover:text-white">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-400 uppercase tracking-wider flex items-center gap-1">
                                        Address
                                        <svg class="w-3.5 h-3.5 opacity-0 group-hover:opacity-100 transition-opacity text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </h4>
                                    <p class="text-slate-700 leading-relaxed text-xs">{{ $addressStr }}</p>
                                </div>
                            </a>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @if($shop->mobile)
                                <a href="tel:{{ $shop->mobile }}" 
                                   class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 group">
                                    <span class="p-1.5 rounded-lg bg-primary/10 text-primary mt-0.5 flex-shrink-0 flex items-center justify-center border border-primary/20 transition-all group-hover:bg-primary group-hover:text-white">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-xs text-slate-400 uppercase tracking-wider">Phone / WhatsApp</h4>
                                        <p class="text-slate-700 font-semibold text-xs truncate">{{ $shop->mobile }}</p>
                                    </div>
                                </a>
                            @endif

                            @if($shop->email)
                                <a href="mailto:{{ $shop->email }}" 
                                   class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 group">
                                    <span class="p-1.5 rounded-lg bg-primary/10 text-primary mt-0.5 flex-shrink-0 flex items-center justify-center border border-primary/20 transition-all group-hover:bg-primary group-hover:text-white">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-xs text-slate-400 uppercase tracking-wider">Email Address</h4>
                                        <p class="text-slate-700 text-xs truncate">{{ $shop->email }}</p>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>



                <!-- Follow Us Card -->
                @if(isset($settings['social_facebook']) || isset($settings['social_instagram']) || isset($settings['social_twitter']))
                    <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-sm space-y-3">
                        <h3 class="font-extrabold text-slate-900 border-b border-slate-100 pb-1.5">Follow Us</h3>
                        <div class="flex items-center gap-2.5">
                            @if(isset($settings['social_facebook']) && $settings['social_facebook'])
                                <a href="{{ $settings['social_facebook'] }}" target="_blank" class="p-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                                </a>
                            @endif
                            @if(isset($settings['social_instagram']) && $settings['social_instagram'])
                                <a href="{{ $settings['social_instagram'] }}" target="_blank" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                </a>
                            @endif
                            @if(isset($settings['social_twitter']) && $settings['social_twitter'])
                                <a href="{{ $settings['social_twitter'] }}" target="_blank" class="p-2 rounded-xl bg-slate-50 text-slate-800 hover:bg-slate-100 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

        </div>
        
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/60 mt-12 py-6 text-center text-xs text-slate-400">
        <p class="font-medium">&copy; {{ date('Y') }} {{ $shop->name }}. All rights reserved.</p>
        <p class="mt-1 font-semibold flex items-center justify-center gap-1">
            Powered by 
            <a href="https://dukanhisab.com" target="_blank" class="text-primary hover:underline">
                Dukan<span class="text-emerald-500 font-extrabold">Hisab</span>
            </a>
        </p>
    </footer>

    <!-- Client-side Vanilla JS Filter & Inquiry logic -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const cards = Array.from(document.querySelectorAll('.product-card'));
        const paginationControls = document.getElementById('paginationControls');
        const pageSize = 9; // 3 columns x 3 rows
        let currentPage = 1;
        let currentCategory = 'All';

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                currentPage = 1;
                applyFilters();
            });
        }

        function filterCategory(categoryName) {
            currentCategory = categoryName;
            currentPage = 1;
            
            // Toggle active styles on tabs
            document.querySelectorAll('.category-tab').forEach(tab => {
                if (tab.getAttribute('data-category') === categoryName) {
                    tab.classList.add('bg-primary', 'text-white');
                    tab.classList.remove('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200/75');
                } else {
                    tab.classList.remove('bg-primary', 'text-white');
                    tab.classList.add('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200/75');
                }
            });

            applyFilters();
        }

        function applyFilters() {
            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            
            // 1. First, find all matching cards
            const visibleCards = [];
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const category = card.getAttribute('data-category');
                
                const matchesQuery = name.includes(query);
                const matchesCategory = currentCategory === 'All' || category === currentCategory;
                
                if (matchesQuery && matchesCategory) {
                    visibleCards.push(card);
                } else {
                    card.style.display = 'none';
                }
            });

            // 2. Paginate visible cards
            const totalItems = visibleCards.length;
            const totalPages = Math.ceil(totalItems / pageSize);
            
            if (currentPage > totalPages && totalPages > 0) {
                currentPage = totalPages;
            }

            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = startIndex + pageSize;

            visibleCards.forEach((card, index) => {
                if (index >= startIndex && index < endIndex) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            // 3. Render pagination controls
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            if (!paginationControls) return;
            paginationControls.innerHTML = '';
            
            if (totalPages <= 1) {
                return; // Hide pagination if only 1 page
            }

            // Previous Button
            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            `;
            prevBtn.className = `p-2.5 rounded-xl border text-sm font-semibold transition-all ${currentPage === 1 ? 'border-slate-200 text-slate-300 cursor-not-allowed' : 'border-slate-300 text-slate-600 hover:bg-slate-50'}`;
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    applyFilters();
                    const section = document.getElementById('productCatalogSection');
                    if (section) {
                        window.scrollTo({ top: section.offsetTop - 100, behavior: 'smooth' });
                    }
                }
            };
            paginationControls.appendChild(prevBtn);

            // Page Number Buttons
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.innerText = i;
                pageBtn.className = `px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all ${currentPage === i ? 'bg-primary text-white shadow-sm' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'}`;
                pageBtn.onclick = () => {
                    currentPage = i;
                    applyFilters();
                    const section = document.getElementById('productCatalogSection');
                    if (section) {
                        window.scrollTo({ top: section.offsetTop - 100, behavior: 'smooth' });
                    }
                };
                paginationControls.appendChild(pageBtn);
            }

            // Next Button
            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            `;
            nextBtn.className = `p-2.5 rounded-xl border text-sm font-semibold transition-all ${currentPage === totalPages ? 'border-slate-200 text-slate-300 cursor-not-allowed' : 'border-slate-300 text-slate-600 hover:bg-slate-50'}`;
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    applyFilters();
                    const section = document.getElementById('productCatalogSection');
                    if (section) {
                        window.scrollTo({ top: section.offsetTop - 100, behavior: 'smooth' });
                    }
                }
            };
            paginationControls.appendChild(nextBtn);
        }

        // WhatsApp inquiry builder
        function sendInquiry(e) {
            e.preventDefault();
            const name = document.getElementById('inqName').value.trim();
            const message = document.getElementById('inqMessage').value.trim();
            const shopName = "{{ rawurlencode($shop->name) }}";
            const shopMobile = "{{ preg_replace('/\D/', '', $shop->mobile) }}";
            
            const text = `Hi! My name is ${name}. I am visiting your online storefront '${shopName}' and would like to inquire about: ${message}`;
            const whatsappUrl = `https://wa.me/${shopMobile}?text=${encodeURIComponent(text)}`;
            
            window.open(whatsappUrl, '_blank');
        }

        // Initialize pagination on load
        document.addEventListener('DOMContentLoaded', () => {
            applyFilters();
        });
    </script>
</body>
</html>
