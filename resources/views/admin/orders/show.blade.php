@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <a href="{{ url('admin/orders') }}"
            class="inline-flex items-center gap-2 text-gray-500 hover:text-white mb-6 transition text-sm">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/20 text-green-500 p-4 rounded-xl mb-6">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Brand & Header Info -->
        <div class="flex justify-between items-start mb-8">
            <div>
                 @if($order->brand)
                    <div class="mb-2">
                        @if($order->brand->logo)
                            <img src="{{ asset($order->brand->logo) }}" alt="{{ $order->brand->name }}" class="h-8 object-contain">
                        @else
                            <span class="px-3 py-1 rounded text-xs font-bold bg-gray-800 text-white border border-gray-700">{{ $order->brand->name }}</span>
                        @endif
                    </div>
                @endif
                <h1 class="text-3xl font-bold mb-2">Order #{{ $order->order_id }}</h1>
                <p class="text-gray-400">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                <div class="mt-2 text-sm text-gray-500">
                    Current Step: <span class="text-white font-bold">{{ $order->current_step }}/8</span>
                </div>
            </div>
            <div>
                @if($order->status == 'Completed')
                    <span class="bg-green-500 text-black px-4 py-2 rounded-lg text-sm font-bold shadow-[0_0_15px_rgba(34,197,94,0.4)]">Completed</span>
                @elseif($order->status == 'Rejected')
                    <span class="bg-brand-red text-white px-4 py-2 rounded-lg text-sm font-bold shadow-[0_0_15px_rgba(255,45,70,0.4)]">Rejected</span>
                @else
                    <span class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-[0_0_15px_rgba(59,130,246,0.4)]">{{ $order->status }}</span>
                @endif
            </div>
        </div>

        <!-- 8-Step Progress Stepper -->
        <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-xl mb-8 relative overflow-hidden">
            <!-- Line -->
            <div class="absolute top-[50%] left-10 right-10 h-1 bg-white/5 -translate-y-1/2 z-0 hidden md:block"></div>
            
            <div class="relative z-10 grid grid-cols-2 md:grid-cols-8 gap-2 text-center">
                @foreach([
                    1 => 'Placed',
                    2 => 'Pending Payment',
                    3 => 'Payment Verified',
                    4 => ($order->is_content_verified) ? 'Content Verified' : 'Content Pending',
                    5 => 'Content Review',
                    6 => 'In Progress',
                    7 => 'Report Ready',
                    8 => 'Completed'
                ] as $step => $label)
                    @php
                        // Determine if this step is active/completed
                        $isActive = $order->current_step >= $step;
                        
                        // Override for Verification Steps based on flags (Visual Sync)
                        if ($step == 3 && $order->is_payment_verified) $isActive = true;
                        if ($step == 5 && $order->is_content_verified) $isActive = true;
                        
                        if ($step == 5 && $order->is_content_verified) $isActive = true;
                        
                        // FIX: Ensure Step 4 is green if Content Verified (regardless of step count)
                        if ($step == 4 && $order->is_content_verified) $isActive = true;

                        // If approved (Processing), ensure up to 6 is active
                        if ($order->status == 'Processing' && $step <= 6) $isActive = true;
                    @endphp
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full border-4 flex items-center justify-center text-xs md:text-sm font-bold bg-brand-dark transition-all duration-300
                            {{ $isActive ? 'border-green-500 text-green-500 shadow-[0_0_10px_rgba(34,197,94,0.3)]' : 'border-gray-600 text-gray-500' }}">
                            @if($isActive && $step < $order->current_step) <i class="fas fa-check"></i> @else {{ $step }} @endif
                        </div>
                        <span class="text-[10px] md:text-xs font-bold leading-tight {{ $isActive ? 'text-green-500' : 'text-gray-500' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Details -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-xl">
                    <h3 class="text-lg font-bold mb-4 border-b border-white/5 pb-2">Customer Details</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <!-- ... (Keep existing customer details fields) ... -->
                        <div>
                            <span class="block text-gray-500 text-xs uppercase mb-1">Full Name</span>
                            <span class="text-white font-medium">{{ $order->customer_name }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 text-xs uppercase mb-1">Email Address</span>
                            <span class="text-white font-medium">{{ $order->customer_email }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 text-xs uppercase mb-1">Phone Number</span>
                            <span class="text-white font-medium">{{ $order->phone }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500 text-xs uppercase mb-1">Company Name</span>
                             <span class="text-white font-medium">{{ $order->company_name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="block text-gray-500 text-xs uppercase mb-1">Website URL</span>
                            <a href="{{ $order->website_url }}" target="_blank"
                                class="text-blue-400 hover:text-blue-300 underline truncate block">{{ $order->website_url }}</a>
                        </div>
                    </div>
                </div>

                <!-- Plan Details -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-xl">
                    <!-- ... (Keep existing plan details) ... -->
                     <h3 class="text-lg font-bold mb-4 border-b border-white/5 pb-2">Subscription & Strategy</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="p-4 bg-black/20 rounded-xl border border-white/5">
                            <span class="block text-gray-500 text-xs uppercase mb-2">Selected Plan</span>
                            <span class="text-brand-red font-extrabold text-xl">{{ $order->plan }}</span>
                            <span class="block text-gray-400 text-xs mt-1">RM
                                {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        <div class="p-4 bg-black/20 rounded-xl border border-white/5">
                            <span class="block text-gray-500 text-xs uppercase mb-2">Strategy Choice</span>
                            <span class="text-white font-bold text-lg">{{ $order->strategy ?? 'Standard' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Workflow Actions -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Staff Assignment -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-xl">
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-user-tag text-purple-500"></i> Staff Assignment
                    </h3>
                    @if(auth()->user()->role == 'admin')
                        <form action="{{ url('admin/orders/'.$order->id.'/assign') }}" method="POST">
                            @csrf
                            <div class="flex gap-2">
                                <select name="staff_id" 
                                    class="flex-1 bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white text-sm focus:border-brand-red focus:outline-none">
                                    <option value="">-- Select Staff --</option>
                                    @foreach($staffMembers as $staff)
                                        <option value="{{ $staff->id }}" {{ $order->staff_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" 
                                    class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-lg transition"
                                    title="Save Assignment">
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- Staff View -->
                        <div class="p-3 bg-black/20 rounded-lg border border-white/5 flex items-center justify-between">
                            <span class="text-gray-400 text-sm">Assigned To:</span>
                            <span class="text-white font-bold flex items-center gap-2">
                                <i class="fas fa-user-circle text-gray-500"></i> 
                                {{ $order->staff_id ? $order->staff->name : 'Unassigned' }}
                            </span>
                        </div>
                    @endif
                </div>
                
                <!-- Dual Verification & Decision System -->
                <div class="space-y-6 sticky top-6">
                    
                    <!-- Always show cards, handle logic internally -->
 
                        <!-- 3. Decision Logic (Moved Here) -->
                        <div class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-xl">
                            <h3 class="text-sm font-bold text-gray-300 mb-4">Order Status</h3>
                            
                            @if($order->status == 'Pending' || ($order->status == 'Assigned' && $order->current_step < 3))
                                <!-- Accept Order (Admin Only) -->
                                @if(auth()->user()->role == 'admin')
                                    @if($order->staff_id)
                                        <a href="{{ url('admin/orders/' . $order->id . '/approve') }}" 
                                           class="block w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-center transition shadow-[0_0_20px_rgba(37,99,235,0.4)] animate-pulse">
                                            <i class="fas fa-check-circle mr-2"></i> Approve Order
                                        </a>
                                        <p class="text-[10px] text-blue-400 text-center mt-2">Staff Assigned • Verifies Payment & Content</p>
                                    @else
                                        <button disabled class="block w-full py-2 rounded-lg bg-gray-700 text-gray-500 font-bold text-center cursor-not-allowed border border-white/5">
                                            <i class="fas fa-check-circle mr-2"></i> Approve Order
                                        </button>
                                        <p class="text-[10px] text-red-400 text-center mt-2"> <i class="fas fa-exclamation-triangle"></i> Assign Staff First</p>
                                    @endif
                                @endif

                                <button onclick="openRejectModal()" class="w-full mt-3 py-2 text-xs font-bold text-red-500 hover:text-red-400 transition">
                                    Reject Order
                                </button>
                            @elseif($order->status == 'Assigned' || $order->status == 'Processing' || $order->status == 'In Progress' || $order->status == 'Review')
                            
                                <!-- A. Assigned State (Step 3) -->
                                @if($order->status == 'Assigned' && $order->current_step >= 3)
                                    <!-- Admin View: Waiting -->
                                    <div class="text-center p-4 bg-green-500/10 border border-green-500/20 rounded-xl mb-4">
                                        <div class="text-green-500 font-bold text-lg mb-1"><i class="fas fa-check"></i> Order Ready for Staff</div>
                                        <p class="text-xs text-green-400">Waiting for Staff to start work.</p>
                                    </div>
                                    
                                    <!-- Staff View: Start Work -->
                                    @if(auth()->user()->role == 'staff' && auth()->id() == $order->staff_id)
                                        <a href="{{ url('admin/orders/' . $order->id . '/start-work') }}" 
                                        class="block w-full py-3 rounded-lg bg-green-600 hover:bg-green-700 text-white font-bold text-center transition shadow-lg animate-pulse mb-3">
                                            <i class="fas fa-play mr-2"></i> Start Work
                                        </a>
                                        <p class="text-[10px] text-gray-400 text-center mb-4">Click to begin working on this order (Step 6)</p>
                                    @endif
                                @endif

                                <!-- B. In Progress / Processing State (Step 6) -->
                                @if(($order->status == 'Processing' || $order->status == 'In Progress') && $order->current_step == 6)
                                     <div class="text-center mb-4 flex flex-col items-center justify-center">
                                        <div class="text-indigo-400 font-bold mb-2 animate-pulse flex items-center justify-center gap-2">
                                            <i class="fas fa-spinner fa-spin"></i> Step {{ $order->current_step }}/8
                                        </div>
                                        <p class="text-[10px] text-gray-500">Staff is working on the order.</p>
                                    </div>

                                    <!-- Staff Action: Submit Report & Complete -->
                                    @if(auth()->user()->role == 'staff' && auth()->id() == $order->staff_id)
                                        <form action="{{ url('admin/orders/' . $order->id . '/complete') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="block text-gray-400 text-xs mb-1">Upload Final Report (PDF/Doc/Zip)</label>
                                                <input type="file" name="report_file" required class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer bg-black/30 rounded-lg border border-white/10">
                                            </div>
                                            <button type="submit" 
                                            class="block w-full py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm text-center transition shadow-md hover:shadow-lg mb-2">
                                                <i class="fas fa-check-circle mr-2"></i> Upload & Complete Order
                                            </button>
                                        </form>
                                        <p class="text-[10px] text-gray-500 text-center mt-2">Upload the report to finish the order (Step 8)</p>
                                    @endif
                                @endif

                                <!-- C. Review State (Step 7) -->
                                @if(($order->status == 'Review' || $order->current_step == 7))
                                    <div class="text-center p-2 rounded-lg bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-sm font-bold mb-4">
                                        <i class="fas fa-check mr-2"></i> Submitted for Review
                                    </div>

                                    <!-- Admin Action: Mark Completed -->
                                    @if(auth()->user()->role == 'admin')
                                        <a href="{{ url('admin/completed/' . $order->id) }}" 
                                        class="block w-full py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold text-sm text-center transition shadow-md hover:shadow-lg mb-2">
                                            <i class="fas fa-flag-checkered mr-2"></i> Approve & Publish
                                        </a>
                                        <p class="text-[10px] text-gray-500 text-center mt-2">Finalizes order (Step 8)</p>
                                    @else
                                        <p class="text-[10px] text-gray-500 text-center">Waiting for Admin Approval</p>
                                    @endif
                                @endif

                            @elseif($order->status == 'Completed' || $order->status == 'Paid')
                                <div class="text-center p-4 bg-green-500/10 border border-green-500/20 rounded-xl">
                                    <div class="text-green-500 font-bold text-lg mb-1"><i class="fas fa-check-circle"></i> Order Finished</div>
                                    <p class="text-xs text-green-400">This order is complete.</p>
                                </div>
                            @elseif($order->status == 'Rejected')
                                <div class="text-center p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                                    <div class="text-red-500 font-bold mb-1"><i class="fas fa-times-circle"></i> Order Rejected</div>
                                    <p class="text-xs text-red-400">{{ $order->rejection_reason ?? 'No reason provided' }}</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- 1. Payment Verification Card -->
                        <div class="bg-brand-dark border {{ $order->is_payment_verified ? 'border-green-500/50' : 'border-white/10' }} rounded-2xl p-6 shadow-xl relative overflow-hidden">
                            @if($order->is_payment_verified)
                                <div class="absolute top-0 right-0 bg-green-500 text-black text-xs font-bold px-3 py-1 rounded-bl-lg">VERIFIED</div>
                            @endif
                            
                            <h3 class="text-sm font-bold text-gray-300 mb-4 flex items-center gap-2">
                                <i class="fas fa-file-invoice-dollar {{ $order->is_payment_verified ? 'text-green-500' : 'text-gray-400' }}"></i> Payment Verification
                            </h3>

                            <!-- Receipt Thumbnail (Placeholder/Real) -->
                            <div class="bg-black/30 rounded-lg p-4 mb-4 flex items-center justify-center border border-white/5 min-h-[100px]">
                                @if($order->receipt_path)
                                    <a href="{{ Storage::url($order->receipt_path) }}" target="_blank" class="block group relative">
                                        <img src="{{ Storage::url($order->receipt_path) }}" class="h-20 object-contain mx-auto" alt="Receipt">
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition rounded">
                                            <i class="fas fa-search-plus text-white"></i>
                                        </div>
                                    </a>
                                @else
                                    <div class="text-center text-gray-500 text-xs">
                                        <i class="fas fa-image text-2xl mb-2 opacity-50"></i><br>
                                        No Receipt Uploaded
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                @if(auth()->user()->role == 'admin')
                                    <form action="{{ url('admin/orders/'.$order->id.'/verify-payment') }}" method="GET" class="contents">
                                        <button type="submit" class="py-2 rounded bg-green-600 hover:bg-green-700 text-white text-xs font-bold transition flex items-center justify-center gap-1 {{ $order->is_payment_verified ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $order->is_payment_verified ? 'disabled' : '' }}>
                                            <i class="fas fa-check"></i> Verified
                                        </button>
                                    </form>
                                    <button onclick="openRejectModal('Payment Failed')" class="py-2 rounded border border-red-500 text-red-500 hover:bg-red-500 hover:text-white text-xs font-bold transition">
                                        <i class="fas fa-times"></i> Failed
                                    </button>
                                @else
                                    <div class="col-span-2 text-center text-xs text-gray-500 py-2 border border-white/5 rounded">
                                        Admin Action Required
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 2. Content Verification Card -->
                        <div class="bg-brand-dark border {{ $order->is_content_verified ? 'border-green-500/50' : 'border-white/10' }} rounded-2xl p-6 shadow-xl relative overflow-hidden">
                             @if($order->is_content_verified)
                                <div class="absolute top-0 right-0 bg-green-500 text-black text-xs font-bold px-3 py-1 rounded-bl-lg">ACCEPTED</div>
                            @endif

                             <h3 class="text-sm font-bold text-gray-300 mb-4 flex items-center gap-2">
                                <i class="fas fa-layer-group {{ $order->is_content_verified ? 'text-green-500' : 'text-gray-400' }}"></i> Content Verification
                            </h3>

                            <div class="bg-black/30 rounded-lg p-3 mb-4 border border-white/5 text-xs text-gray-400">
                                <p><strong class="text-gray-300">Plan:</strong> {{ $order->plan }}</p>
                                <p><strong class="text-gray-300">Strategy:</strong> {{ $order->strategy }}</p>
                                <p class="truncate"><strong class="text-gray-300">URL:</strong> {{ $order->website_url }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                 <form action="{{ url('admin/orders/'.$order->id.'/verify-content') }}" method="GET" class="contents">
                                    <button type="submit" class="py-2 rounded bg-green-600 hover:bg-green-700 text-white text-xs font-bold transition flex items-center justify-center gap-1 {{ $order->is_content_verified ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $order->is_content_verified ? 'disabled' : '' }}>
                                        <i class="fas fa-check"></i> Accepted
                                    </button>
                                </form>
                                <button onclick="openRejectModal('Content Inappropriate')" class="py-2 rounded border border-red-500 text-red-500 hover:bg-red-500 hover:text-white text-xs font-bold transition">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </div>

                        <!-- 3. Decision Logic -->
                        <!-- Order Status Card moved up -->

                    <!-- End Logic -->

                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal"
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-brand-dark border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
            <button onclick="closeRejectModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white">
                <i class="fas fa-times"></i>
            </button>

            <h3 class="text-xl font-bold text-white mb-4">Reject Order</h3>
            <p class="text-gray-400 text-sm mb-6">Please specify the reason for rejection. This will be logged.</p>

            <form action="{{ url('admin/orders/' . $order->id . '/reject-content') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-gray-400 text-sm mb-2">Rejection Reason</label>
                    <textarea name="reason" rows="4" required placeholder="Please explain why the order is being rejected..." 
                        class="w-full bg-black/30 border border-white/10 rounded-xl p-3 text-white focus:border-brand-red focus:outline-none placeholder-gray-600 text-sm"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()"
                        class="flex-1 py-3 rounded-lg border border-white/10 text-gray-400 hover:text-white hover:bg-white/5 transition font-bold">Cancel</button>
                    <button type="submit"
                        class="flex-1 py-3 rounded-lg bg-brand-red text-white font-bold hover:bg-red-600 transition shadow-lg shadow-red-900/50">Confirm
                        Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(reason = '') {
            document.getElementById('rejectModal').classList.remove('hidden');
            if(reason) {
                 const select = document.querySelector('select[name="reason"]');
                 if(select) {
                     // Try to select if exists in options, else select 'Other' or just match text logic
                     // Simple match:
                     select.value = reason; 
                     // If reason provided isn't in dropdown (e.g. from button click 'Payment Failed'), we might need to match it to 'Wrong Amount' or just let user pick.
                     // The buttons send "Payment Failed" and "Content Inappropriate".
                     // "Content Inappropriate" -> "Inappropriate Content" (close match)
                     // "Payment Failed" -> maybe "Wrong Amount"? 
                     if(reason === 'Payment Failed') select.value = 'Wrong Amount';
                     if(reason === 'Content Inappropriate') select.value = 'Inappropriate Content';
                 }
            }
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
@endsection