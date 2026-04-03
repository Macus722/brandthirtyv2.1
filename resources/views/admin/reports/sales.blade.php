@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Sales Report</h1>
                <p class="text-slate-500 text-sm mt-1">Historical financial data and transaction analysis.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ url('admin/reports/sales/download-pdf') }}?{{ http_build_query(request()->all()) }}"
                   class="bg-brand-red hover:bg-brand-red-hover text-white text-sm font-semibold py-2.5 px-5 rounded-xl flex items-center gap-2 transition-all duration-200 shadow-lg shadow-red-900/20">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a href="{{ url('admin/reports/sales/export') }}?{{ http_build_query(request()->all()) }}"
                   class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 px-5 rounded-xl flex items-center gap-2 transition-all duration-200 shadow-lg shadow-emerald-900/20">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="exec-card p-7 mb-10">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div>
                    <label class="block text-slate-400 text-xs uppercase font-semibold mb-2 tracking-wider">Start Date</label>
                    <input type="date" name="date_start" value="{{ request('date_start') }}"
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs uppercase font-semibold mb-2 tracking-wider">End Date</label>
                    <input type="date" name="date_end" value="{{ request('date_end') }}"
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs uppercase font-semibold mb-2 tracking-wider">Service Type</label>
                    <select name="plan"
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-3 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        <option value="All">All Plans</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan }}" {{ request('plan') == $plan ? 'selected' : '' }}>{{ $plan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-brand-red hover:bg-brand-red-hover text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-red-900/20 text-sm">
                        Filter
                    </button>
                    @if(request()->anyFilled(['date_start', 'date_end', 'plan']))
                        <a href="{{ url('admin/reports/sales') }}"
                           class="bg-slate-700/50 hover:bg-slate-700 text-slate-400 hover:text-white px-4 py-3 rounded-xl transition flex items-center justify-center">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Summary Cards & Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div class="space-y-6">
                <div class="exec-card p-7">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider mb-2">Total Revenue</div>
                            <div class="text-3xl font-bold text-white">RM {{ number_format((float)$totalRevenue, 2) }}</div>
                            <div class="flex items-center gap-2 mt-3">
                                @if($growthPercentage >= 0)
                                    <span class="text-xs font-semibold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full">
                                        <i class="fas fa-arrow-up"></i> {{ number_format((float)$growthPercentage, 1) }}%
                                    </span>
                                @else
                                    <span class="text-xs font-semibold text-red-400 bg-red-500/10 px-2.5 py-1 rounded-full">
                                        <i class="fas fa-arrow-down"></i> {{ number_format((float)abs($growthPercentage), 1) }}%
                                    </span>
                                @endif
                                <span class="text-[11px] text-slate-500">vs last month</span>
                            </div>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-brand-red/10 flex items-center justify-center text-brand-red">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>

                <div class="exec-card p-7 flex justify-between items-center">
                    <div>
                        <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider mb-2">Avg. Order Value</div>
                        <div class="text-3xl font-bold text-white">RM {{ number_format((float)$aov, 2) }}</div>
                        <div class="text-[11px] text-slate-500 mt-1">Per approved order</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>

                <div class="exec-card p-7 flex justify-between items-center">
                    <div>
                        <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider mb-2">Potential Sales</div>
                        <div class="text-2xl font-bold text-white">RM {{ number_format((float)$potentialRevenue, 2) }}</div>
                        <div class="text-[11px] text-slate-500 mt-1">All visible orders</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 exec-card p-7">
                <h3 class="text-sm font-semibold text-slate-300 mb-5 flex items-center gap-2">
                    <i class="fas fa-chart-area text-brand-red text-xs"></i> Daily Revenue Trend
                </h3>
                <div class="relative h-[300px] w-full">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Secondary Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div class="exec-card p-7">
                <h3 class="text-sm font-semibold text-slate-300 mb-5 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-brand-red text-xs"></i> Revenue by Plan
                </h3>
                <div class="relative h-[250px] w-full flex items-center justify-center">
                    <canvas id="planDistributionChart"></canvas>
                </div>
            </div>

            <div class="exec-card p-7">
                <h3 class="text-sm font-semibold text-slate-300 mb-5 flex items-center gap-2">
                    <i class="fas fa-crown text-amber-400 text-xs"></i> Top 5 Customers
                </h3>
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-slate-500 uppercase border-b border-border-subtle">
                        <tr>
                            <th class="pb-3 font-semibold tracking-wider">Customer</th>
                            <th class="pb-3 text-right font-semibold tracking-wider">Spent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($topCustomers as $customer)
                            <tr>
                                <td class="py-4 font-medium text-white">{{ $customer['name'] }}</td>
                                <td class="py-4 text-right text-brand-red font-semibold">RM {{ number_format($customer['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-6 text-center text-slate-500 text-sm">No data available</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="exec-card p-7">
                <h3 class="text-sm font-semibold text-slate-300 mb-5 flex items-center gap-2">
                    <i class="fas fa-user-tie text-purple-400 text-xs"></i> Top Performing Staff
                </h3>
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-slate-500 uppercase border-b border-border-subtle">
                        <tr>
                            <th class="pb-3 font-semibold tracking-wider">Staff</th>
                            <th class="pb-3 text-right font-semibold tracking-wider">Sales</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($staffPerformance->take(5) as $staff)
                            <tr>
                                <td class="py-4 font-medium text-white flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-full bg-purple-500/15 text-[10px] flex items-center justify-center text-purple-400 font-semibold flex-shrink-0">
                                        {{ substr($staff->name, 0, 1) }}
                                    </div>
                                    {{ $staff->name }}
                                </td>
                                <td class="py-4 text-right text-emerald-400 font-semibold">RM {{ number_format($staff->orders_sum_total_amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-6 text-center text-slate-500 text-sm">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="exec-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-border-subtle bg-slate-800/30">
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Service</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Amount (RM)</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Payment</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Staff</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($orders as $order)
                        <tr class="border-b border-border-subtle hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-7 py-5 text-slate-400">{{ $order->created_at->format('Y-m-d') }}</td>
                            <td class="px-7 py-5 font-semibold text-white">#{{ $order->order_id }}</td>
                            <td class="px-7 py-5">
                                <div class="text-white font-medium">{{ $order->customer_name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $order->company_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-7 py-5 text-slate-300">{{ $order->plan }}</td>
                            <td class="px-7 py-5 text-right text-white font-medium">
                                RM {{ number_format((float)$order->total_amount, 2) }}
                            </td>
                            <td class="px-7 py-5 text-center">
                                @if(in_array($order->status, ['Paid', 'Completed']))
                                    <span class="inline-flex px-3 py-1 bg-emerald-500/15 text-emerald-400 rounded-full text-xs font-semibold">Paid</span>
                                @elseif(in_array($order->status, ['Processing', 'In Progress', 'Approved']))
                                    <span class="inline-flex px-3 py-1 bg-emerald-500/15 text-emerald-400 rounded-full text-xs font-semibold">Secured</span>
                                @elseif($order->status == 'Rejected')
                                    <span class="inline-flex px-3 py-1 bg-red-500/15 text-red-400 rounded-full text-xs font-semibold">Failed</span>
                                @else
                                    <span class="inline-flex px-3 py-1 bg-amber-500/15 text-amber-400 rounded-full text-xs font-semibold">Pending</span>
                                @endif
                            </td>
                            <td class="px-7 py-5">
                                @if($order->staff_id && $order->staff_name)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-purple-500/15 flex items-center justify-center text-xs text-purple-400 font-semibold flex-shrink-0">
                                            {{ substr($order->staff_name, 0, 1) }}
                                        </div>
                                        <span class="text-slate-300">{{ $order->staff_name }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-600 italic">Unassigned</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-7 py-16 text-center text-slate-500 text-sm">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-800/20 border-t border-border-subtle">
                    <tr>
                        <td colspan="4" class="px-7 py-5 text-right uppercase text-xs tracking-wider text-slate-500 font-semibold">Total Visible</td>
                        <td class="px-7 py-5 text-right text-xl font-bold text-brand-red">
                            RM {{ number_format((float)$potentialRevenue, 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 12 } } } },
                scales: {
                    y: { grid: { color: 'rgba(148,163,184,0.08)' }, ticks: { color: '#64748b' } },
                    x: { grid: { color: 'rgba(148,163,184,0.08)' }, ticks: { color: '#64748b' } }
                }
            };

            new Chart(document.getElementById('revenueTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($trendLabels) !!},
                    datasets: [{
                        label: 'Revenue (RM)',
                        data: {!! json_encode($trendValues) !!},
                        borderColor: '#E31E24',
                        backgroundColor: 'rgba(227, 30, 36, 0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#E31E24',
                        pointRadius: 3,
                    }]
                },
                options: commonOptions
            });

            new Chart(document.getElementById('planDistributionChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($planLabels) !!},
                    datasets: [{
                        data: {!! json_encode($planValues) !!},
                        backgroundColor: ['#E31E24', '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'],
                        borderWidth: 0,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { color: '#94a3b8', padding: 16, font: { family: 'Inter' } } } },
                    cutout: '65%',
                }
            });
        });
    </script>
@endsection
