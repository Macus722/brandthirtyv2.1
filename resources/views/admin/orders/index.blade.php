@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Order Management</h1>
            <div class="flex gap-2">
                @if(auth()->user()->role != 'staff')
                <a href="{{ url('admin/orders?status=Pending') }}"
                    class="px-4 py-2 rounded-lg bg-brand-dark border border-white/10 hover:border-brand-red transition text-sm {{ request('status') == 'Pending' || !request('status') ? 'text-brand-red border-brand-red' : 'text-gray-400' }}">Pending</a>
                @endif
                <a href="{{ url('admin/orders?status=Processing') }}"
                    class="px-4 py-2 rounded-lg bg-brand-dark border border-white/10 hover:border-brand-red transition text-sm {{ request('status') == 'Processing' ? 'text-brand-red border-brand-red' : 'text-gray-400' }}">In Progress</a>
                <a href="{{ url('admin/orders?status=Completed') }}"
                    class="px-4 py-2 rounded-lg bg-brand-dark border border-white/10 hover:border-brand-red transition text-sm {{ request('status') == 'Completed' ? 'text-brand-red border-brand-red' : 'text-gray-400' }}">Completed</a>
                <a href="{{ url('admin/orders?status=All') }}"
                    class="px-4 py-2 rounded-lg bg-brand-dark border border-white/10 hover:border-brand-red transition text-sm {{ request('status') == 'All' ? 'text-brand-red border-brand-red' : 'text-gray-400' }}">All</a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-6 bg-brand-dark p-4 rounded-xl border border-white/10">
            <form action="" method="GET" class="flex gap-4">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by Order ID or Customer Name..."
                    class="flex-1 bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-brand-red focus:outline-none">
                <button type="submit"
                    class="bg-brand-red hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg transition">
                    Search
                </button>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-brand-dark border border-white/10 rounded-2xl overflow-hidden shadow-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black/20 text-gray-500 text-xs uppercase border-b border-white/5">
                        <th class="p-6 font-bold">Order ID</th>
                        <th class="p-6 font-bold">Customer</th>
                        <th class="p-6 font-bold">Plan / Strategy</th>
                        @if(request('status') == 'Pending' || !request('status'))
                            <th class="p-6 font-bold">Receipt</th>
                        @endif
                        <th class="p-6 font-bold">Status</th>
                        <th class="p-6 font-bold">Time Verified</th>
                        <th class="p-6 font-bold text-right py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-white/5">
                    @forelse($orders as $order)
                        <tr class="hover:bg-white/5 transition group">
                            <td class="p-6 font-mono text-gray-300">#{{ $order->order_id }}</td>
                            <td class="p-6">
                                <div class="font-bold text-white">{{ $order->customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $order->customer_email }}</div>
                            </td>
                            <td class="p-6">
                                <div class="text-brand-red font-bold">{{ $order->plan }}</div>
                                <div class="text-xs text-gray-400">{{ $order->strategy ?? 'N/A' }}</div>
                            </td>
                            
                            @if(request('status') == 'Pending' || !request('status'))
                                <td class="p-6">
                                    <!-- Placeholder for Receipt Thumbnail -->
                                    @if(isset($order->receipt_path) || $order->is_payment_verified)
                                        <div class="w-10 h-10 bg-gray-700 rounded flex items-center justify-center text-xs text-gray-400 border border-white/10">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </div>
                                    @else
                                         <span class="text-xs text-gray-600">No Receipt</span>
                                    @endif
                                </td>
                            @endif

                            <td class="p-6">
                                @if($order->status == 'Completed' || $order->status == 'Paid')
                                    <span class="bg-green-500/10 text-green-500 px-3 py-1 rounded-full text-xs font-bold border border-green-500/20">Completed</span>
                                @elseif($order->current_step == 7 || $order->status == 'Review')
                                    <span class="bg-yellow-500/10 text-yellow-500 px-3 py-1 rounded-full text-xs font-bold border border-yellow-500/20"><i class="fas fa-clock mr-1"></i> Waiting for Approval</span>
                                @elseif($order->status == 'Processing' || $order->status == 'Approved' || $order->status == 'In Progress' || $order->status == 'Assigned')
                                    <span class="bg-blue-500/10 text-blue-500 px-3 py-1 rounded-full text-xs font-bold border border-blue-500/20">In Progress</span>
                                @elseif($order->status == 'Rejected')
                                    <span class="bg-red-500/10 text-red-500 px-3 py-1 rounded-full text-xs font-bold border border-red-500/20">Rejected</span>
                                @else
                                    <span class="bg-yellow-500/10 text-yellow-500 px-3 py-1 rounded-full text-xs font-bold border border-yellow-500/20">Pending</span>
                                @endif
                            </td>
                            <td class="p-6 text-gray-500 text-xs">
                                {{ $order->created_at->diffForHumans() }}
                            </td>
                            <td class="p-6 text-right">
                                <a href="{{ url('admin/orders/' . $order->id) }}"
                                    class="inline-block px-4 py-2 rounded-lg text-xs font-bold transition {{ ($order->status == 'Pending' && (!$order->is_payment_verified || !$order->is_content_verified)) ? 'bg-brand-red text-white hover:bg-red-600 shadow-[0_0_10px_rgba(255,45,70,0.4)]' : 'bg-white/10 hover:bg-white/20 text-white' }}">
                                    {{ ($order->status == 'Pending') ? 'Review' : 'Manage' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="p-4 border-t border-white/5">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection