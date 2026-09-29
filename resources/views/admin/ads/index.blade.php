@extends('layouts.admin')

@section('title', 'Advertisements')
@section('page_title', 'Advertisements')

@section('content')
    <div class="space-y-6">

        @if(session('success'))
            <div class="px-4 py-2.5 rounded-xl bg-success/10 border border-success/30 text-success text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="px-4 py-2.5 rounded-xl bg-danger/10 border border-danger/30 text-danger text-sm">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <!-- Create campaign -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 font-semibold text-white text-sm">
                Create Advertisement Campaign
            </div>
            <form action="{{ route('admin.ads.store') }}" method="POST" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Title</label>
                    <input type="text" name="title" required value="{{ old('title') }}"
                        class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Type</label>
                        <select name="type" required
                            class="block w-full px-3 py-2.5 bg-secondary/30 border border-border-dark rounded-xl text-sm text-slate-300">
                            @foreach(['banner', 'interstitial', 'native', 'announcement'] as $t)
                                <option value="{{ $t }}" @selected(old('type') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Status</label>
                        <select name="status" required
                            class="block w-full px-3 py-2.5 bg-secondary/30 border border-border-dark rounded-xl text-sm text-slate-300">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Image URL</label>
                    <input type="url" name="image_url" value="{{ old('image_url') }}" placeholder="https://"
                        class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Target URL</label>
                    <input type="url" name="target_url" value="{{ old('target_url') }}" placeholder="https://"
                        class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Script / Embed Code (optional)</label>
                    <textarea name="script_code" rows="3"
                        class="block w-full px-3.5 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white font-mono">{{ old('script_code') }}</textarea>
                </div>
                <div class="md:col-span-2 flex justify-end pt-2 border-t border-border-dark">
                    <x-button type="submit" variant="primary">Create Campaign</x-button>
                </div>
            </form>
        </div>

        <!-- Campaign list -->
        <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-secondary/40 border-b border-border-dark text-[10px] font-semibold uppercase text-slate-400 tracking-wider">
                            <th class="px-4 py-2.5">Campaign</th>
                            <th class="px-4 py-2.5">Type</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5 text-right">Views</th>
                            <th class="px-4 py-2.5 text-right">Clicks</th>
                            <th class="px-4 py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark text-sm text-slate-700">
                        @forelse($ads as $ad)
                            <tr class="hover:bg-secondary/10 transition-colors">
                                <td class="px-4 py-2 text-xs font-bold text-slate-800">
                                    {{ $ad->title }}
                                    @if($ad->target_url)
                                        <div class="text-[10px] font-normal text-slate-500 truncate max-w-xs">{{ $ad->target_url }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-xs uppercase text-slate-500">{{ $ad->type }}</td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider {{ $ad->status === 'active' ? 'bg-success/20 text-success' : 'bg-slate-500/20 text-slate-400' }}">
                                        {{ $ad->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($ad->views ?? 0) }}</td>
                                <td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($ad->clicks ?? 0) }}</td>
                                <td class="px-4 py-2 text-right whitespace-nowrap">
                                    <form action="{{ route('admin.ads.toggle', $ad->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-primary hover:underline cursor-pointer">
                                            {{ $ad->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.ads.destroy', $ad->id) }}" method="POST" class="inline ml-3"
                                        onsubmit="return confirm('Delete this campaign?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-danger hover:underline cursor-pointer">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state colspan="6" title="No campaigns yet"
                                message="Create an advertisement campaign above to get started." />
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ads->hasPages())
                <div class="px-4 py-3 border-t border-border-dark bg-secondary/5">
                    {{ $ads->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
