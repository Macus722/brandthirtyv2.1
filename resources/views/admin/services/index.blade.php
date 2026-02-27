@extends('layouts.admin')

@section('content')
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                background: '#0f172a',
                color: '#e2e8f0',
                confirmButtonColor: '#E31E24',
            });
        </script>
    @endif

    <div class="max-w-4xl mx-auto">
        <div class="mb-10">
            <h1 class="text-3xl font-bold text-white tracking-tight">Service Manager</h1>
            <p class="text-slate-500 text-sm mt-1">Configure pricing, mockups, and landing page data.</p>
        </div>

        <form action="{{ url('admin/services') }}" method="POST">
            @csrf

            <div class="space-y-8">
                <!-- Pricing -->
                <div class="exec-card p-8">
                    <h2 class="text-lg font-semibold text-white mb-2 flex items-center gap-3">
                        <i class="fas fa-tags text-brand-red text-sm"></i> Plan Pricing (RM)
                    </h2>
                    <p class="text-slate-500 text-sm mb-7">Manage the display prices for all packages on the landing page.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Access Plan</label>
                            <input type="number" name="price_access" value="{{ $settings['price_access'] ?? 1980 }}"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Growth Plan</label>
                            <input type="number" name="price_growth" value="{{ $settings['price_growth'] ?? 2380 }}"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Authority Plan</label>
                            <input type="number" name="price_authority" value="{{ $settings['price_authority'] ?? 3980 }}"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Ultimate Plan</label>
                            <input type="number" name="price_ultimate" value="{{ $settings['price_ultimate'] ?? 4980 }}"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                    </div>
                </div>

                <!-- Mockup -->
                <div class="exec-card p-8">
                    <h2 class="text-lg font-semibold text-white mb-2 flex items-center gap-3">
                        <i class="fas fa-paint-brush text-blue-400 text-sm"></i> Mockup Customization
                    </h2>
                    <p class="text-slate-500 text-sm mb-7">Update the "Dominate Search" section mockups dynamically.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Brand Name</label>
                            <input type="text" name="mockup_brand_name"
                                value="{{ $settings['mockup_brand_name'] ?? 'Your Brand' }}" placeholder="e.g. BrandThirty"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Brand URL</label>
                            <input type="text" name="mockup_brand_url"
                                value="{{ $settings['mockup_brand_url'] ?? 'yourbrand.com' }}" placeholder="e.g. brandthirty.com"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                    </div>
                </div>

                <!-- Graph Stats -->
                <div class="exec-card p-8">
                    <h2 class="text-lg font-semibold text-white mb-2 flex items-center gap-3">
                        <i class="fas fa-chart-line text-emerald-400 text-sm"></i> Results Stats (JSON)
                    </h2>
                    <p class="text-slate-500 text-sm mb-7">Override the "Results Speak Louder" graph data points. Must be valid JSON.</p>

                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Graph Stats JSON</label>
                        <textarea name="graph_stats_json" rows="8"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm leading-relaxed"
                            placeholder='[{"clicks": "76k", "imp": "865k", "ctr": "8.8%", "pos": "8.5"}]'>{{ $settings['graph_stats_json'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-2 pb-12">
                    <button type="submit"
                        class="bg-brand-red hover:bg-brand-red-hover text-white font-semibold py-3 px-8 rounded-xl shadow-lg shadow-red-900/20 transition-all duration-200">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
