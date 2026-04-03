@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <a href="{{ url('admin/clients/offline') }}" class="text-slate-500 hover:text-white transition">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Financial Reports</h1>
                </div>
                <p class="text-slate-500 text-sm ml-7">Revenue analytics and aging reports for offline clients.</p>
            </div>

            <a href="{{ url('admin/clients/offline/export') }}"
                target="_blank"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-emerald-900/30 whitespace-nowrap">
                <i class="fas fa-file-excel text-xs"></i> Export Excel
            </a>
        </div>

        {{-- Revenue Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            <div class="exec-card p-6 text-center">
                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Total Collected</div>
                <div class="text-2xl font-bold text-emerald-400">RM {{ number_format($totalCollected, 0) }}</div>
                <div class="text-xs text-slate-600 mt-1">All time</div>
            </div>
            <div class="exec-card p-6 text-center">
                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">This Month</div>
                <div class="text-2xl font-bold text-white">RM {{ number_format($thisMonthCollected, 0) }}</div>
                <div class="text-xs text-slate-600 mt-1">{{ now()->format('F Y') }}</div>
            </div>
            <div class="exec-card p-6 text-center">
                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Active Clients</div>
                <div class="text-2xl font-bold text-blue-400">{{ $activeClients }}</div>
                <div class="text-xs text-slate-600 mt-1">Currently active</div>
            </div>
            <div class="exec-card p-6 text-center">
                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Expected Monthly</div>
                <div class="text-2xl font-bold text-amber-400">RM {{ number_format($expectedMonthly, 0) }}</div>
                <div class="text-xs text-slate-600 mt-1">If all pay on time</div>
            </div>
        </div>

        {{-- Revenue Chart --}}
        <div class="exec-card p-8 mb-10">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-400"></i> Monthly Collections vs Expected
                </h3>
                <span class="text-xs text-slate-500">Last 12 months</span>
            </div>
            <div id="revenue-chart" style="height: 350px;"></div>
        </div>

        {{-- Aging Report --}}
        <div class="exec-card overflow-hidden">
            <div class="px-8 py-6 border-b border-border-subtle flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-red-400"></i> Aging Report — Overdue Clients
                </h3>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-500/15 text-red-400">
                    {{ count($overdueClients) }} client{{ count($overdueClients) !== 1 ? 's' : '' }} overdue
                </span>
            </div>

            @if(count($overdueClients) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border-subtle text-left">
                                <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Client</th>
                                <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Billing Mode</th>
                                <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Unpaid Months</th>
                                <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold text-right">Total Overdue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueClients as $item)
                                <tr class="border-b border-border-subtle/50 hover:bg-white/[0.03] transition">
                                    <td class="px-6 py-4">
                                        <div class="text-white font-semibold">{{ $item['client']->company_name }}</div>
                                        <div class="text-slate-500 text-xs mt-0.5">{{ $item['client']->pic_name }} • {{ $item['client']->pic_phone }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item['client']->isRecurring())
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-400 uppercase tracking-wider">Retainer</span>
                                        @else
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-500/15 text-blue-400 uppercase tracking-wider">Fixed</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($item['unpaid_months'] as $um)
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-red-500/15 text-red-400 border border-red-500/20">
                                                    {{ $um['period'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-lg font-bold text-red-400">RM {{ number_format($item['total_overdue'], 0) }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-2xl text-emerald-400"></i>
                    </div>
                    <p class="text-emerald-400 font-semibold mb-1">All Clear!</p>
                    <p class="text-slate-500 text-sm">No overdue payments at this time.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
{{-- ApexCharts CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var options = {
        chart: {
            type: 'bar',
            height: 350,
            background: 'transparent',
            toolbar: { show: false },
            fontFamily: 'Inter, system-ui, sans-serif',
        },
        series: [{
            name: 'Collected',
            data: @json($chartCollected)
        }, {
            name: 'Expected',
            data: @json($chartExpected)
        }],
        xaxis: {
            categories: @json($chartLabels),
            labels: {
                style: { colors: '#64748b', fontSize: '10px' }
            },
            axisBorder: { color: '#334155' },
            axisTicks: { color: '#334155' },
        },
        yaxis: {
            labels: {
                style: { colors: '#64748b', fontSize: '10px' },
                formatter: function(val) { return 'RM ' + val.toLocaleString(); }
            }
        },
        colors: ['#10b981', '#3b82f6'],
        fill: { opacity: [1, 0.25] },
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '55%',
            }
        },
        dataLabels: { enabled: false },
        grid: {
            borderColor: '#1e293b',
            strokeDashArray: 3,
        },
        legend: {
            labels: { colors: '#94a3b8' },
            fontSize: '11px',
        },
        tooltip: {
            theme: 'dark',
            y: { formatter: function(val) { return 'RM ' + val.toLocaleString(); } }
        },
        states: {
            hover: { filter: { type: 'lighten', value: 0.05 } }
        }
    };

    var chart = new ApexCharts(document.querySelector('#revenue-chart'), options);
    chart.render();
</script>
@endsection
