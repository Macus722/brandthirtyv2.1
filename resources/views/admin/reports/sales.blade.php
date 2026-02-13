@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold">Sales Report</h1>
                <p class="text-gray-500 text-sm mt-1">Historical financial data and transaction analysis.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ url('admin/reports/sales/download-pdf') }}?{{ http_build_query(request()->all()) }}" 
                   class="bg-brand-red hover:bg-red-600 text-white text-sm font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition shadow-lg shadow-red-900/20">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a href="{{ url('admin/reports/sales/export') }}?{{ http_build_query(request()->all()) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition shadow-lg shadow-green-900/20">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>

        <!-- Filters ... (Same as before) -->
        <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-xl mb-8">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Start Date</label>
                    <input type="date" name="date_start" value="{{ request('date_start') }}"
                        class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">End Date</label>
                    <input type="date" name="date_end" value="{{ request('date_end') }}"
                        class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Service Type</label>
                    <select name="plan"
                        class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none">
                        <option value="All">All Plans</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan }}" {{ request('plan') == $plan ? 'selected' : '' }}>{{ $plan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-brand-red hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl transition shadow-lg shadow-red-900/50">
                        Filter Report
                    </button>
                    @if(request()->anyFilled(['date_start', 'date_end', 'plan']))
                        <a href="{{ url('admin/reports/sales') }}" 
                           class="bg-white/10 hover:bg-white/20 text-gray-400 hover:text-white px-4 py-3 rounded-xl transition flex items-center justify-center">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Summary Cards & Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Cards Column (Left) -->
            <div class="space-y-6">
                <!-- Total Revenue -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg relative overflow-hidden">
                    <div class="flex justify-between items-center z-10 relative">
                        <div>
                            <div class="text-xs text-brand-red uppercase font-bold tracking-wider mb-1">Total Revenue</div>
                            <div class="text-3xl font-bold text-white">RM {{ number_format((float)$totalRevenue, 2) }}</div>
                            <div class="flex items-center gap-2 mt-2">
                                @if($growthPercentage >= 0)
                                    <span class="text-xs font-bold text-green-500 bg-green-500/10 px-2 py-0.5 rounded-full border border-green-500/20">
                                        <i class="fas fa-arrow-up"></i> {{ number_format((float)$growthPercentage, 1) }}%
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-red-500 bg-red-500/10 px-2 py-0.5 rounded-full border border-red-500/20">
                                        <i class="fas fa-arrow-down"></i> {{ number_format((float)abs($growthPercentage), 1) }}%
                                    </span>
                                @endif
                                <span class="text-[10px] text-gray-500">vs last month</span>
                            </div>
                        </div>
                        <div class="w-10 h-10 bg-brand-red/10 rounded-full flex items-center justify-center text-brand-red text-lg border border-brand-red/20">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>

                <!-- AOV -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg flex justify-between items-center">
                    <div>
                        <div class="text-xs text-purple-400 uppercase font-bold tracking-wider mb-1">Avg. Order Value</div>
                        <div class="text-3xl font-bold text-white">RM {{ number_format((float)$aov, 2) }}</div>
                        <div class="text-[10px] text-gray-500 mt-1">Per approved order</div>
                    </div>
                    <div class="w-10 h-10 bg-purple-500/10 rounded-full flex items-center justify-center text-purple-500 text-lg border border-purple-500/20">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>

                <!-- Potential Sales -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg flex justify-between items-center opacity-70 hover:opacity-100 transition">
                    <div>
                        <div class="text-xs text-blue-400 uppercase font-bold tracking-wider mb-1">Potential Sales</div>
                        <div class="text-2xl font-bold text-white">RM {{ number_format((float)$potentialRevenue, 2) }}</div>
                        <div class="text-[10px] text-gray-500 mt-1">All visible orders</div>
                    </div>
                    <div class="w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center text-blue-500 text-lg border border-blue-500/20">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <!-- Revenue Trend Chart (Center - Wider) -->
            <div class="lg:col-span-2 bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg">
                <h3 class="text-sm font-bold text-gray-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-area text-brand-red"></i> Daily Revenue Trend
                </h3>
                <div class="relative h-[300px] w-full">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Secondary Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Service Distribution Pie -->
            <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg">
                 <h3 class="text-sm font-bold text-gray-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-brand-red"></i> Revenue by Plan
                </h3>
                <div class="relative h-[250px] w-full flex items-center justify-center">
                    <canvas id="planDistributionChart"></canvas>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg">
                 <h3 class="text-sm font-bold text-gray-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-crown text-yellow-500"></i> Top 5 Customers
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-gray-500 uppercase border-b border-white/5">
                            <tr>
                                <th class="pb-2">Customer</th>
                                <th class="pb-2 text-right">Spent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($topCustomers as $customer)
                                <tr>
                                    <td class="py-3 font-medium text-white">{{ $customer['name'] }}</td>
                                    <td class="py-3 text-right font-mono text-brand-red">RM {{ number_format($customer['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="py-4 text-center text-gray-500">No data available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Staff Leaderboard -->
            <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg">
                 <h3 class="text-sm font-bold text-gray-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-tie text-purple-400"></i> Top Performing Staff
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-gray-500 uppercase border-b border-white/5">
                            <tr>
                                <th class="pb-2">Staff</th>
                                <th class="pb-2 text-right">Sales</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($staffPerformance->take(5) as $staff)
                                <tr>
                                    <td class="py-3 font-medium text-white flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full bg-purple-500/20 text-[10px] flex items-center justify-center text-purple-400 font-bold border border-purple-500/10">
                                            {{ substr($staff->name, 0, 1) }}
                                        </div>
                                        {{ $staff->name }}
                                    </td>
                                    <td class="py-3 text-right font-mono text-green-400">RM {{ number_format($staff->orders_sum_total_amount ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="py-4 text-center text-gray-500">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-brand-dark border border-white/10 rounded-2xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black/20 text-gray-500 text-xs uppercase border-b border-white/5">
                            <th class="p-6 font-bold">Date</th>
                            <th class="p-6 font-bold">Order ID</th>
                            <th class="p-6 font-bold">Customer</th>
                            <th class="p-6 font-bold">Service Type</th>
                            <th class="p-6 font-bold text-right">Amount (RM)</th>
                            <th class="p-6 font-bold text-center">Payment Status</th>
                            <th class="p-6 font-bold">Assigned Staff</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-white/5">
                        @forelse($orders as $order)
                            <tr class="hover:bg-white/5 transition">
                                <td class="p-6 text-gray-400 font-mono">{{ $order->created_at->format('Y-m-d') }}</td>
                                <td class="p-6 font-bold text-white">#{{ $order->order_id }}</td>
                                <td class="p-6">
                                    <div class="text-white">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->company_name ?? 'N/A' }}</div>
                                </td>
                                <td class="p-6 text-gray-300">{{ $order->plan }}</td>
                                <td class="p-6 text-right font-mono text-white">
                                    {{ number_format((float)$order->total_amount, 2) }}
                                </td>
                                <td class="p-6 text-center">
                                    @if(in_array($order->status, ['Paid', 'Completed']))
                                        <span class="inline-block px-3 py-1 bg-green-500/10 text-green-500 rounded-full text-xs font-bold border border-green-500/20">Paid</span>
                                    @elseif(in_array($order->status, ['Processing', 'In Progress', 'Approved']))
                                        <span class="inline-block px-3 py-1 bg-green-500/10 text-green-500 rounded-full text-xs font-bold border border-green-500/20">Secured</span>
                                    @elseif($order->status == 'Rejected')
                                        <span class="inline-block px-3 py-1 bg-red-500/10 text-red-500 rounded-full text-xs font-bold border border-red-500/20">Failed</span>
                                    @else
                                        <span class="inline-block px-3 py-1 bg-yellow-500/10 text-yellow-500 rounded-full text-xs font-bold border border-yellow-500/20">Pending</span>
                                    @endif
                                </td>
                                <td class="p-6">
                                    @if($order->staff_id && $order->staff)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-purple-500/20 flex items-center justify-center text-xs text-purple-400 font-bold">
                                                {{ substr($order->staff->name, 0, 1) }}
                                            </div>
                                            <span class="text-gray-300">{{ $order->staff->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-600 italic">Unassigned</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500">No transactions found for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!-- Summary Footer Row -->
                    <tfoot class="bg-white/5 border-t border-white/10 font-bold text-white">
                        <tr>
                            <td colspan="4" class="p-6 text-right uppercase text-xs tracking-wider text-gray-400">Total Visible</td>
                            <td class="p-6 text-right font-mono text-xl text-brand-red">
                                RM {{ number_format((float)$potentialRevenue, 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
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
                plugins: {
                    legend: { labels: { color: '#9CA3AF', font: { family: 'Inter' } } }
                },
                scales: {
                    y: { grid: { color: '#333' }, ticks: { color: '#6B7280' } },
                    x: { grid: { color: '#333' }, ticks: { color: '#6B7280' } }
                }
            };

            // Revenue Trend Chart
            const trendCtx = document.getElementById('revenueTrendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($trendLabels) !!},
                    datasets: [{
                        label: 'Revenue (RM)',
                        data: {!! json_encode($trendValues) !!},
                        borderColor: '#FF2D46',
                        backgroundColor: 'rgba(255, 45, 70, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: '#FF2D46',
                        pointRadius: 4
                    }]
                },
                options: commonOptions
            });

            // Plan Distribution Chart
            const planCtx = document.getElementById('planDistributionChart').getContext('2d');
            new Chart(planCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($planLabels) !!},
                    datasets: [{
                        data: {!! json_encode($planValues) !!},
                        backgroundColor: ['#FF2D46', '#25D366', '#3B82F6', '#F59E0B', '#8B5CF6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { color: '#9CA3AF', padding: 20 } }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>
@endsection
