@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 flex-shrink-0">
            <div>
                <h1 class="text-3xl font-bold">Orders Management</h1>
                <p class="text-gray-500 text-sm mt-1">View and manage all customer orders.</p>
            </div>
            <div class="text-right">
                <span
                    class="bg-brand-gray px-4 py-2 rounded-full text-xs font-mono text-gray-400 border border-white/5">
                    {{ date('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-hidden flex flex-col">
            <!-- Order Table Container -->
            <div class="bg-brand-dark border border-white/10 rounded-2xl shadow-xl relative flex flex-col h-full overflow-hidden">
                
                <!-- Toolbar: Tabs & Filters -->
                <div class="p-6 border-b border-white/5 flex-shrink-0 space-y-4">
                    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
                         <!-- Tabs -->
                        <div class="flex bg-black/40 p-1 rounded-xl overflow-x-auto max-w-full">
                            <a href="{{ url('admin/orders') }}?status=Pending" class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? 'Pending') == 'Pending' ? 'bg-brand-red text-white shadow-lg' : 'text-gray-400 hover:text-white' }}">Pending</a>
                            <a href="{{ url('admin/orders') }}?status=Processing" class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? '') == 'Processing' ? 'bg-orange-500 text-white shadow-lg' : 'text-gray-400 hover:text-white' }}">Processing</a>
                            <a href="{{ url('admin/orders') }}?status=Completed" class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? '') == 'Completed' ? 'bg-green-500 text-white shadow-lg' : 'text-gray-400 hover:text-white' }}">Completed</a>
                            <a href="{{ url('admin/orders') }}?status=Cancelled" class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? '') == 'Cancelled' ? 'bg-red-900/50 text-red-200' : 'text-gray-400 hover:text-white' }}">Cancelled</a>
                            <a href="{{ url('admin/orders') }}?status=All" class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ ($statusTab ?? '') == 'All' ? 'bg-white/10 text-white' : 'text-gray-400 hover:text-white' }}">All Orders</a>
                        </div>
                        
                        <!-- Export Button -->
                        <a href="{{ url('admin/export/orders') }}" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition shadow-lg shadow-green-900/20 whitespace-nowrap">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                    
                    <!-- Filters -->
                    <form action="{{ url('admin/orders') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-black/20 p-4 rounded-xl border border-white/5">
                        <input type="hidden" name="status" value="{{ $statusTab ?? 'Pending' }}">
                        
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Customer/ID..." 
                                class="w-full bg-brand-dark border border-white/10 rounded-lg pl-9 pr-3 py-2 text-sm text-white focus:border-brand-red focus:outline-none">
                        </div>
                        
                        <!-- Date Range -->
                        <div class="flex items-center gap-2">
                             <input type="date" name="date_start" value="{{ request('date_start') }}" class="w-full bg-brand-dark border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-red focus:outline-none placeholder-gray-500">
                        </div>
                        
                         <!-- Amount Range -->
                         <div class="flex items-center gap-2">
                            <input type="number" name="min_amount" placeholder="Min RM" value="{{ request('min_amount') }}" class="w-1/2 bg-brand-dark border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-red focus:outline-none">
                            <input type="number" name="max_amount" placeholder="Max RM" value="{{ request('max_amount') }}" class="w-1/2 bg-brand-dark border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-red focus:outline-none">
                       </div>

                       <div class="flex gap-2">
                            <button type="submit" class="bg-brand-red hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition text-sm flex-1">
                                Filter
                           </button>
                           @if(request()->has('search') || request()->has('date_start') || request()->has('min_amount'))
                               <a href="{{ url('admin/orders') }}?status={{ $statusTab ?? 'Pending' }}" class="bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white px-4 py-2 rounded-lg text-sm transition flex items-center justify-center">
                                   <i class="fas fa-times"></i>
                               </a>
                           @endif
                       </div>
                    </form>
                </div>

                <!-- Batch Actions (Hidden by default) -->
                <form action="{{ url('admin/batch') }}" method="POST" id="batchForm" class="flex-1 flex flex-col overflow-hidden">
                    @csrf
                    <div id="batchActionBar" class="hidden mx-6 mt-4 bg-brand-red/10 border border-brand-red/20 p-3 rounded-xl flex items-center justify-between flex-shrink-0">
                        <div class="text-brand-red font-bold text-sm">
                            <span id="selectedCount">0</span> orders selected
                        </div>
                        <div class="flex gap-2">
                             <button type="submit" name="action" value="processing" class="text-xs bg-brand-red hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition font-bold">
                                Mark Processing
                            </button>
                            <button type="submit" name="action" value="completed" class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition font-bold">
                                Mark Completed
                            </button>
                            <button type="submit" name="action" value="cancelled" class="text-xs bg-gray-600 hover:bg-gray-700 text-white px-3 py-1.5 rounded-lg transition font-bold">
                                Mark Cancelled
                            </button>
                            <button type="submit" name="action" value="delete" class="text-xs bg-red-900 hover:bg-red-800 text-white px-3 py-1.5 rounded-lg transition font-bold ml-2 border border-red-700">
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Table (Scrollable) -->
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-6 pt-2">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-brand-dark/95 backdrop-blur z-10">
                                <tr class="text-gray-500 text-xs uppercase border-b border-white/5">
                                    <th class="p-4 w-10">
                                        <input type="checkbox" id="selectAll" class="rounded bg-white/10 border-white/20 text-brand-red focus:ring-0 cursor-pointer">
                                    </th>
                                    <th class="p-4 font-bold">Order ID</th>
                                    <th class="p-4 font-bold">Customer</th>
                                    <th class="p-4 font-bold">Plan / Strategy</th>
                                    <th class="p-4 font-bold">Amount</th>
                                    <th class="p-4 font-bold">Status</th>
                                    <th class="p-4 font-bold">Date</th>
                                    <th class="p-4 font-bold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @if(isset($orders) && count($orders) > 0)
                                    @foreach($orders as $order)
                                    <tr class="border-b border-white/5 hover:bg-white/5 transition group">
                                        <td class="p-4">
                                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded bg-white/10 border-white/20 text-brand-red focus:ring-0 cursor-pointer" onchange="updateBatchBar()">
                                        </td>
                                        <td class="p-4 font-mono text-gray-400">{{ $order->order_id }}</td>
                                        <td class="p-4">
                                            <div class="font-bold text-white">{{ $order->customer_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $order->customer_email }}</div>
                                            <div class="text-xs text-gray-500">{{ $order->company_name }}</div>
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-white/5 text-white block w-fit mb-1">{{ $order->plan }}</span>
                                            <div class="text-xs text-gray-500 truncate max-w-[150px]" title="{{ $order->strategy }}">{{ $order->strategy }}</div>
                                        </td>
                                        <td class="p-4 font-mono text-gray-300">
                                            RM {{ number_format($order->total_amount) }}
                                        </td>
                                        <td class="p-4">
                                            @if($order->status == 'Paid')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-500 border border-green-500/20">
                                                    <i class="fas fa-check-circle text-[10px]"></i> Completed
                                                </span>
                                            @elseif($order->status == 'Rejected')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-500 border border-red-500/20">
                                                    <i class="fas fa-times-circle text-[10px]"></i> Cancelled
                                                </span>
                                            @elseif($order->status == 'Processing')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-500/10 text-orange-500 border border-orange-500/20">
                                                    <i class="fas fa-spinner fa-spin text-[10px]"></i> Processing
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                                                    <i class="fas fa-clock text-[10px]"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-gray-500 whitespace-nowrap">{{ $order->created_at->format('M d, H:i') }}</td>
                                        <td class="p-4 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                                                <!-- View Invoice (PDF) -->
                                                <a href="{{ url('admin/invoice/'.$order->id.'/download') }}" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white flex items-center justify-center transition" title="Download Invoice">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                
                                                <!-- Edit -->
                                                <a href="{{ url('admin/edit/'.$order->id) }}" class="w-8 h-8 rounded-lg bg-brand-gray/50 hover:bg-brand-gray text-gray-300 hover:text-white flex items-center justify-center transition" title="Edit Order">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @if($order->status == 'Pending')
                                                    <!-- Mark Pending -> Processing -->
                                                    <a href="{{ url('admin/paid/'.$order->id) }}" class="w-8 h-8 rounded-lg bg-green-500/10 hover:bg-green-500 text-green-500 hover:text-white flex items-center justify-center transition border border-green-500/20" title="Accept (Move to Processing)">
                                                        <i class="fas fa-check-circle"></i>
                                                    </a>
                                                    
                                                    <!-- Reject -->
                                                    <a href="{{ url('admin/reject/'.$order->id) }}" onclick="return confirm('Are you sure you want to reject this order?')" class="w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white flex items-center justify-center transition border border-red-500/20" title="Reject Order">
                                                        <i class="fas fa-times-circle"></i>
                                                    </a>
                                                @elseif($order->status == 'Processing')
                                                     <!-- Mark Processing -> Completed -->
                                                     <a href="{{ url('admin/completed/'.$order->id) }}" class="w-8 h-8 rounded-lg bg-blue-500/10 hover:bg-blue-500 text-blue-500 hover:text-white flex items-center justify-center transition border border-blue-500/20" title="Mark Completed">
                                                        <i class="fas fa-rocket"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="p-16 text-center text-gray-500">
                                            <div class="bg-white/5 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-search text-2xl opacity-50"></i>
                                            </div>
                                            <h3 class="text-lg font-bold text-gray-400">No orders found</h3>
                                            <p class="text-sm mt-1">Try adjusting your filters or tabs.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        <div class="mt-6 px-4">
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
            // Checkbox & Batch Logic
            window.updateBatchBar = function() {
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
