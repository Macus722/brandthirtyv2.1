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
        <h1 class="text-xl font-bold">System Settings</h1>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ url('admin/settings') }}" method="POST">
            @csrf

            <div class="space-y-6">

                <!-- Pricing Card -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-8 shadow-xl">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                        <i class="fas fa-tags text-brand-red"></i> Plan Pricing (RM)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Access Plan</label>
                            <input type="number" name="price_access" value="{{ $settings['price_access'] ?? 1000 }}"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Growth Plan</label>
                            <input type="number" name="price_growth" value="{{ $settings['price_growth'] ?? 2000 }}"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Authority Plan</label>
                            <input type="number" name="price_authority" value="{{ $settings['price_authority'] ?? 5000 }}"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-gray-500">Ultimate Plan</label>
                            <input type="number" name="price_ultimate" value="{{ $settings['price_ultimate'] ?? 7000 }}"
                                class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition font-mono">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-brand-red hover:bg-red-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-red-900/50 transition transform hover:-translate-y-1">
                        Save Changes
                    </button>
                </div>

            </div>
        </form>
    </div>
@endsection