@extends('layouts.admin')

@section('title', 'App Settings')
@section('page_title', 'App Config & Version Control')

@section('content')
<div class="space-y-6 max-w-4xl">

    <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 font-semibold text-white text-sm">
            Platform Application Configuration Parameters
        </div>
        
        <form action="{{ route('admin.settings.app.update') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Version Config -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Live Application Version</label>
                    <input type="text" name="app_version" value="{{ old('app_version', $settings['app_version']) }}" required 
                        class="block w-full px-3.5 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white font-mono">
                    <span class="text-[10px] text-slate-500 mt-1 block">Current build version.</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Min Required Version</label>
                    <input type="text" name="min_required_version" value="{{ old('min_required_version', $settings['min_required_version']) }}" required 
                        class="block w-full px-3.5 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white font-mono">
                    <span class="text-[10px] text-slate-500 mt-1 block">Minimum client version to run.</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Force Client Update</label>
                    <select name="force_update" required 
                        class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="no" {{ $settings['force_update'] === 'no' ? 'selected' : '' }}>No (Optional Update)</option>
                        <option value="yes" {{ $settings['force_update'] === 'yes' ? 'selected' : '' }}>Yes (Enforce update popup)</option>
                    </select>
                    <span class="text-[10px] text-slate-500 mt-1 block">If yes, blocks older versions.</span>
                </div>
            </div>

            <!-- Maintenance & Announcement -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="md:col-span-1">
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">System Maintenance Gate</label>
                    <select name="maintenance_mode" required 
                        class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        <option value="no" {{ $settings['maintenance_mode'] === 'no' ? 'selected' : '' }}>Inactive (Online)</option>
                        <option value="yes" {{ $settings['maintenance_mode'] === 'yes' ? 'selected' : '' }}>Active (Maintenance Screen)</option>
                    </select>
                    <span class="text-[10px] text-slate-500 mt-1 block">If active, locks tenant panel sync.</span>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Announcements & Maintenance Warning Ticker</label>
                    <input type="text" name="announcement_message" value="{{ old('announcement_message', $settings['announcement_message']) }}" 
                        placeholder="e.g. System upgrade scheduled on Sunday from 2 AM to 4 AM..."
                        class="block w-full px-3.5 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    <span class="text-[10px] text-slate-500 mt-1 block">Displayed on client dashboards if filled.</span>
                </div>
            </div>

            <!-- Feature Flags JSON -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Feature Flags Configuration (JSON Format)</label>
                <textarea name="feature_flags" required rows="5" 
                    class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white font-mono">{{ old('feature_flags', $settings['feature_flags']) }}</textarea>
                <span class="text-[10px] text-slate-500 mt-1 block">Specify feature switches. Example: <code>{"whatsapp": false, "multi_currency": true}</code></span>
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-4 border-t border-border-dark">
                <x-button type="submit" variant="primary">Apply Global Settings</x-button>
            </div>
        </form>
    </div>

</div>
@endsection
