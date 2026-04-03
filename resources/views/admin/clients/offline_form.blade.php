@extends('layouts.admin')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-10">
            <a href="{{ url('admin/clients/offline') }}" class="text-slate-500 hover:text-white transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">
                    {{ $client ? 'Edit Client' : 'Add New Client' }}
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    {{ $client ? 'Update ' . $client->company_name . ' details.' : 'Register a new offline enterprise client.' }}
                </p>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ $client ? url('admin/clients/offline/' . $client->id) : url('admin/clients/offline') }}"
            method="POST" class="space-y-8" id="clientForm">
            @csrf

            {{-- Company Information --}}
            <div class="exec-card p-8">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-building text-slate-600"></i> Company Information
                </h3>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Company Name
                            <span class="text-brand-red">*</span></label>
                        <input type="text" name="company_name"
                            value="{{ old('company_name', $client->company_name ?? '') }}" required
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition"
                            placeholder="e.g. Acme Corporation">
                        @error('company_name')
                            <p class="mt-1.5 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- PIC Contact --}}
            <div class="exec-card p-8">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-user-tie text-slate-600"></i> Person In Charge (PIC)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">PIC Name
                            <span class="text-brand-red">*</span></label>
                        <input type="text" name="pic_name" value="{{ old('pic_name', $client->pic_name ?? '') }}" required
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition"
                            placeholder="e.g. John Doe">
                        @error('pic_name')
                            <p class="mt-1.5 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Phone Number
                            <span class="text-brand-red">*</span></label>
                        <input type="text" name="pic_phone" value="{{ old('pic_phone', $client->pic_phone ?? '') }}" required
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition"
                            placeholder="e.g. +60123456789">
                        @error('pic_phone')
                            <p class="mt-1.5 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Email <span
                                class="text-slate-600">(optional)</span></label>
                        <input type="email" name="pic_email" value="{{ old('pic_email', $client->pic_email ?? '') }}"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition"
                            placeholder="e.g. john@company.com">
                        @error('pic_email')
                            <p class="mt-1.5 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ═══ Billing Mode Selection ═══ --}}
            <div class="exec-card p-8">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-toggle-on text-slate-600"></i> Billing Mode
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Fixed Contract Card --}}
                    <label class="relative cursor-pointer group" id="label-fixed">
                        <input type="radio" name="billing_mode" value="fixed"
                            class="sr-only peer"
                            {{ old('billing_mode', $client->billing_mode ?? 'fixed') === 'fixed' ? 'checked' : '' }}
                            onchange="toggleBillingMode()">
                        <div class="rounded-xl border-2 p-5 transition-all duration-200
                            peer-checked:border-blue-500 peer-checked:bg-blue-500/5
                            border-border-subtle hover:border-slate-600 bg-slate-800/30">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                                    <i class="fas fa-file-contract text-blue-400"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">Fixed Contract</div>
                                    <div class="text-[10px] text-slate-500 uppercase tracking-wider">Total + Installments</div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                A fixed total amount paid in monthly installments. Progress is tracked until the full package is paid off.
                            </p>
                        </div>
                    </label>

                    {{-- Recurring Retainer Card --}}
                    <label class="relative cursor-pointer group" id="label-recurring">
                        <input type="radio" name="billing_mode" value="recurring"
                            class="sr-only peer"
                            {{ old('billing_mode', $client->billing_mode ?? 'fixed') === 'recurring' ? 'checked' : '' }}
                            onchange="toggleBillingMode()">
                        <div class="rounded-xl border-2 p-5 transition-all duration-200
                            peer-checked:border-emerald-500 peer-checked:bg-emerald-500/5
                            border-border-subtle hover:border-slate-600 bg-slate-800/30">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                                    <i class="fas fa-sync-alt text-emerald-400"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">Recurring Retainer</div>
                                    <div class="text-[10px] text-slate-500 uppercase tracking-wider">Monthly Subscription</div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                A recurring monthly fee with no total limit. The bill resets every month and tracks payment status per period.
                            </p>
                        </div>
                    </label>
                </div>

                @error('billing_mode')
                    <p class="mt-3 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                @enderror
            </div>

            {{-- Payment Terms --}}
            <div class="exec-card p-8">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-slate-600"></i> Payment Terms
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Total Package (only for Fixed Contract) --}}
                    <div id="total-package-field">
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Total
                            Package Amount (RM) <span class="text-brand-red">*</span></label>
                        <input type="number" name="total_package" id="total_package_input"
                            value="{{ old('total_package', $client->total_package ?? '') }}" min="1" step="0.01"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition"
                            placeholder="e.g. 10000">
                        @error('total_package')
                            <p class="mt-1.5 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Monthly
                            Payment (RM) <span class="text-brand-red">*</span></label>
                        <input type="number" name="monthly_payment"
                            value="{{ old('monthly_payment', $client->monthly_payment ?? '') }}" required min="1"
                            step="0.01"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition"
                            placeholder="e.g. 3000">
                        @error('monthly_payment')
                            <p class="mt-1.5 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Contract
                            Start Date <span class="text-brand-red">*</span></label>
                        <input type="date" name="contract_start"
                            value="{{ old('contract_start', $client ? $client->contract_start->format('Y-m-d') : '') }}"
                            required
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition [color-scheme:dark]">
                        @error('contract_start')
                            <p class="mt-1.5 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Monthly Due
                            Day <span class="text-brand-red">*</span></label>
                        <select name="due_day" required
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white focus:border-slate-500 focus:outline-none transition appearance-none">
                            @for($i = 1; $i <= 28; $i++)
                                <option value="{{ $i }}" {{ old('due_day', $client->due_day ?? 1) == $i ? 'selected' : '' }}>
                                    {{ $i }}{{ in_array($i, [1, 21]) ? 'st' : (in_array($i, [2, 22]) ? 'nd' : (in_array($i, [3, 23]) ? 'rd' : 'th')) }}
                                    of every month
                                </option>
                            @endfor
                        </select>
                        @error('due_day')
                            <p class="mt-1.5 text-xs text-red-400"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Notes & Status --}}
            <div class="exec-card p-8">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-sticky-note text-slate-600"></i> Additional Details
                </h3>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Notes <span
                                class="text-slate-600">(optional)</span></label>
                        <textarea name="notes" rows="3"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition resize-none"
                            placeholder="Any additional notes about this client...">{{ old('notes', $client->notes ?? '') }}</textarea>
                    </div>

                    @if($client)
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Contract
                                Status</label>
                            <select name="status"
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white focus:border-slate-500 focus:outline-none transition appearance-none">
                                <option value="active" {{ old('status', $client->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $client->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $client->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between">
                <a href="{{ url('admin/clients/offline') }}"
                    class="px-6 py-3 bg-slate-800/50 text-slate-400 hover:text-white border border-border-subtle rounded-xl text-sm font-semibold transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-8 py-3 bg-brand-red hover:bg-brand-red-hover text-white font-bold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-red-900/30">
                    <i class="fas fa-save mr-2"></i>
                    {{ $client ? 'Update Client' : 'Add Client' }}
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
    function toggleBillingMode() {
        const isFixed = document.querySelector('input[name="billing_mode"][value="fixed"]').checked;
        const totalPackageField = document.getElementById('total-package-field');
        const totalPackageInput = document.getElementById('total_package_input');

        if (isFixed) {
            totalPackageField.style.display = '';
            totalPackageInput.required = true;
        } else {
            totalPackageField.style.display = 'none';
            totalPackageInput.required = false;
            totalPackageInput.value = '';
        }
    }

    // Run on page load
    document.addEventListener('DOMContentLoaded', toggleBillingMode);
</script>
@endsection