@extends('layouts.admin')

@section('title', 'Invoice Layout Settings')
@section('page_title', 'Invoice & PDF Branding Configuration')


@section('content')
<div class="space-y-2">

    <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 font-semibold text-white text-sm">
            Platform Global Billing Brand Settings
        </div>
        
        <form action="{{ route('admin.settings.invoice.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-2">
            @csrf

            <!-- Logo & Prefix -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Default Invoice Header Logo</label>
                    <input type="file" name="default_logo" accept="image/*"
                        class="block w-full px-3.5 py-1.5 bg-secondary/30 border border-border-dark focus:outline-none rounded-xl text-xs text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30">
                    
                    @if($settings['default_logo'])
                        <div class="mt-3 flex items-center gap-3">
                            <img src="{{ asset('storage/' . $settings['default_logo']) }}" class="h-10 border border-border-dark rounded p-1 bg-white" alt="Default Logo">
                            <span class="text-[10px] text-slate-500 font-mono">Current logo path: {{ $settings['default_logo'] }}</span>
                        </div>
                    @else
                        <!-- <span class="text-[10px] text-slate-500 mt-1 block">Default placeholder brand logo used if empty.</span> -->
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Global Invoice Number Prefix</label>
                    <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" required 
                        placeholder="e.g. DH-"
                        class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white font-mono">
                    <!-- <span class="text-[10px] text-slate-500 mt-1 block">Prepended to invoice numbers (e.g. DH-1004).</span> -->
                </div>
            </div>

            <!-- Watermark & Tax -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Invoice PDF Watermark</label>
                    <select name="watermark" required 
                        class="block w-full px-3 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="no" {{ $settings['watermark'] === 'no' ? 'selected' : '' }}>Disabled (Clean layout)</option>
                        <option value="yes" {{ $settings['watermark'] === 'yes' ? 'selected' : '' }}>Enabled (Apply DukanHisab background watermark)</option>
                    </select>
                    <!-- <span class="text-[10px] text-slate-500 mt-1 block">Forces a watermark to verify system audits.</span> -->
                </div>

            </div>

            <!-- Footer terms -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Default Invoice Terms & Footer Notice</label>
                <textarea name="footer_text" rows="3" placeholder="Thank you for shopping with us! Standard billing terms apply..."
                    class="block w-full px-3.5 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">{{ old('footer_text', $settings['footer_text']) }}</textarea>
                <!-- <span class="text-[10px] text-slate-500 mt-1 block">Prints at the base of client invoices.</span> -->
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-4 border-t border-border-dark">
                <x-button type="submit" variant="primary">Save Brand Layout</x-button>
            </div>
        </form>
    </div>

</div>
@endsection
