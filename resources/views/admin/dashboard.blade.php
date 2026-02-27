@extends('layouts.admin')

@push('scripts_head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Dashboard</h1>
                <p class="text-slate-500 text-sm mt-1">Real-time business performance overview.</p>
            </div>
            <div>
                <span class="bg-surface-raised backdrop-blur border border-border-subtle px-5 py-2.5 rounded-xl text-xs text-slate-400 font-medium">
                    {{ date('d M Y') }}
                </span>
            </div>
        </div>

        <style>
            @keyframes highlightFade {
                0% { background-color: rgba(227, 30, 36, 0.1); }
                100% { background-color: transparent; }
            }
            .flash-row { animation: highlightFade 3s ease-out; }
        </style>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            @if(!$isStaff)
                <div class="exec-card p-7">
                    <div class="flex items-center justify-between mb-5">
                        <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Today's Sales</div>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                            <i class="fas fa-calendar-day text-emerald-400 text-sm"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-white" id="today-sales-amount">RM {{ number_format($todaySales ?? 0, 2) }}</div>
                    <div class="text-xs text-emerald-400 mt-2 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Live
                    </div>
                </div>
            @endif

            <div class="exec-card p-7">
                <div class="flex items-center justify-between mb-5">
                    <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider">
                        {{ $isStaff ? 'My New Orders' : "Today's Orders" }}
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-blue-400 text-sm"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white" id="today-orders-count">{{ $todayOrders ?? 0 }}</div>
                <div class="text-xs text-slate-500 mt-2">New today</div>
            </div>

            @if(!$isStaff)
                <div class="exec-card p-7">
                    <div class="flex items-center justify-between mb-5">
                        <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Total Revenue</div>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                            <i class="fas fa-wallet text-emerald-400 text-sm"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-white" id="total-revenue-amount">RM {{ number_format($totalRevenue ?? 0, 2) }}</div>
                </div>
            @endif

            <div class="exec-card p-7">
                <div class="flex items-center justify-between mb-5">
                    <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider">
                        {{ $isStaff ? 'My Pending Work' : 'Pending Orders' }}
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center">
                        <i class="fas fa-clock text-amber-400 text-sm"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white" id="pending-orders-count">{{ $isStaff ? ($inProgressCount ?? 0) : ($pendingCount ?? 0) }}</div>
                @if($isStaff)
                    <div class="text-xs text-slate-500 mt-2">Assigned, Processing, In Progress, Review</div>
                @endif
            </div>

            @if($isStaff)
                <div class="exec-card p-7">
                    <div class="flex items-center justify-between mb-5">
                        <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider">My Completed</div>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                            <i class="fas fa-check-circle text-emerald-400 text-sm"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-white">{{ $completedCount ?? 0 }}</div>
                </div>
            @endif
        </div>

        @if(!$isStaff)
            <!-- Revenue Intelligence -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="exec-card p-7">
                    <div class="flex items-center justify-between mb-5">
                        <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Confirmed Revenue</div>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                            <i class="fas fa-check-double text-emerald-400 text-sm"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-white">RM {{ number_format($totalRevenue ?? 0, 2) }}</div>
                    <div class="text-xs text-emerald-400 mt-2">Completed orders only</div>
                </div>

                <div class="exec-card p-7">
                    <div class="flex items-center justify-between mb-5">
                        <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Potential Sales</div>
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center">
                            <i class="fas fa-chart-line text-blue-400 text-sm"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-white">RM {{ number_format($potentialSales ?? 0, 2) }}</div>
                    <div class="text-xs text-slate-500 mt-2">Excludes rejected &amp; cancelled</div>
                </div>

                <div class="exec-card p-7">
                    <div class="flex items-center justify-between mb-5">
                        <div class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Conversion Rate</div>
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center">
                            <i class="fas fa-percentage text-amber-400 text-sm"></i>
                        </div>
                    </div>
                    @php $convRate = ($totalOrders > 0) ? round(($completedOrdersCount / $totalOrders) * 100, 1) : 0; @endphp
                    <div class="text-2xl font-bold text-white">{{ $convRate }}%</div>
                    <div class="text-xs text-slate-500 mt-2">Orders to Completed</div>
                </div>
            </div>

            <!-- Charts + Funnel -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                <div class="lg:col-span-2 exec-card p-7">
                    <h3 class="text-sm font-semibold text-slate-300 mb-6">30-Day Sales Trend</h3>
                    <div class="relative h-[300px] w-full">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <div class="lg:col-span-1 exec-card p-7">
                    <h3 class="text-sm font-semibold text-slate-300 mb-5">Conversion Funnel</h3>
                    @php
                        $funnelTotal = $totalOrders > 0 ? $totalOrders : 1;
                        $funnelStages = [
                            ['label' => 'Total Orders', 'count' => $totalOrders, 'color' => 'bg-slate-500', 'pct' => 100],
                            ['label' => 'Approved', 'count' => $approvedOrders, 'color' => 'bg-blue-500', 'pct' => round(($approvedOrders / $funnelTotal) * 100)],
                            ['label' => 'Completed', 'count' => $completedOrdersCount, 'color' => 'bg-emerald-500', 'pct' => round(($completedOrdersCount / $funnelTotal) * 100)],
                        ];
                    @endphp
                    <div class="space-y-5">
                        @foreach($funnelStages as $stage)
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-xs text-slate-400 font-medium">{{ $stage['label'] }}</span>
                                    <span class="text-sm font-semibold text-white tabular-nums">{{ $stage['count'] }}</span>
                                </div>
                                <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full {{ $stage['color'] }} rounded-full transition-all duration-500"
                                        style="width: {{ min($stage['pct'], 100) }}%"></div>
                                </div>
                                <div class="text-right mt-0.5">
                                    <span class="text-[11px] text-slate-500">{{ $stage['pct'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 pt-4 border-t border-border-subtle text-center">
                        <span class="text-xs text-slate-500 font-medium">Rejection Rate</span>
                        <div class="text-sm font-semibold mt-1 {{ $rejectedCount > 0 ? 'text-red-400' : 'text-slate-500' }}">
                            {{ $totalOrders > 0 ? round(($rejectedCount / $totalOrders) * 100, 1) : 0 }}%
                            <span class="text-xs text-slate-600 font-normal">({{ $rejectedCount }})</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                <div class="lg:col-span-1 exec-card p-7">
                    <h3 class="text-sm font-semibold text-slate-300 mb-6">Plan Popularity</h3>
                    <div class="relative h-[300px] w-full flex items-center justify-center">
                        <canvas id="planChart"></canvas>
                    </div>
                </div>

                <div class="lg:col-span-2 exec-card p-7">
                    <h3 class="text-sm font-semibold text-slate-300 mb-1">Staff Workload</h3>
                    <p class="text-xs text-slate-500 mb-5">Active jobs per staff (Assigned, Processing, In Progress, Review)</p>
                    @if($staffWorkload->count() > 0)
                        @php $maxLoad = $staffWorkload->max('orders_count') ?: 1; @endphp
                        <div class="space-y-4">
                            @foreach($staffWorkload as $member)
                                <div class="flex items-center gap-4">
                                    <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center font-semibold text-white text-xs flex-shrink-0">
                                        {{ strtoupper(substr($member->name, 0, 2)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-center mb-1.5">
                                            <span class="text-sm font-medium text-white truncate">{{ $member->name }}</span>
                                            <span class="text-xs text-slate-400 font-medium tabular-nums ml-2 flex-shrink-0">{{ $member->orders_count }} active</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-slate-500 rounded-full transition-all duration-500"
                                                style="width: {{ $maxLoad > 0 ? round(($member->orders_count / $maxLoad) * 100) : 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 text-slate-500 text-sm">No staff members with active assignments yet.</div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Order Table -->
        <div class="exec-card p-7 relative">
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-7">
                <div class="flex bg-slate-800/60 p-1 rounded-xl overflow-x-auto max-w-full">
                    @if(!$isStaff)
                        <a href="{{ url('admin') }}?status=Pending" data-status="Pending"
                            class="tab-link px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? 'Pending') == 'Pending' ? 'bg-brand-red text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                            Pending
                            <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]" id="tab-count-pending">{{ $tabCounts['pending'] ?? 0 }}</span>
                        </a>
                    @endif
                    <a href="{{ url('admin') }}?status=Pending Approval" data-status="Pending Approval"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'Pending Approval' ? 'bg-amber-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                        Pending Approval
                        <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]" id="tab-count-pending_approval">{{ $tabCounts['pending_approval'] ?? 0 }}</span>
                    </a>
                    <a href="{{ url('admin') }}?status=Processing" data-status="Processing"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'Processing' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                        In Progress
                        <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]" id="tab-count-processing">{{ $tabCounts['processing'] ?? 0 }}</span>
                    </a>
                    <a href="{{ url('admin') }}?status=Completed" data-status="Completed"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'Completed' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                        Completed
                        <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]" id="tab-count-completed">{{ $tabCounts['completed'] ?? 0 }}</span>
                    </a>
                    <a href="{{ url('admin') }}?status=Cancelled" data-status="Cancelled"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'Cancelled' ? 'bg-slate-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                        Cancelled
                        <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]" id="tab-count-cancelled">{{ $tabCounts['cancelled'] ?? 0 }}</span>
                    </a>
                    <a href="{{ url('admin') }}?status=All" data-status="All"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'All' ? 'bg-slate-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                        All Orders
                        <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]" id="tab-count-all">{{ $tabCounts['all'] ?? 0 }}</span>
                    </a>
                </div>

                <a href="{{ url('admin/export/orders') }}"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 px-5 rounded-xl flex items-center gap-2 transition-all duration-200 shadow-lg shadow-emerald-900/20 whitespace-nowrap">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>

            <form action="{{ url('admin') }}" method="GET"
                class="mb-7 grid grid-cols-1 md:grid-cols-4 gap-4 bg-slate-800/30 p-5 rounded-xl border border-border-subtle">
                <input type="hidden" name="status" value="{{ $statusTab ?? 'Pending' }}">
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Customer/ID..."
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500/30 transition">
                </div>
                <div>
                    <input type="date" name="date_start" value="{{ request('date_start') }}"
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500/30 transition">
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" name="min_amount" placeholder="Min RM" value="{{ request('min_amount') }}"
                        class="w-1/2 bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none transition">
                    <input type="number" name="max_amount" placeholder="Max RM" value="{{ request('max_amount') }}"
                        class="w-1/2 bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none transition">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-brand-red hover:bg-brand-red-hover text-white font-semibold py-2.5 px-5 rounded-xl transition-all duration-200 text-sm flex-1 shadow-lg shadow-red-900/20">
                        Filter
                    </button>
                    @if(request()->has('search') || request()->has('date_start') || request()->has('min_amount'))
                        <a href="{{ url('admin') }}?status={{ $statusTab ?? 'Pending' }}"
                            class="bg-slate-700/50 hover:bg-slate-700 text-slate-400 hover:text-white px-4 py-2.5 rounded-xl text-sm transition flex items-center justify-center">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>

            <form action="{{ url('admin/batch') }}" method="POST" id="batchForm">
                @csrf
                <div id="batchActionBar"
                    class="hidden mb-5 bg-brand-red/10 border border-brand-red/20 p-4 rounded-xl flex items-center justify-between">
                    <div class="text-brand-red font-semibold text-sm">
                        <span id="selectedCount">0</span> orders selected
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="action" value="processing"
                            class="text-xs bg-brand-red hover:bg-brand-red-hover text-white px-4 py-2 rounded-lg transition font-semibold">
                            Mark Processing
                        </button>
                        <button type="submit" name="action" value="cancelled"
                            class="text-xs bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg transition font-semibold">
                            Mark Cancelled
                        </button>
                        @if(!$isStaff)
                            <button type="submit" name="action" value="delete"
                                class="text-xs bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition font-semibold ml-2 border border-red-700">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border-subtle">
                                <th class="px-5 py-4 w-10">
                                    <input type="checkbox" id="selectAll" onclick="toggleAll(this)"
                                        class="rounded bg-slate-700 border-slate-600 text-brand-red focus:ring-0 cursor-pointer">
                                </th>
                                <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Plan / Amount</th>
                                <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm" id="orders-table-body">
                            @include('admin.partials.dashboard_rows')
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        <!-- Recent Activity -->
        <h2 class="text-lg font-semibold text-white mb-4 mt-10">Recent Activity</h2>
        <div class="h-52 overflow-y-auto pr-2 space-y-2.5">
            @forelse($recentOrders as $order)
                <div class="exec-card px-5 py-4 flex items-center justify-between text-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full flex-shrink-0
                            {{ $order->status == 'Paid' || $order->status == 'Completed' ? 'bg-emerald-400' : ($order->status == 'Processing' ? 'bg-blue-400' : ($order->status == 'Rejected' ? 'bg-red-400' : ($order->current_step == 7 || $order->status == 'Review' ? 'bg-amber-400' : 'bg-amber-400'))) }}">
                        </div>
                        <div>
                            <span class="font-medium text-slate-200">Order #{{ $order->order_id }}</span>
                            <span class="text-slate-500"> from {{ $order->customer_name }}</span>
                            <div class="text-xs text-slate-600 mt-0.5">
                                {{ ($order->current_step == 7 || $order->status == 'Review') ? 'Waiting for Approval' : $order->status }}
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ $order->created_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="text-center text-slate-500 py-6">No recent activity</div>
            @endforelse
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tableBody = document.getElementById('orders-table-body');

            // Track current tab so poll returns the same list as the visible tab (single source of truth)
            let currentTabStatus = '{{ $statusTab ?? "Pending" }}';

            tabLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentTabStatus = this.getAttribute('data-status');
                    tabLinks.forEach(l => {
                        l.classList.remove('bg-brand-red', 'bg-amber-600', 'bg-blue-600', 'bg-emerald-600', 'bg-slate-600', 'text-white', 'shadow-md');
                        l.classList.add('text-slate-400');
                    });
                    this.classList.remove('text-slate-400');
                    this.classList.add('text-white', 'shadow-md');

                    const status = this.getAttribute('data-status');
                    if (status === 'Pending') this.classList.add('bg-brand-red');
                    else if (status === 'Pending Approval') this.classList.add('bg-amber-600');
                    else if (status === 'Processing') this.classList.add('bg-blue-600');
                    else if (status === 'Completed') this.classList.add('bg-emerald-600');
                    else this.classList.add('bg-slate-600');

                    fetch(this.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(response => response.text())
                        .then(html => {
                            tableBody.innerHTML = html;
                            history.pushState(null, '', this.href);
                            updateBatchBar();
                            const newCheckboxes = document.querySelectorAll('.order-checkbox');
                            newCheckboxes.forEach(cb => {
                                cb.addEventListener('change', function () {
                                    if (!this.checked && selectAll) selectAll.checked = false;
                                    updateBatchBar();
                                });
                            });
                            if (selectAll) selectAll.checked = false;
                        })
                        .catch(error => console.error('Error:', error));
                });
            });

            let latestSeenId = {{ $orders->first()->id ?? 0 }};
            const todayCountEl = document.getElementById('today-orders-count');
            const pendingCountEl = document.getElementById('pending-orders-count');

            function updateCountsFromApi(data) {
                if (todayCountEl) todayCountEl.innerText = data.today_orders ?? todayCountEl.innerText;
                if (pendingCountEl) pendingCountEl.innerText = data.pending_count ?? pendingCountEl.innerText;
                if (data.tab_counts) {
                    ['pending', 'pending_approval', 'processing', 'completed', 'cancelled', 'all'].forEach(function (k) {
                        const el = document.getElementById('tab-count-' + k);
                        if (el && data.tab_counts[k] !== undefined) el.textContent = data.tab_counts[k];
                    });
                }
                const todaySalesEl = document.getElementById('today-sales-amount');
                if (todaySalesEl && data.today_sales !== undefined) todaySalesEl.textContent = 'RM ' + Number(data.today_sales).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const totalRevEl = document.getElementById('total-revenue-amount');
                if (totalRevEl && data.total_revenue !== undefined) totalRevEl.textContent = 'RM ' + Number(data.total_revenue).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function refreshTableFromPoll(data) {
                if (!data.html) return;
                tableBody.innerHTML = data.html;
                if (data.latest_order_id > latestSeenId) {
                    const rows = tableBody.querySelectorAll('tr');
                    if (rows.length > 0) rows[0].classList.add('flash-row');
                    latestSeenId = data.latest_order_id;
                }
                if (window.updateBatchBar) window.updateBatchBar();
                const newCheckboxes = document.querySelectorAll('.order-checkbox');
                newCheckboxes.forEach(cb => {
                    cb.addEventListener('change', function () {
                        if (!this.checked && selectAll) selectAll.checked = false;
                        if (window.updateBatchBar) window.updateBatchBar();
                    });
                });
                if (selectAll) selectAll.checked = false;
            }

            setInterval(() => {
                const pollUrl = '{{ url("admin/api/updates") }}?status=' + encodeURIComponent(currentTabStatus) + '&_=' + Date.now();
                fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) return;
                        updateCountsFromApi(data);
                        // Always refresh table with current tab's list so orders that left the tab (e.g. Pending → In Progress) disappear
                        refreshTableFromPoll(data);
                    })
                    .catch(err => console.error('Sync Error:', err));
            }, 15000);

            window.toggleAll = function (source) {
                const checkboxes = document.getElementsByClassName('order-checkbox');
                for (var i = 0, n = checkboxes.length; i < n; i++) checkboxes[i].checked = source.checked;
                updateBatchBar();
            }

            window.updateBatchBar = function () {
                const checkboxes = document.querySelectorAll('.order-checkbox:checked');
                const count = checkboxes.length;
                const bar = document.getElementById('batchActionBar');
                const countSpan = document.getElementById('selectedCount');
                if (countSpan) countSpan.innerText = count;
                if (bar) { count > 0 ? bar.classList.remove('hidden') : bar.classList.add('hidden'); }
            }

            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.order-checkbox');
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    Array.from(checkboxes).forEach(cb => cb.checked = this.checked);
                    updateBatchBar();
                });
            }
            Array.from(checkboxes).forEach(cb => {
                cb.addEventListener('change', function () {
                    if (!this.checked && selectAll) selectAll.checked = false;
                    updateBatchBar();
                });
            });

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 12 } } } },
                scales: {
                    y: { grid: { color: 'rgba(148,163,184,0.08)' }, ticks: { color: '#64748b' } },
                    x: { grid: { color: 'rgba(148,163,184,0.08)' }, ticks: { color: '#64748b' } }
                }
            };

            const salesCanvas = document.getElementById('salesChart');
            if (salesCanvas) {
                new Chart(salesCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartLabels) !!},
                        datasets: [{
                            label: 'Sales (RM)',
                            data: {!! json_encode($chartValues) !!},
                            borderColor: '#E31E24',
                            backgroundColor: 'rgba(227, 30, 36, 0.08)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#E31E24',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                        }]
                    },
                    options: commonOptions
                });
            }

            const planCanvas = document.getElementById('planChart');
            if (planCanvas) {
                new Chart(planCanvas.getContext('2d'), {
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
                        plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', padding: 16, font: { family: 'Inter' } } } },
                        cutout: '65%',
                    }
                });
            }
        });
    </script>
@endsection
