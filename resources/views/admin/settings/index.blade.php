@extends('layouts.admin')

@section('content')
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                background: '#141414',
                color: '#fff',
                confirmButtonColor: '#FF2D46'
            });
        </script>
    @endif

    <div
        class="h-20 bg-brand-dark/80 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 z-10 mb-8 rounded-2xl">
        <h1 class="text-xl font-bold">General Settings</h1>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ url('admin/settings') }}" method="POST">
            @csrf

            <div class="space-y-8">

                <!-- Pricing Section -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-8 shadow-xl">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                        <i class="fas fa-tags text-brand-red"></i> Plan Pricing (RM)
                    </h2>
                    <p class="text-gray-500 text-sm mb-6">Manage the display prices for all packages on the landing page.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Access Plan</label>
                            <input type="number" name="price_access" value="{{ $settings['price_access'] ?? 1980 }}"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Growth Plan</label>
                            <input type="number" name="price_growth" value="{{ $settings['price_growth'] ?? 2380 }}"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Authority Plan</label>
                            <input type="number" name="price_authority" value="{{ $settings['price_authority'] ?? 3980 }}"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Ultimate Plan</label>
                            <input type="number" name="price_ultimate" value="{{ $settings['price_ultimate'] ?? 4980 }}"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono">
                        </div>
                    </div>
                </div>

                <!-- Mockup Customization Section -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-8 shadow-xl">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                        <i class="fas fa-paint-brush text-blue-500"></i> Mockup Customization
                    </h2>
                    <p class="text-gray-500 text-sm mb-6">Update the "Dominate Search" section mockups dynamically.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Brand Name</label>
                            <input type="text" name="mockup_brand_name"
                                value="{{ $settings['mockup_brand_name'] ?? 'Your Brand' }}" placeholder="e.g. BrandThirty"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Brand URL</label>
                            <input type="text" name="mockup_brand_url"
                                value="{{ $settings['mockup_brand_url'] ?? 'yourbrand.com' }}"
                                placeholder="e.g. brandthirty.com"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Results Stats (JSON) Section -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-8 shadow-xl">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                        <i class="fas fa-chart-line text-green-500"></i> Results Stats (JSON)
                    </h2>
                    <p class="text-gray-500 text-sm mb-6">Override the "Results Speak Louder" graph data points. Must be
                        valid JSON.</p>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-gray-500">Graph Stats JSON</label>
                        <textarea name="graph_stats_json" rows="8"
                            class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono text-sm leading-relaxed"
                            placeholder='[{"clicks": "76k", "imp": "865k", "ctr": "8.8%", "pos": "8.5"}]'>{{ $settings['graph_stats_json'] ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Save Action -->
                <div class="flex justify-end pt-4 pb-12">
                    <button type="submit"
                        class="bg-brand-red hover:bg-red-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-red-900/50 transition transform hover:-translate-y-1">
                        Save Changes
                    </button>
                </div>

            </div>
        </form>
    </div>
@endsection