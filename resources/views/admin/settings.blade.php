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
            <h1 class="text-3xl font-bold text-white tracking-tight">System Settings</h1>
            <p class="text-slate-500 text-sm mt-1">Configure plan pricing.</p>
        </div>

        <form action="{{ url('admin/settings') }}" method="POST">
            @csrf

            <div class="space-y-8">
                <div class="exec-card p-8">
                    <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-3">
                        <i class="fas fa-tags text-brand-red text-sm"></i> Plan Pricing (RM)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Access Plan</label>
                            <input type="number" name="price_access" value="{{ $settings['price_access'] ?? 1000 }}"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Growth Plan</label>
                            <input type="number" name="price_growth" value="{{ $settings['price_growth'] ?? 2000 }}"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Authority Plan</label>
                            <input type="number" name="price_authority" value="{{ $settings['price_authority'] ?? 5000 }}"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Ultimate Plan</label>
                            <input type="number" name="price_ultimate" value="{{ $settings['price_ultimate'] ?? 7000 }}"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3.5 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-brand-red hover:bg-brand-red-hover text-white font-semibold py-3 px-8 rounded-xl shadow-lg shadow-red-900/20 transition-all duration-200">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
