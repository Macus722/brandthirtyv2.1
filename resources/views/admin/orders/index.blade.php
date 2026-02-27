@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Order Management</h1>
                <p class="text-slate-500 text-sm mt-1">View and manage all customer orders.</p>
            </div>
            @php $currentStatus = $status ?? request('status', auth()->user()->role == 'staff' ? 'Processing' : 'Pending'); @endphp
            <div class="flex flex-wrap gap-2">
                @if(auth()->user()->role != 'staff')
                    <a href="{{ url('admin/orders?status=Pending') }}"
                        class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border
                        {{ $currentStatus == 'Pending' ? 'bg-brand-red text-white border-brand-red shadow-lg shadow-red-900/20' : 'bg-surface-card backdrop-blur border-border-subtle text-slate-400 hover:text-white hover:border-slate-500' }}">
                        Pending
                        <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]">{{ $tabCounts['pending'] ?? 0 }}</span>
                    </a>
                @endif
                <a href="{{ url('admin/orders?status=Pending Approval') }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border
                    {{ $currentStatus == 'Pending Approval' ? 'bg-amber-600 text-white border-amber-600 shadow-lg shadow-amber-900/20' : 'bg-surface-card backdrop-blur border-border-subtle text-slate-400 hover:text-white hover:border-slate-500' }}">
                    Pending Approval
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]">{{ $tabCounts['pending_approval'] ?? 0 }}</span>
                </a>
                <a href="{{ url('admin/orders?status=Processing') }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border
                    {{ $currentStatus == 'Processing' ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-900/20' : 'bg-surface-card backdrop-blur border-border-subtle text-slate-400 hover:text-white hover:border-slate-500' }}">
                    In Progress
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]">{{ $tabCounts['processing'] ?? 0 }}</span>
                </a>
                <a href="{{ url('admin/orders?status=Completed') }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border
                    {{ $currentStatus == 'Completed' ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-900/20' : 'bg-surface-card backdrop-blur border-border-subtle text-slate-400 hover:text-white hover:border-slate-500' }}">
                    Completed
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]">{{ $tabCounts['completed'] ?? 0 }}</span>
                </a>
                <a href="{{ url('admin/orders?status=Cancelled') }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border
                    {{ $currentStatus == 'Cancelled' ? 'bg-slate-600 text-white border-slate-600 shadow-lg' : 'bg-surface-card backdrop-blur border-border-subtle text-slate-400 hover:text-white hover:border-slate-500' }}">
                    Cancelled
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]">{{ $tabCounts['cancelled'] ?? 0 }}</span>
                </a>
                <a href="{{ url('admin/orders?status=All') }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border
                    {{ $currentStatus == 'All' ? 'bg-slate-600 text-white border-slate-600 shadow-lg' : 'bg-surface-card backdrop-blur border-border-subtle text-slate-400 hover:text-white hover:border-slate-500' }}">
                    All
                    <span class="ml-1 px-1.5 py-0.5 rounded bg-white/10 text-[10px]">{{ $tabCounts['all'] ?? 0 }}</span>
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-8 exec-card p-5">
            <form action="" method="GET" class="flex gap-4">
                <input type="hidden" name="status" value="{{ $currentStatus ?? request('status') }}">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by Order ID or Customer Name..."
                    class="flex-1 bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500/30 transition text-sm">
                <button type="submit"
                    class="bg-brand-red hover:bg-brand-red-hover text-white font-semibold py-3 px-7 rounded-xl transition-all duration-200 shadow-lg shadow-red-900/20 text-sm">
                    Search
                </button>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="exec-card overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-border-subtle bg-slate-800/30">
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Plan / Strategy</th>
                        @if($currentStatus == 'Pending')
                            <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Receipt</th>
                        @endif
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Time</th>
                        <th class="px-7 py-5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($orders as $order)
                        <tr class="border-b border-border-subtle hover:bg-white/[0.02] transition-colors duration-150 group">
                            <td class="px-7 py-5 text-slate-300 font-medium">#{{ $order->order_id }}</td>
                            <td class="px-7 py-5">
                                <div class="font-medium text-white">{{ $order->customer_name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $order->customer_email }}</div>
                            </td>
                            <td class="px-7 py-5">
                                <div class="font-semibold text-white">{{ $order->plan }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $order->strategy ?? 'N/A' }}</div>
                            </td>

                            @if($currentStatus == 'Pending')
                                <td class="px-7 py-5">
                                    @if($order->receipt_path && \Storage::disk('public')->exists($order->receipt_path))
                                        <div class="w-9 h-9 bg-emerald-500/15 rounded-lg flex items-center justify-center text-emerald-400">
                                            <i class="fas fa-file-invoice-dollar text-sm"></i>
                                        </div>
                                    @elseif($order->receipt_path)
                                        <div class="w-9 h-9 bg-amber-500/15 rounded-lg flex items-center justify-center text-amber-400">
                                            <i class="fas fa-exclamation-triangle text-sm"></i>
                                        </div>
                                    @else
                                         <span class="text-xs text-slate-600">No Receipt</span>
                                    @endif
                                </td>
                            @endif

                            <td class="px-7 py-5">
                                @if($order->status == 'Completed' || $order->status == 'Paid')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400">Completed</span>
                                @elseif($order->current_step == 7 || $order->status == 'Review')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-400"><i class="fas fa-clipboard-check mr-1 text-[9px]"></i> Pending Approval</span>
                                @elseif($order->status == 'Processing' || $order->status == 'Approved' || $order->status == 'In Progress' || $order->status == 'Assigned')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/15 text-blue-400">In Progress</span>
                                @elseif($order->status == 'Rejected')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/15 text-red-400">Rejected</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-400"><i class="fas fa-hourglass-half mr-1 text-[9px]"></i> Pending</span>
                                @endif
                            </td>
                            <td class="px-7 py-5 text-slate-500 text-sm">
                                {{ $order->created_at->diffForHumans() }}
                            </td>
                            <td class="px-7 py-5 text-right">
                                @php
                                    $isReviewStage = $order->current_step == 7 || $order->status == 'Review';
                                    $isCustomerPending = $order->status == 'Pending';
                                    $buttonLabel = $isReviewStage ? 'Review Work' : ($isCustomerPending ? 'Review' : 'Manage');
                                    $buttonHighlight = ($isCustomerPending && (!$order->is_payment_verified || !$order->is_content_verified)) || $isReviewStage;
                                @endphp
                                <a href="{{ url('admin/orders/' . $order->id) }}"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-xs font-semibold transition-all duration-200
                                    {{ $buttonHighlight ? ($isReviewStage ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-brand-red hover:bg-brand-red-hover text-white') : 'bg-slate-700/50 hover:bg-slate-700 text-slate-200' }}">
                                    {{ $buttonLabel }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $currentStatus == 'Pending' ? 7 : 6 }}" class="px-7 py-16 text-center text-slate-500">
                                <i class="fas fa-inbox text-3xl mb-3 opacity-30 block"></i>
                                <p class="text-sm">No orders found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-7 py-5 border-t border-border-subtle">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
