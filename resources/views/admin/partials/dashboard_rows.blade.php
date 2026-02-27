@if(isset($orders) && count($orders) > 0)
    @foreach($orders as $order)
        <tr class="border-b border-border-subtle hover:bg-white/[0.02] transition-colors duration-150 group">
            <td class="px-5 py-4">
                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                    class="order-checkbox rounded bg-slate-700 border-slate-600 text-brand-red focus:ring-0 cursor-pointer"
                    onchange="updateBatchBar()">
            </td>
            <td class="px-5 py-4 text-slate-400 font-medium">{{ $order->order_id }}</td>
            <td class="px-5 py-4">
                <div class="font-medium text-white">{{ $order->customer_name }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ $order->customer_email }}</div>
            </td>
            <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-700/50 text-slate-200">{{ $order->plan }}</span>
                <div class="mt-1.5 text-slate-400 text-sm">RM {{ number_format($order->total_amount) }}</div>
            </td>
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
                        <i class="fas fa-clock text-[9px]"></i> Pending Payment
                    </span>
                @endif
            </td>
            <td class="px-5 py-4 text-slate-500 text-sm">{{ $order->created_at->format('M d, H:i') }}</td>
            <td class="px-5 py-4 text-right">
                <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    @php
                        $isReviewStage = $order->current_step == 7 || $order->status == 'Review';
                        $isCustomerPending = $order->status == 'Pending';
                        $buttonLabel = $isReviewStage ? 'Review Work' : ($isCustomerPending ? 'Review' : 'Manage');
                    @endphp
                    <a href="{{ url('admin/orders/' . $order->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200
                        {{ $isReviewStage ? 'bg-amber-600 hover:bg-amber-700 text-white' : ($isCustomerPending ? 'bg-brand-red hover:bg-brand-red-hover text-white' : 'bg-slate-700/50 hover:bg-slate-700 text-slate-200') }}">
                        {{ $buttonLabel }} <i class="fas fa-arrow-right text-[9px]"></i>
                    </a>
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="7" class="px-5 py-16 text-center text-slate-500">
            <i class="fas fa-inbox text-3xl mb-3 opacity-30 block"></i>
            <p class="text-sm">No orders found.</p>
        </td>
    </tr>
@endif
