@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold">BI Dashboard</h1>
                <p class="text-gray-500 text-sm mt-1">Real-time business performance.</p>
            </div>
            <div class="text-right">
                <span class="bg-brand-gray px-4 py-2 rounded-full text-xs font-mono text-gray-400 border border-white/5">
                    {{ date('d M Y') }}
                </span>
            </div>
        </div>

        <style>
            @keyframes highlightFade {
                0% {
                    background-color: rgba(255, 45, 70, 0.2);
                }

                100% {
                    background-color: transparent;
                }
            }

            .flash-row {
                animation: highlightFade 3s ease-out;
            }
        </style>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Today's Sales (Admin Only) or Orders (Staff) -->
            @if(!$isStaff)
                <div
                    class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg hover:border-brand-red/50 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs text-gray-500 uppercase font-bold tracking-wider">Today's Sales</div>
                        <i class="fas fa-calendar-day text-brand-red opacity-50"></i>
                    </div>
                    <div class="text-2xl font-bold text-white">RM {{ number_format($todaySales ?? 0, 2) }}</div>
                    <div class="text-xs text-green-500 mt-1 flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> Live
                    </div>
                </div>
            @endif

            <!-- Today's Orders / My New Orders -->
            <div
                class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg hover:border-brand-red/50 transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-xs text-gray-500 uppercase font-bold tracking-wider">
                        {{ $isStaff ? 'My New Orders' : "Today's Orders" }}
                    </div>
                    <i class="fas fa-shopping-cart text-brand-red opacity-50"></i>
                </div>
                <div class="text-2xl font-bold text-white" id="today-orders-count">{{ $todayOrders ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Today's inputs</div>
            </div>

            @if(!$isStaff)
                <!-- Total Revenue (Lifetime) - Admin Only -->
                <div
                    class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg hover:border-green-500/50 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Revenue</div>
                        <i class="fas fa-wallet text-green-500 opacity-50"></i>
                    </div>
                    <div class="text-2xl font-bold text-white">RM {{ number_format($totalRevenue ?? 0, 2) }}</div>
                </div>
            @endif

            <!-- Pending / Workload -->
            <div
                class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg hover:border-yellow-500/50 transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-xs text-gray-500 uppercase font-bold tracking-wider">
                        {{ $isStaff ? 'My Pending Work' : 'Pending Orders' }}
                    </div>
                    <i class="fas fa-clock text-yellow-500 opacity-50"></i>
                </div>
                <div class="text-2xl font-bold text-white" id="pending-orders-count">{{ $pendingCount ?? 0 }}</div>
            </div>

            @if($isStaff)
                <!-- Completed (Staff View) -->
                <div
                    class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg hover:border-green-500/50 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs text-gray-500 uppercase font-bold tracking-wider">My Completed</div>
                        <i class="fas fa-check-circle text-green-500 opacity-50"></i>
                    </div>
                    <div class="text-2xl font-bold text-white">{{ $completedCount ?? 0 }}</div>
                </div>
            @endif
        </div>

        @if(!$isStaff)
            <!-- Charts Section (Admin Only) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Sales Trend Chart (Width: 2/3) -->
                <div class="lg:col-span-2 bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg">
                    <h3 class="text-lg font-bold mb-6">30-Day Sales Trend</h3>
                    <div class="relative h-[300px] w-full">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Plan Popularity Chart (Width: 1/3) -->
                <div class="lg:col-span-1 bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-lg">
                    <h3 class="text-lg font-bold mb-6">Plan Popularity</h3>
                    <div class="relative h-[300px] w-full flex items-center justify-center">
                        <canvas id="planChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        <!-- Fulfillment Section -->
        <!-- Main Order Table (Dynamic) -->
        <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-xl relative">

            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6">
                <!-- Tabs -->
                <div class="flex bg-black/40 p-1 rounded-xl overflow-x-auto max-w-full">
                    @if(!$isStaff)
                        <a href="{{ url('admin') }}?status=Pending" data-status="Pending"
                            class="tab-link px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? 'Pending') == 'Pending' ? 'bg-brand-red text-white shadow-lg' : 'text-gray-400 hover:text-white' }}">Pending</a>
                    @endif
                    <a href="{{ url('admin') }}?status=Processing" data-status="Processing"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? '') == 'Processing' ? 'bg-orange-500 text-white shadow-lg' : 'text-gray-400 hover:text-white' }}">Processing</a>
                    <a href="{{ url('admin') }}?status=Completed" data-status="Completed"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? '') == 'Completed' ? 'bg-green-500 text-white shadow-lg' : 'text-gray-400 hover:text-white' }}">Completed</a>
                    <a href="{{ url('admin') }}?status=Cancelled" data-status="Cancelled"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? '') == 'Cancelled' ? 'bg-red-900/50 text-red-200' : 'text-gray-400 hover:text-white' }}">Cancelled</a>
                    <a href="{{ url('admin') }}?status=All" data-status="All"
                        class="tab-link px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? '') == 'All' ? 'bg-white/10 text-white' : 'text-gray-400 hover:text-white' }}">All
                        Orders</a>
                </div>

                <!-- Export Button -->
                <a href="{{ url('admin/export/orders') }}"
                    class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition shadow-lg shadow-green-900/20 whitespace-nowrap">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>

            <form action="{{ url('admin') }}" method="GET"
                class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-black/20 p-4 rounded-xl border border-white/5">
                <input type="hidden" name="status" value="{{ $statusTab ?? 'Pending' }}">

                <!-- Search -->
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Customer/ID..."
                        class="w-full bg-brand-dark border border-white/10 rounded-lg pl-9 pr-3 py-2 text-sm text-white focus:border-brand-red focus:outline-none">
                </div>

                <!-- Date Range -->
                <div class="flex items-center gap-2">
                    <input type="date" name="date_start" value="{{ request('date_start') }}"
                        class="w-full bg-brand-dark border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-red focus:outline-none placeholder-gray-500">
                </div>

                <!-- Amount Range -->
                <div class="flex items-center gap-2">
                    <input type="number" name="min_amount" placeholder="Min RM" value="{{ request('min_amount') }}"
                        class="w-1/2 bg-brand-dark border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-red focus:outline-none">
                    <input type="number" name="max_amount" placeholder="Max RM" value="{{ request('max_amount') }}"
                        class="w-1/2 bg-brand-dark border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-red focus:outline-none">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-brand-red hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition text-sm flex-1">
                        Filter
                    </button>
                    @if(request()->has('search') || request()->has('date_start') || request()->has('min_amount'))
                        <a href="{{ url('admin') }}?status={{ $statusTab ?? 'Pending' }}"
                            class="bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white px-4 py-2 rounded-lg text-sm transition flex items-center justify-center">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>

            <form action="{{ url('admin/batch') }}" method="POST" id="batchForm">
                @csrf
                <!-- Batch Action Bar (Hidden by default) -->
                <div id="batchActionBar"
                    class="hidden mb-4 bg-brand-red/10 border border-brand-red/20 p-3 rounded-xl flex items-center justify-between">
                    <div class="text-brand-red font-bold text-sm">
                        <span id="selectedCount">0</span> orders selected
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="action" value="processing"
                            class="text-xs bg-brand-red hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition font-bold">
                            Mark Processing
                        </button>
                        <button type="submit" name="action" value="completed"
                            class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition font-bold">
                            Mark Completed
                        </button>
                        <button type="submit" name="action" value="cancelled"
                            class="text-xs bg-gray-600 hover:bg-gray-700 text-white px-3 py-1.5 rounded-lg transition font-bold">
                            Mark Cancelled
                        </button>
                        @if(!$isStaff)
                            <button type="submit" name="action" value="delete"
                                class="text-xs bg-red-900 hover:bg-red-800 text-white px-3 py-1.5 rounded-lg transition font-bold ml-2 border border-red-700">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-500 text-xs uppercase border-b border-white/5">
                                <th class="p-4 w-10">
                                    <input type="checkbox" id="selectAll" onclick="toggleAll(this)"
                                        class="rounded bg-white/10 border-white/20 text-brand-red focus:ring-0 cursor-pointer">
                                </th>
                                <th class="p-4 font-bold">Order ID</th>
                                <th class="p-4 font-bold">Customer</th>
                                <th class="p-4 font-bold">Plan / Amount</th>
                                <th class="p-4 font-bold">Status</th>
                                <th class="p-4 font-bold">Date</th>
                                <th class="p-4 font-bold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm" id="orders-table-body">
                            @include('admin.partials.dashboard_rows')
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        <!-- Recent Activity (MOCK DATA for Visualization) -->
        <h2 class="text-xl font-bold mb-4 mt-8 px-2">Recent Activity</h2>
        <div class="h-48 overflow-y-auto pr-2 space-y-2 custom-scrollbar">
            @forelse($recentOrders as $order)
                <div
                    class="bg-brand-dark border border-white/5 p-3 rounded-xl flex items-center justify-between text-sm hover:bg-white/5 transition">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-2 h-2 rounded-full {{ $order->status == 'Paid' || $order->status == 'Completed' ? 'bg-green-500' : ($order->status == 'Processing' ? 'bg-orange-500' : ($order->status == 'Rejected' ? 'bg-red-500' : ($order->current_step == 7 || $order->status == 'Review' ? 'bg-yellow-500' : 'bg-yellow-500'))) }}">
                        </div>
                        <div>
                            <span class="font-bold text-gray-300">Order #{{ $order->order_id }}</span>
                            <span class="text-gray-500"> from {{ $order->customer_name }}</span>
                            <div class="text-xs text-gray-600">Status:
                                {{ ($order->current_step == 7 || $order->status == 'Review') ? 'Waiting for Approval' : $order->status }}
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-mono">
                        {{ $order->created_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">No recent activity</div>
            @endforelse
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // AJAX Tabs
            const tabLinks = document.querySelectorAll('.tab-link');
            const tableBody = document.getElementById('orders-table-body');

            tabLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Visual Update
                    tabLinks.forEach(l => {
                        l.classList.remove('bg-brand-red', 'bg-orange-500', 'bg-green-500', 'bg-red-900/50', 'bg-white/10', 'text-white', 'shadow-lg', 'text-red-200');
                        l.classList.add('text-gray-400');
                    });

                    // Simple logic to re-apply active classes based on status text
                    // This is a quick visual fix, ideally server returns active state or we handle it more robustly
                    this.classList.remove('text-gray-400');
                    this.classList.add('text-white', 'shadow-lg');

                    const status = this.getAttribute('data-status');
                    if (status === 'Pending') this.classList.add('bg-brand-red');
                    else if (status === 'Processing') this.classList.add('bg-orange-500');
                    else if (status === 'Completed') this.classList.add('bg-green-500');
                    else if (status === 'Cancelled') this.classList.add('bg-red-900/50', 'text-red-200');
                    else this.classList.add('bg-white/10');

                    // Fetch Content
                    fetch(this.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(response => response.text())
                        .then(html => {
                            tableBody.innerHTML = html;
                            history.pushState(null, '', this.href);
                            // Re-initialize batch bar logic for new content
                            updateBatchBar();
                            const newCheckboxes = document.querySelectorAll('.order-checkbox');
                            newCheckboxes.forEach(cb => {
                                cb.addEventListener('change', function () {
                                    if (!this.checked && selectAll) selectAll.checked = false;
                                    updateBatchBar();
                                });
                            });
                            if (selectAll) selectAll.checked = false; // Uncheck select all when new content loads
                        })
                        .catch(error => console.error('Error:', error));
                });
            });

            // Live Sync Logic (15s Polling)
            let latestSeenId = {{ $orders->first()->id ?? 0 }};
            const todayCountEl = document.getElementById('today-orders-count');
            const pendingCountEl = document.getElementById('pending-orders-count');

            // Only poll if we are on the 'Pending' or 'All' tab (or default)
            // Simplified: Poll always, but only inject if on relevant tab to avoid confusion

            setInterval(() => {
                fetch('{{ url("admin/api/updates") }}')
                    .then(res => res.json())
                    .then(data => {
                        // 1. Update Counters
                        if (todayCountEl) todayCountEl.innerText = data.today_orders;
                        if (pendingCountEl) pendingCountEl.innerText = data.pending_count;

                        // 2. Check for new orders
                        if (data.latest_order_id > latestSeenId) {

                            // Check if we are on Pending tab to prepend
                            const activeTab = document.querySelector('.tab-link.bg-brand-red'); // Pending tab has this class
                            if (activeTab || !document.querySelector('.tab-link.shadow-lg')) { // Or if no tab selected (default)

                                // We fetch the partial HTML and inject it. 
                                // Efficient approach: replace the whole tbody or prepend just the new ones?
                                // For simplicity and consistency with the "partial" approach, we'll replace the table content
                                // IF the user is on the first page/view. 
                                // Better UX: Prepend. But our partial returns ALL 10 latest.
                                // So we replace the table body which is cleaner for "Live Sync" of the view.

                                tableBody.innerHTML = data.html;

                                // Add animation to the new rows (top ones)
                                const rows = tableBody.querySelectorAll('tr');
                                // Highlight top rows that are new
                                rows.forEach(row => {
                                    // Logic to find if this row ID > latestSeenId (need parsing or just highlight first one)
                                    // Simple visual cue: flash the first row
                                });
                                if (rows.length > 0) rows[0].classList.add('flash-row');

                                // Re-init batch listeners
                                if (window.updateBatchBar) window.updateBatchBar();
                                const newCheckboxes = document.querySelectorAll('.order-checkbox');
                                newCheckboxes.forEach(cb => {
                                    cb.addEventListener('change', function () {
                                        if (!this.checked && selectAll) selectAll.checked = false;
                                        if (window.updateBatchBar) window.updateBatchBar();
                                    });
                                });
                            }
                            latestSeenId = data.latest_order_id;
                        }
                    })
                    .catch(err => console.error('Sync Error:', err));
            }, 15000);

            // Checkbox & Batch Logic
            window.toggleAll = function (source) {
                const checkboxes = document.getElementsByClassName('order-checkbox');
                for (var i = 0, n = checkboxes.length; i < n; i++) {
                    checkboxes[i].checked = source.checked;
                }
                updateBatchBar();
            }

            window.updateBatchBar = function () {
                const checkboxes = document.querySelectorAll('.order-checkbox:checked');
                const count = checkboxes.length;
                const bar = document.getElementById('batchActionBar');
                const countSpan = document.getElementById('selectedCount');

                if (countSpan) countSpan.innerText = count;

                if (bar) {
                    if (count > 0) {
                        bar.classList.remove('hidden');
                    } else {
                        bar.classList.add('hidden');
                    }
                }
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

            // --- Charts Configuration ---
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

            // Sales Trend Chart (Mock Data)
            const salesCanvas = document.getElementById('salesChart');
            if (salesCanvas) {
                const ctxSales = salesCanvas.getContext('2d');
                new Chart(ctxSales, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartLabels) !!},
                        datasets: [{
                            label: 'Sales (RM)',
                            data: {!! json_encode($chartValues) !!}, // Dynamic Data
                            borderColor: '#FF2D46',
                            backgroundColor: 'rgba(255, 45, 70, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#FF2D46'
                        }]
                    },
                    options: commonOptions
                });
            }

            // Plan Popularity Chart (Mock Data)
            const planCanvas = document.getElementById('planChart');
            if (planCanvas) {
                const ctxPlan = planCanvas.getContext('2d');
                new Chart(ctxPlan, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($planLabels) !!},
                        datasets: [{
                            data: {!! json_encode($planValues) !!}, // Dynamic Data
                            backgroundColor: ['#FF2D46', '#25D366', '#3B82F6', '#F59E0B', '#6366F1'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#9CA3AF' } }
                        }
                    }
                });
            }
        });
    </script>
@endsection