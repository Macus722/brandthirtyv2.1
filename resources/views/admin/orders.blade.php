@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 flex-shrink-0">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Orders Management</h1>
                <p class="text-slate-500 text-sm mt-1">View and manage all customer orders.</p>
            </div>
            <div>
                <span class="bg-surface-raised backdrop-blur border border-border-subtle px-5 py-2.5 rounded-xl text-xs text-slate-400 font-medium">
                    {{ date('d M Y') }}
                </span>
            </div>
        </div>

        <div class="flex-1 overflow-hidden flex flex-col">
            <div class="exec-card relative flex flex-col h-full overflow-hidden">

                <!-- Toolbar -->
                <div class="p-7 border-b border-border-subtle flex-shrink-0 space-y-5">
                    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
                        <div class="flex bg-slate-800/60 p-1 rounded-xl overflow-x-auto max-w-full">
                            <a href="{{ url('admin/orders') }}?status=Pending" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? 'Pending') == 'Pending' ? 'bg-brand-red text-white shadow-md' : 'text-slate-400 hover:text-white' }}">Pending</a>
                            <a href="{{ url('admin/orders') }}?status=Processing" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'Processing' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">Processing</a>
                            <a href="{{ url('admin/orders') }}?status=Completed" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'Completed' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">Completed</a>
                            <a href="{{ url('admin/orders') }}?status=Cancelled" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'Cancelled' ? 'bg-slate-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">Cancelled</a>
                            <a href="{{ url('admin/orders') }}?status=All" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 {{ ($statusTab ?? '') == 'All' ? 'bg-slate-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">All Orders</a>
                        </div>

                        <a href="{{ url('admin/export/orders') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 px-5 rounded-xl flex items-center gap-2 transition-all duration-200 shadow-lg shadow-emerald-900/20 whitespace-nowrap">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>

                    <form action="{{ url('admin/orders') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-slate-800/30 p-5 rounded-xl border border-border-subtle">
                        <input type="hidden" name="status" value="{{ $statusTab ?? 'Pending' }}">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Customer/ID..."
                                class="w-full bg-slate-800/50 border border-border-subtle rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none transition">
                        </div>
                        <div>
                            <input type="date" name="date_start" value="{{ request('date_start') }}" class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white focus:border-slate-500 focus:outline-none transition">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_amount" placeholder="Min RM" value="{{ request('min_amount') }}" class="w-1/2 bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none transition">
                            <input type="number" name="max_amount" placeholder="Max RM" value="{{ request('max_amount') }}" class="w-1/2 bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none transition">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-brand-red hover:bg-brand-red-hover text-white font-semibold py-2.5 px-5 rounded-xl transition-all duration-200 text-sm flex-1 shadow-lg shadow-red-900/20">
                                Filter
                            </button>
                            @if(request()->has('search') || request()->has('date_start') || request()->has('min_amount'))
                                <a href="{{ url('admin/orders') }}?status={{ $statusTab ?? 'Pending' }}" class="bg-slate-700/50 hover:bg-slate-700 text-slate-400 hover:text-white px-4 py-2.5 rounded-xl text-sm transition flex items-center justify-center">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Batch Actions -->
                <form action="{{ url('admin/batch') }}" method="POST" id="batchForm" class="flex-1 flex flex-col overflow-hidden">
                    @csrf
                    <div id="batchActionBar" class="hidden mx-7 mt-5 bg-brand-red/10 border border-brand-red/20 p-4 rounded-xl flex items-center justify-between flex-shrink-0">
                        <div class="text-brand-red font-semibold text-sm">
                            <span id="selectedCount">0</span> orders selected
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" name="action" value="processing" class="text-xs bg-brand-red hover:bg-brand-red-hover text-white px-4 py-2 rounded-lg transition font-semibold">Mark Processing</button>
                            <button type="submit" name="action" value="completed" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition font-semibold">Mark Completed</button>
                            <button type="submit" name="action" value="cancelled" class="text-xs bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg transition font-semibold">Mark Cancelled</button>
                            <button type="submit" name="action" value="delete" class="text-xs bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition font-semibold ml-2 border border-red-700">Delete</button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-7 pt-3">
                        <table class="w-full text-left">
                            <thead class="sticky top-0 bg-surface/95 backdrop-blur z-10">
                                <tr class="border-b border-border-subtle">
                                    <th class="px-5 py-4 w-10">
                                        <input type="checkbox" id="selectAll" class="rounded bg-slate-700 border-slate-600 text-brand-red focus:ring-0 cursor-pointer">
                                    </th>
                                    <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Plan / Strategy</th>
                                    <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                    <th class="px-5 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @if(isset($orders) && count($orders) > 0)
                                    @foreach($orders as $order)
                                    <tr class="border-b border-border-subtle hover:bg-white/[0.02] transition-colors duration-150 group">
                                        <td class="px-5 py-4">
                                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded bg-slate-700 border-slate-600 text-brand-red focus:ring-0 cursor-pointer" onchange="updateBatchBar()">
                                        </td>
                                        <td class="px-5 py-4 text-slate-400 font-medium">{{ $order->order_id }}</td>
                                        <td class="px-5 py-4">
                                            <div class="font-medium text-white">{{ $order->customer_name }}</div>
                                            <div class="text-xs text-slate-500 mt-0.5">{{ $order->customer_email }}</div>
                                            <div class="text-xs text-slate-500">{{ $order->company_name }}</div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-700/50 text-slate-200 block w-fit mb-1">{{ $order->plan }}</span>
                                            <div class="text-xs text-slate-500 truncate max-w-[150px]" title="{{ $order->strategy }}">{{ $order->strategy }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-slate-300 font-medium">RM {{ number_format($order->total_amount) }}</td>
                                        <td class="px-5 py-4">
                                            @if($order->status == 'Completed' || $order->status == 'Paid')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400">
                                                    <i class="fas fa-check-circle text-[9px]"></i> Completed
                                                </span>
                                            @elseif($order->status == 'Rejected')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/15 text-red-400">
                                                    <i class="fas fa-times-circle text-[9px]"></i> Cancelled
                                                </span>
                                            @elseif($order->status == 'Review')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-400">
                                                    <i class="fas fa-hourglass-half text-[9px]"></i> Pending Approval
                                                </span>
                                            @elseif($order->status == 'Processing' || $order->status == 'In Progress' || $order->status == 'Assigned')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/15 text-blue-400">
                                                    <i class="fas fa-spinner fa-spin text-[9px]"></i> In Progress
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-400">
                                                    <i class="fas fa-clock text-[9px]"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ $order->created_at->format('M d, H:i') }}</td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <a href="{{ url('admin/invoice/'.$order->id.'/download') }}" class="w-8 h-8 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition" title="Download Invoice">
                                                    <i class="fas fa-file-pdf text-xs"></i>
                                                </a>
                                                <a href="{{ url('admin/edit/'.$order->id) }}" class="w-8 h-8 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition" title="Edit Order">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                                @if($order->status == 'Pending')
                                                    <a href="{{ url('admin/paid/'.$order->id) }}" class="w-8 h-8 rounded-lg bg-emerald-500/15 hover:bg-emerald-600 text-emerald-400 hover:text-white flex items-center justify-center transition" title="Accept">
                                                        <i class="fas fa-check-circle text-xs"></i>
                                                    </a>
                                                    <a href="{{ url('admin/reject/'.$order->id) }}" onclick="return confirm('Are you sure you want to reject this order?')" class="w-8 h-8 rounded-lg bg-red-500/15 hover:bg-red-600 text-red-400 hover:text-white flex items-center justify-center transition" title="Reject">
                                                        <i class="fas fa-times-circle text-xs"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="px-5 py-16 text-center text-slate-500">
                                            <i class="fas fa-inbox text-3xl mb-3 opacity-30 block"></i>
                                            <h3 class="text-base font-semibold text-slate-400">No orders found</h3>
                                            <p class="text-sm mt-1">Try adjusting your filters or tabs.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div class="mt-6">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.updateBatchBar = function() {
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
                selectAll.addEventListener('change', function() {
                    Array.from(checkboxes).forEach(cb => cb.checked = this.checked);
                    updateBatchBar();
                });
            }
            Array.from(checkboxes).forEach(cb => {
                cb.addEventListener('change', function() {
                    if (!this.checked && selectAll) selectAll.checked = false;
                    updateBatchBar();
                });
            });
        });
    </script>
@endsection
