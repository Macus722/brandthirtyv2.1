@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <a href="{{ url('admin/clients') }}" class="text-slate-500 hover:text-white transition">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Offline Clients</h1>
                </div>
                <p class="text-slate-500 text-sm ml-7">Manage enterprise clients with monthly payment tracking.</p>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ url('admin/clients/offline') }}" method="GET" class="relative w-full sm:w-64">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search clients..."
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none transition">
                </form>
                <a href="{{ url('admin/clients/offline/reports') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800/50 text-slate-300 hover:text-white hover:bg-white/5 border border-border-subtle font-semibold text-sm rounded-xl transition-all duration-200 whitespace-nowrap">
                    <i class="fas fa-chart-bar text-xs"></i> Reports
                </a>
                <a href="{{ url('admin/clients/offline/create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-red hover:bg-brand-red-hover text-white font-semibold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-red-900/30 whitespace-nowrap">
                    <i class="fas fa-plus text-xs"></i> Add Client
                </a>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ url('admin/clients/offline') }}"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200
               {{ !request('status') && !request('mode') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'bg-slate-800/50 text-slate-400 hover:text-white hover:bg-white/5 border border-border-subtle' }}">
                All
            </a>
            @foreach(['active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                <a href="{{ url('admin/clients/offline?status=' . $key) }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200
                   {{ request('status') === $key ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'bg-slate-800/50 text-slate-400 hover:text-white hover:bg-white/5 border border-border-subtle' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Clients Table --}}
        <div class="exec-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border-subtle text-left">
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Company</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">PIC Contact</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Monthly</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Due Day</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Last Month</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Current Month</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Progress / Stats</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            @php
                                $lastStatus = $client->getLastMonthStatus();
                                $currentStatus = $client->getCurrentMonthStatus();
                                $totalPaid = $client->total_paid;
                                $totalPackage = $client->total_package;
                                $progressPercent = $client->progress_percent;
                                $remainingBalance = $client->remaining_balance;
                                $nextUnpaid = $client->getNextUnpaidMonth();

                                $statusBadge = [
                                    'Paid'     => 'bg-emerald-500/15 text-emerald-400',
                                    'Unpaid'   => 'bg-red-500/15 text-red-400',
                                    'Upcoming' => 'bg-amber-500/15 text-amber-400',
                                    'N/A'      => 'bg-slate-500/15 text-slate-500',
                                ];

                                // Pay button visible: Fixed → remaining > 0, Retainer → always while active
                                $showPayButton = $client->status === 'active' && ($client->isRecurring() || $remainingBalance > 0);
                            @endphp
                            <tr class="border-b border-border-subtle/50 hover:bg-white/[0.04] transition cursor-pointer"
                                onclick="openClientModal({{ $client->id }})">
                                {{-- Company --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="text-white font-semibold">{{ $client->company_name }}</div>
                                        @if($client->isRecurring())
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-400 uppercase tracking-wider">Retainer</span>
                                        @else
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-500/15 text-blue-400 uppercase tracking-wider">Fixed</span>
                                        @endif
                                    </div>
                                    <div class="text-slate-500 text-xs mt-0.5">Since {{ $client->contract_start->format('M Y') }}</div>
                                </td>

                                {{-- PIC --}}
                                <td class="px-6 py-4">
                                    <div class="text-slate-300 text-sm">{{ $client->pic_name }}</div>
                                    <div class="text-slate-500 text-xs mt-0.5">{{ $client->pic_phone }}</div>
                                </td>

                                {{-- Monthly Payment --}}
                                <td class="px-6 py-4 text-white font-semibold">RM {{ number_format($client->monthly_payment, 0) }}</td>

                                {{-- Due Day --}}
                                <td class="px-6 py-4">
                                    <span class="text-slate-300">{{ $client->due_day }}<sup class="text-slate-500">{{ in_array($client->due_day, [1,21,31]) ? 'st' : (in_array($client->due_day, [2,22]) ? 'nd' : (in_array($client->due_day, [3,23]) ? 'rd' : 'th')) }}</sup></span>
                                </td>

                                {{-- Last Month Status --}}
                                <td class="px-6 py-4">
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $statusBadge[$lastStatus] ?? $statusBadge['N/A'] }}">
                                        {{ $lastStatus }}
                                    </span>
                                </td>

                                {{-- Current Month Status --}}
                                <td class="px-6 py-4">
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $statusBadge[$currentStatus] ?? $statusBadge['N/A'] }}">
                                        {{ $currentStatus }}
                                    </span>
                                </td>

                                {{-- Progress / Stats (Mode-aware) --}}
                                <td class="px-6 py-4">
                                    @if($client->isRecurring())
                                        {{-- Recurring: show lifetime paid + months active --}}
                                        <div class="min-w-[140px]">
                                            <div class="text-white font-semibold text-sm">RM {{ number_format($totalPaid, 0) }}</div>
                                            <div class="text-[10px] text-slate-500">{{ $client->months_active }} months active</div>
                                        </div>
                                    @else
                                        {{-- Fixed: show progress bar --}}
                                        <div class="min-w-[160px]">
                                            <div class="flex justify-between text-xs mb-1.5">
                                                <span class="text-slate-400">RM {{ number_format($totalPaid, 0) }}</span>
                                                <span class="text-slate-500">/ RM {{ number_format($totalPackage, 0) }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500
                                                    {{ $progressPercent >= 100 ? 'bg-emerald-500' : ($progressPercent >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}"
                                                    style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                            <div class="text-[10px] text-slate-600 mt-1">{{ $progressPercent }}% completed</div>
                                        </div>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($showPayButton)
                                            <button type="button"
                                                onclick="openPayModal({{ $client->id }}, '{{ addslashes($client->company_name) }}', {{ $client->monthly_payment }}, {{ $nextUnpaid['month'] }}, {{ $nextUnpaid['year'] }}, '{{ $nextUnpaid['label'] }}')"
                                                class="px-3 py-1.5 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-lg text-xs font-semibold transition">
                                                <i class="fas fa-check mr-1"></i> Pay
                                            </button>
                                        @endif
                                        <a href="{{ url('admin/clients/offline/' . $client->id . '/edit') }}"
                                            class="px-3 py-1.5 bg-slate-700/50 text-slate-300 hover:text-white hover:bg-slate-700 border border-border-subtle rounded-lg text-xs font-semibold transition">
                                            <i class="fas fa-pen text-[10px]"></i>
                                        </a>
                                        <a href="{{ url('admin/clients/offline/' . $client->id . '/delete') }}"
                                            onclick="return confirm('Are you sure you want to delete this client? All payment records will be lost.')"
                                            class="px-3 py-1.5 bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 rounded-lg text-xs font-semibold transition">
                                            <i class="fas fa-trash text-[10px]"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 rounded-2xl bg-slate-800/50 border border-border-subtle flex items-center justify-center mb-4">
                                            <i class="fas fa-handshake text-2xl text-slate-600"></i>
                                        </div>
                                        <p class="text-slate-500 text-sm mb-4">No offline clients yet.</p>
                                        <a href="{{ url('admin/clients/offline/create') }}"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-red hover:bg-brand-red-hover text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-red-900/30">
                                            <i class="fas fa-plus text-xs"></i> Add Your First Client
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($clients->hasPages())
            <div class="mt-6 flex justify-center">
                <div class="inline-flex items-center gap-1">
                    @if($clients->onFirstPage())
                        <span class="px-3 py-2 rounded-lg text-slate-600 text-sm cursor-not-allowed"><i class="fas fa-chevron-left text-xs"></i></span>
                    @else
                        <a href="{{ $clients->previousPageUrl() }}" class="px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-sm transition"><i class="fas fa-chevron-left text-xs"></i></a>
                    @endif

                    @foreach($clients->getUrlRange(max(1, $clients->currentPage()-2), min($clients->lastPage(), $clients->currentPage()+2)) as $page => $url)
                        <a href="{{ $url }}"
                           class="px-3.5 py-2 rounded-lg text-sm font-medium transition
                           {{ $page == $clients->currentPage() ? 'bg-brand-red text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            {{ $page }}
                        </a>
                    @endforeach

                    @if($clients->hasMorePages())
                        <a href="{{ $clients->nextPageUrl() }}" class="px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-sm transition"><i class="fas fa-chevron-right text-xs"></i></a>
                    @else
                        <span class="px-3 py-2 rounded-lg text-slate-600 text-sm cursor-not-allowed"><i class="fas fa-chevron-right text-xs"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- CLIENT DETAIL MODALS — Rendered OUTSIDE the table              --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @foreach($clients as $client)
        @php
            $lastStatus = $client->getLastMonthStatus();
            $currentStatus = $client->getCurrentMonthStatus();
            $totalPaid = $client->total_paid;
            $totalPackage = $client->total_package;
            $progressPercent = $client->progress_percent;
            $remainingBalance = $client->remaining_balance;

            $statusBadge = [
                'Paid'     => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                'Unpaid'   => 'bg-red-500/15 text-red-400 border-red-500/20',
                'Upcoming' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                'N/A'      => 'bg-slate-500/15 text-slate-500 border-slate-500/20',
            ];

            $contractStatusColors = [
                'active'    => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20',
                'completed' => 'bg-blue-500/15 text-blue-400 border border-blue-500/20',
                'cancelled' => 'bg-red-500/15 text-red-400 border border-red-500/20',
            ];

            $dueSuffix = in_array($client->due_day, [1,21,31]) ? 'st' : (in_array($client->due_day, [2,22]) ? 'nd' : (in_array($client->due_day, [3,23]) ? 'rd' : 'th'));
        @endphp

        {{-- Full-screen overlay --}}
        <div id="modal-{{ $client->id }}"
             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
             onclick="closeClientModal({{ $client->id }})">

            {{-- Modal Container --}}
            <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-white/10 shadow-2xl bg-slate-900"
                 onclick="event.stopPropagation()"
                 style="scrollbar-width: thin; scrollbar-color: rgba(100,116,139,0.3) transparent;">

                {{-- Modal Header --}}
                <div class="relative p-8 pb-6">
                    <button onclick="closeClientModal({{ $client->id }})"
                        class="absolute top-6 right-6 w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>

                    <div class="flex items-center gap-4 pr-14">
                        <div class="w-14 h-14 rounded-2xl {{ $client->isRecurring() ? 'bg-gradient-to-br from-emerald-500/20 to-teal-500/20' : 'bg-gradient-to-br from-blue-500/20 to-emerald-500/20' }} border border-white/10 flex items-center justify-center flex-shrink-0">
                            <i class="{{ $client->isRecurring() ? 'fas fa-sync-alt text-emerald-400' : 'fas fa-building text-blue-400' }} text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white tracking-tight">{{ $client->company_name }}</h2>
                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $contractStatusColors[$client->status] ?? 'bg-slate-500/15 text-slate-400' }} capitalize">{{ $client->status }}</span>
                                @if($client->isRecurring())
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-400 uppercase tracking-wider border border-emerald-500/20">Recurring Retainer</span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-500/15 text-blue-400 uppercase tracking-wider border border-blue-500/20">Fixed Contract</span>
                                @endif
                                <span class="text-xs text-slate-500">Since {{ $client->contract_start->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top Stats Grid (mode-aware) --}}
                <div class="px-8 pb-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @if($client->isRecurring())
                            {{-- Recurring: Monthly Fee, Total Paid, Months Active, Due Day --}}
                            <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Monthly Fee</div>
                                <div class="text-xl font-bold text-emerald-400">RM {{ number_format($client->monthly_payment, 0) }}</div>
                            </div>
                            <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Total Paid (Lifetime)</div>
                                <div class="text-xl font-bold text-white">RM {{ number_format($totalPaid, 0) }}</div>
                            </div>
                            <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Months Active</div>
                                <div class="text-xl font-bold text-white">{{ $client->months_active }}</div>
                                <div class="text-[10px] text-slate-600 mt-0.5">since start</div>
                            </div>
                            <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Due Day</div>
                                <div class="text-xl font-bold text-white">{{ $client->due_day }}<sup class="text-sm text-slate-500">{{ $dueSuffix }}</sup></div>
                                <div class="text-[10px] text-slate-600 mt-0.5">of every month</div>
                            </div>
                        @else
                            {{-- Fixed: Total Package, Monthly Payment, Start Date, Due Day --}}
                            <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Total Package</div>
                                <div class="text-xl font-bold text-white">RM {{ number_format($totalPackage, 0) }}</div>
                            </div>
                            <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Monthly Payment</div>
                                <div class="text-xl font-bold text-emerald-400">RM {{ number_format($client->monthly_payment, 0) }}</div>
                            </div>
                            <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Start Date</div>
                                <div class="text-base font-bold text-white">{{ $client->contract_start->format('d M Y') }}</div>
                            </div>
                            <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Due Day</div>
                                <div class="text-xl font-bold text-white">{{ $client->due_day }}<sup class="text-sm text-slate-500">{{ $dueSuffix }}</sup></div>
                                <div class="text-[10px] text-slate-600 mt-0.5">of every month</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Financial Section (mode-aware) --}}
                <div class="px-8 pb-6">
                    <div class="bg-slate-800/40 rounded-2xl p-6 border border-white/5">
                        @if($client->isRecurring())
                            {{-- Recurring: Next Billing Date + Payment Streak --}}
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                                    <i class="fas fa-calendar-alt mr-1.5 text-emerald-400"></i> Billing Overview
                                </h3>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-400">
                                    <i class="fas fa-sync-alt mr-1 text-[10px]"></i> Recurring
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-slate-900/50 rounded-xl p-5 border border-white/5 text-center">
                                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Next Billing Date</div>
                                    <div class="text-2xl font-bold text-white">{{ $client->next_billing_date->format('d M Y') }}</div>
                                    <div class="text-xs text-slate-500 mt-1.5">
                                        @if($client->next_billing_date->isToday())
                                            <span class="text-amber-400 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>Due Today</span>
                                        @elseif($client->next_billing_date->isPast())
                                            <span class="text-red-400 font-medium"><i class="fas fa-exclamation-triangle mr-1"></i>Overdue</span>
                                        @else
                                            in {{ now()->diffInDays($client->next_billing_date) }} days
                                        @endif
                                    </div>
                                </div>
                                <div class="bg-slate-900/50 rounded-xl p-5 border border-white/5 text-center">
                                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Lifetime Payments</div>
                                    <div class="text-2xl font-bold text-emerald-400">RM {{ number_format($totalPaid, 0) }}</div>
                                    <div class="text-xs text-slate-500 mt-1.5">{{ $client->payments->count() }} payments recorded</div>
                                </div>
                            </div>
                        @else
                            {{-- Fixed: Progress Bar + Remaining Balance --}}
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                                    <i class="fas fa-chart-line mr-1.5 text-blue-400"></i> Financial Progress
                                </h3>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg
                                    {{ $progressPercent >= 100 ? 'bg-emerald-500/15 text-emerald-400' : ($progressPercent >= 50 ? 'bg-blue-500/15 text-blue-400' : 'bg-amber-500/15 text-amber-400') }}">
                                    {{ $progressPercent }}%
                                </span>
                            </div>

                            <div class="flex items-end justify-between mb-4">
                                <div>
                                    <div class="text-3xl font-bold text-white">RM {{ number_format($totalPaid, 0) }}</div>
                                    <div class="text-xs text-slate-500 mt-1">of RM {{ number_format($totalPackage, 0) }} total</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold {{ $remainingBalance <= 0 ? 'text-emerald-400' : 'text-amber-400' }}">RM {{ number_format(max(0, $remainingBalance), 0) }}</div>
                                    <div class="text-xs text-slate-500 mt-1">remaining balance</div>
                                </div>
                            </div>

                            <div class="w-full bg-slate-700/50 rounded-full h-3.5 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 relative overflow-hidden
                                    {{ $progressPercent >= 100 ? 'bg-gradient-to-r from-emerald-600 to-emerald-400' : ($progressPercent >= 50 ? 'bg-gradient-to-r from-blue-600 to-blue-400' : 'bg-gradient-to-r from-amber-600 to-amber-400') }}"
                                    style="width: {{ min($progressPercent, 100) }}%">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 animate-pulse"></div>
                                </div>
                            </div>

                            <div class="flex justify-between mt-2.5">
                                <span class="text-xs text-slate-500">{{ $progressPercent }}% completed</span>
                                @if($client->monthly_payment > 0 && $remainingBalance > 0)
                                    <span class="text-xs text-slate-500">~{{ ceil($remainingBalance / $client->monthly_payment) }} months remaining</span>
                                @elseif($remainingBalance <= 0)
                                    <span class="text-xs text-emerald-400 font-medium"><i class="fas fa-check-circle mr-1"></i>Fully Paid</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Two-column: PIC Contact + Payment Status --}}
                <div class="px-8 pb-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- PIC Contact --}}
                        <div class="bg-slate-800/40 rounded-2xl p-6 border border-white/5">
                            <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-4">
                                <i class="fas fa-user-tie mr-1.5 text-blue-400"></i> Person In Charge
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user text-blue-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Name</div>
                                        <div class="text-sm text-white font-medium">{{ $client->pic_name }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-phone text-emerald-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Phone</div>
                                        <div class="text-sm text-white font-medium">{{ $client->pic_phone }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-envelope text-purple-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Email</div>
                                        <div class="text-sm text-white font-medium">{{ $client->pic_email ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Status --}}
                        <div class="bg-slate-800/40 rounded-2xl p-6 border border-white/5">
                            <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-4">
                                <i class="fas fa-signal mr-1.5 text-blue-400"></i> Payment Status
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between bg-slate-900/50 rounded-xl px-4 py-3.5 border border-white/5">
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase mb-0.5">Last Month</div>
                                        <div class="text-xs text-slate-400">{{ now()->subMonth()->format('F Y') }}</div>
                                    </div>
                                    <span class="text-xs font-bold px-3 py-1.5 rounded-lg border {{ $statusBadge[$lastStatus] ?? $statusBadge['N/A'] }}">{{ $lastStatus }}</span>
                                </div>
                                <div class="flex items-center justify-between bg-slate-900/50 rounded-xl px-4 py-3.5 border border-white/5">
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase mb-0.5">Current Month</div>
                                        <div class="text-xs text-slate-400">{{ now()->format('F Y') }}</div>
                                    </div>
                                    <span class="text-xs font-bold px-3 py-1.5 rounded-lg border {{ $statusBadge[$currentStatus] ?? $statusBadge['N/A'] }}">{{ $currentStatus }}</span>
                                </div>
                                @php
                                    $nextMonth = now()->addMonth();
                                    $nextMonthStatus = $client->getPaymentStatus($nextMonth->month, $nextMonth->year);
                                @endphp
                                <div class="flex items-center justify-between bg-slate-900/50 rounded-xl px-4 py-3.5 border border-white/5">
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase mb-0.5">Next Month</div>
                                        <div class="text-xs text-slate-400">{{ $nextMonth->format('F Y') }}</div>
                                    </div>
                                    <span class="text-xs font-bold px-3 py-1.5 rounded-lg border {{ $statusBadge[$nextMonthStatus] ?? $statusBadge['N/A'] }}">{{ $nextMonthStatus }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment History --}}
                <div class="px-8 pb-6">
                    <div class="bg-slate-800/40 rounded-2xl p-6 border border-white/5">
                        <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-4">
                            <i class="fas fa-history mr-1.5 text-blue-400"></i> Payment History
                        </h3>
                        @if($client->payments->count() > 0)
                            <div class="space-y-2.5">
                                @foreach($client->payments->sortByDesc(function($p) { return $p->period_year * 100 + $p->period_month; }) as $payment)
                                    <div class="flex items-center justify-between bg-slate-900/50 rounded-xl px-4 py-3 border border-white/5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-check text-emerald-400 text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm text-white font-medium">
                                                    {{ \Carbon\Carbon::create($payment->period_year, $payment->period_month)->format('F Y') }}
                                                </div>
                                                <div class="text-[10px] text-slate-500">
                                                    Paid on {{ $payment->paid_at->format('d M Y, h:i A') }}
                                                    @if($payment->invoice_number)
                                                        <span class="text-slate-600">•</span> {{ $payment->invoice_number }}
                                                    @endif
                                                    @if($payment->notes)
                                                        <span class="text-slate-600">•</span> {{ $payment->notes }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-sm font-bold text-emerald-400">RM {{ number_format($payment->amount, 2) }}</div>
                                            <a href="{{ url('admin/clients/offline/invoice/' . $payment->id) }}"
                                               onclick="event.stopPropagation()"
                                               target="_blank"
                                               class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 hover:bg-blue-500/20 transition"
                                               title="Download Invoice">
                                                <i class="fas fa-file-pdf text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-slate-900/50 rounded-xl p-8 border border-white/5 text-center">
                                <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-inbox text-slate-600 text-lg"></i>
                                </div>
                                <p class="text-slate-500 text-sm">No payments recorded yet.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Notes --}}
                @if($client->notes)
                <div class="px-8 pb-6">
                    <div class="bg-slate-800/40 rounded-2xl p-6 border border-white/5">
                        <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-3">
                            <i class="fas fa-sticky-note mr-1.5 text-blue-400"></i> Notes
                        </h3>
                        <p class="text-sm text-slate-300 leading-relaxed">{{ $client->notes }}</p>
                    </div>
                </div>
                @endif

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between px-8 py-6 border-t border-white/5 bg-slate-900/50">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->pic_phone) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-emerald-900/30">
                        <i class="fab fa-whatsapp text-base"></i> WhatsApp PIC
                    </a>
                    <div class="flex items-center gap-3">
                        <a href="{{ url('admin/clients/offline/' . $client->id . '/edit') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold text-sm rounded-xl transition-all duration-200 border border-white/5">
                            <i class="fas fa-pen text-xs"></i> Edit Client
                        </a>
                        <button onclick="closeClientModal({{ $client->id }})"
                            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-sm rounded-xl border border-white/10 transition-all duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- ═══ PAYMENT MODAL ═══ --}}
    <div id="pay-modal"
         class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         onclick="closePayModal()">
        <div class="w-full max-w-md rounded-2xl border border-white/10 shadow-2xl bg-slate-900 p-8"
             onclick="event.stopPropagation()">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-white">Record Payment</h3>
                    <p class="text-sm text-slate-500 mt-0.5" id="pay-modal-client"></p>
                </div>
                <button onclick="closePayModal()"
                    class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white transition">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <form id="pay-form" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="month" id="pay-month">
                <input type="hidden" name="year" id="pay-year">

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Applying Payment For</label>
                    <div class="flex items-center gap-3">
                        <div class="text-sm text-white font-medium bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 flex-1" id="pay-period-label"></div>
                        <div id="pay-advance-badge" class="hidden">
                            <span class="text-[9px] font-bold px-2 py-1 rounded-lg bg-blue-500/15 text-blue-400 border border-blue-500/20 uppercase tracking-wider whitespace-nowrap">
                                <i class="fas fa-forward mr-1"></i> Advance
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">
                        Amount Received (RM) <span class="text-brand-red">*</span>
                    </label>
                    <input type="number" name="amount" id="pay-amount" required min="0.01" step="0.01"
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-emerald-500/50 focus:outline-none transition"
                        placeholder="Enter actual amount">
                    <p class="text-[10px] text-slate-600 mt-1.5" id="pay-suggested"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">
                        Notes <span class="text-slate-600">(optional)</span>
                    </label>
                    <input type="text" name="payment_note" maxlength="500"
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-slate-500 focus:outline-none transition"
                        placeholder="e.g. Partial payment, bank transfer ref...">
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" onclick="closePayModal()"
                        class="flex-1 px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-sm rounded-xl border border-white/10 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-emerald-900/30">
                        <i class="fas fa-check mr-2"></i> Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function openClientModal(id) {
        const modal = document.getElementById('modal-' + id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.animation = 'fadeIn 0.2s ease-out';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeClientModal(id) {
        const modal = document.getElementById('modal-' + id);
        if (modal) {
            modal.style.animation = 'fadeOut 0.15s ease-in';
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.animation = '';
                document.body.style.overflow = '';
            }, 140);
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="modal-"]').forEach(function(modal) {
                if (!modal.classList.contains('hidden')) {
                    var id = modal.id.replace('modal-', '');
                    closeClientModal(id);
                }
            });
            closePayModal();
        }
    });

    // ─── Payment Modal ───
    function openPayModal(clientId, companyName, suggestedAmount, targetMonth, targetYear, periodLabel) {
        var modal = document.getElementById('pay-modal');
        var form = document.getElementById('pay-form');
        var amountInput = document.getElementById('pay-amount');
        var clientLabel = document.getElementById('pay-modal-client');
        var suggestedLabel = document.getElementById('pay-suggested');
        var periodDisplay = document.getElementById('pay-period-label');
        var monthInput = document.getElementById('pay-month');
        var yearInput = document.getElementById('pay-year');
        var advanceBadge = document.getElementById('pay-advance-badge');

        form.action = '{{ url("admin/clients/offline") }}/' + clientId + '/pay';
        clientLabel.textContent = companyName;
        amountInput.value = suggestedAmount;
        suggestedLabel.textContent = 'Suggested: RM ' + parseFloat(suggestedAmount).toLocaleString();

        // Set target period
        periodDisplay.textContent = periodLabel;
        monthInput.value = targetMonth;
        yearInput.value = targetYear;

        // Show advance badge if not current month
        var now = new Date();
        var isAdvance = (targetYear > now.getFullYear()) || (targetYear === now.getFullYear() && targetMonth > (now.getMonth() + 1));
        advanceBadge.classList.toggle('hidden', !isAdvance);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.animation = 'fadeIn 0.2s ease-out';
        amountInput.focus();
        amountInput.select();
    }

    function closePayModal() {
        var modal = document.getElementById('pay-modal');
        if (modal && !modal.classList.contains('hidden')) {
            modal.style.animation = 'fadeOut 0.15s ease-in';
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.animation = '';
            }, 140);
        }
    }
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    [id^="modal-"] > div::-webkit-scrollbar {
        width: 6px;
    }
    [id^="modal-"] > div::-webkit-scrollbar-track {
        background: transparent;
    }
    [id^="modal-"] > div::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.3);
        border-radius: 3px;
    }
    [id^="modal-"] > div::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.5);
    }
</style>
@endsection