@if(isset($orders) && count($orders) > 0)
    @foreach($orders as $order)
        <tr class="border-b border-white/5 hover:bg-white/5 transition group">
            <td class="p-4">
                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                    class="order-checkbox rounded bg-white/10 border-white/20 text-brand-red focus:ring-0 cursor-pointer"
                    onchange="updateBatchBar()">
            </td>
            <td class="p-4 font-mono text-gray-400">{{ $order->order_id }}</td>
            <td class="p-4">
                <div class="font-bold text-white">{{ $order->customer_name }}</div>
                <div class="text-xs text-gray-500">{{ $order->customer_email }}</div>
            </td>
            <td class="p-4">
                <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-white/5 text-white">{{ $order->plan }}</span>
                <div class="mt-1 text-gray-400 font-mono">RM {{ number_format($order->total_amount) }}</div>
            </td>
            <td class="p-4">
                @if($order->status == 'Paid')
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-500 border border-green-500/20">
                        <i class="fas fa-check-circle text-[10px]"></i> Completed
                    </span>
                @elseif($order->status == 'Rejected')
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-500 border border-red-500/20">
                        <i class="fas fa-times-circle text-[10px]"></i> Cancelled
                    </span>
                @elseif($order->current_step == 7 || $order->status == 'Review')
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                        <i class="fas fa-clock text-[10px]"></i> Waiting for Approval
                    </span>
                @elseif($order->status == 'Processing' || $order->status == 'In Progress')
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-500/10 text-orange-500 border border-orange-500/20">
                        <i class="fas fa-spinner fa-spin text-[10px]"></i> Processing
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                        <i class="fas fa-clock text-[10px]"></i> Pending
                    </span>
                @endif
            </td>
            <td class="p-4 text-gray-500">{{ $order->created_at->format('M d, H:i') }}</td>
            <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                    <!-- Manage Link (Single Action Point) -->
                    <a href="{{ url('admin/orders/' . $order->id) }}"
                        class="px-3 py-1.5 rounded-lg bg-brand-gray/50 hover:bg-brand-gray text-white text-xs font-bold transition flex items-center gap-2 border border-white/10"
                        title="Manage Order">
                        Manage <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="7" class="p-8 text-center text-gray-500">
            <i class="fas fa-inbox text-2xl mb-2 opacity-50"></i>
            <p>No orders found.</p>
        </td>
    </tr>
@endif