@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <a href="{{ url('admin/orders') }}"
            class="inline-flex items-center gap-2 text-slate-500 hover:text-white mb-8 transition-colors duration-200 text-sm font-medium">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-5 rounded-xl mb-6 text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-5 rounded-xl mb-6 text-sm font-medium">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-amber-500/10 border border-amber-500/20 text-amber-300 p-5 rounded-xl mb-6">
                <p class="font-semibold mb-2 text-sm"><i class="fas fa-exclamation-triangle mr-2"></i> Please fix the following:</p>
                <ul class="list-disc list-inside text-sm space-y-1 text-amber-300/80">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header -->
        <div class="flex justify-between items-start mb-10">
            <div>
                @if($order->brand)
                    <div class="mb-3">
                        @if($order->brand->logo)
                            <img src="{{ asset($order->brand->logo) }}" alt="{{ $order->brand->name }}" class="h-8 object-contain">
                        @else
                            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-700 text-white">{{ $order->brand->name }}</span>
                        @endif
                    </div>
                @endif
                <h1 class="text-3xl font-bold text-white tracking-tight mb-1">Order #{{ $order->order_id }}</h1>
                <p class="text-slate-500 text-sm">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                <div class="mt-3 text-sm text-slate-500">
                    Current Step: <span class="text-white font-semibold">{{ $order->current_step }}/8</span>
                </div>
            </div>
            <div>
                @if($order->status == 'Completed')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">Completed</span>
                @elseif($order->status == 'Rejected')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-red-500/15 text-red-400 border border-red-500/20">Rejected</span>
                @elseif($order->status == 'Review')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/20">Under Review</span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-blue-500/15 text-blue-400 border border-blue-500/20">{{ $order->status }}</span>
                @endif
            </div>
        </div>

        <!-- 8-Step Progress -->
        <div class="exec-card p-7 mb-10 relative overflow-hidden">
            <div class="absolute top-[50%] left-10 right-10 h-px bg-border-subtle -translate-y-1/2 z-0 hidden md:block"></div>
            <div class="relative z-10 grid grid-cols-2 md:grid-cols-8 gap-3 text-center">
                @foreach([
                    1 => 'Placed',
                    2 => 'Pending Payment',
                    3 => 'Payment Verified',
                    4 => ($order->is_content_verified) ? 'Content Verified' : 'Content Pending',
                    5 => 'Content Review',
                    6 => 'In Progress',
                    7 => 'Pending Approval',
                    8 => 'Completed'
                ] as $step => $label)
                    @php
                        $isActive = $order->current_step >= $step;
                        if ($step == 3 && $order->is_payment_verified) $isActive = true;
                        if ($step == 5 && $order->is_content_verified) $isActive = true;
                        if ($step == 4 && $order->is_content_verified) $isActive = true;
                        if ($order->status == 'Processing' && $step <= 6) $isActive = true;
                    @endphp
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-9 h-9 md:w-10 md:h-10 rounded-full border-2 flex items-center justify-center text-xs font-semibold transition-all duration-300
                            {{ $isActive ? 'border-emerald-400 text-emerald-400 bg-emerald-500/10' : 'border-slate-600 text-slate-500 bg-surface' }}">
                            @if($isActive && $step < $order->current_step) <i class="fas fa-check text-xs"></i> @else {{ $step }} @endif
                        </div>
                        <span class="text-[10px] md:text-xs font-medium leading-tight {{ $isActive ? 'text-emerald-400' : 'text-slate-500' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
            <!-- Staff Assignment -->
            <div class="exec-card p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-5 flex items-center gap-2">
                    <i class="fas fa-user-tag text-purple-400"></i> Staff Assignment
                </h3>
                @if(auth()->user()->role == 'admin')
                    <form action="{{ url('admin/orders/'.$order->id.'/assign') }}" method="POST">
                        @csrf
                        <div class="flex gap-2">
                            <select name="staff_id"
                                class="flex-1 bg-slate-800/50 border border-border-subtle rounded-xl px-4 py-2.5 text-white text-sm focus:border-slate-500 focus:outline-none transition">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ $order->staff_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                class="bg-slate-700/50 hover:bg-slate-700 text-white p-2.5 rounded-xl transition" title="Save">
                                <i class="fas fa-save"></i>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-slate-800/30 rounded-xl border border-border-subtle flex items-center justify-between">
                        <span class="text-slate-400 text-sm">Assigned To:</span>
                        <span class="text-white font-semibold text-sm">{{ $order->staff_id ? $order->staff->name : 'Unassigned' }}</span>
                    </div>
                @endif
            </div>

            <!-- Order Status -->
            <div class="exec-card p-6">
                <h3 class="text-sm font-semibold text-slate-300 mb-5">Order Status</h3>
                @if($order->status == 'Pending' || ($order->status == 'Assigned' && $order->current_step < 3))
                    @if(auth()->user()->role == 'admin')
                        @if($order->staff_id)
                            <a href="{{ url('admin/orders/' . $order->id . '/approve') }}"
                                class="block w-full py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white font-semibold text-center transition-all duration-200 text-sm shadow-lg shadow-red-900/20">
                                <i class="fas fa-check-circle mr-2"></i> Approve Order
                            </a>
                            <p class="text-[11px] text-emerald-400 text-center mt-2 font-medium">Staff Assigned</p>
                        @else
                            <button disabled class="block w-full py-2.5 rounded-xl bg-slate-700 text-slate-500 font-semibold text-center cursor-not-allowed text-sm border border-border-subtle">
                                <i class="fas fa-check-circle mr-2"></i> Approve Order
                            </button>
                            <p class="text-[11px] text-red-400 text-center mt-2"><i class="fas fa-exclamation-triangle"></i> Assign Staff First</p>
                        @endif
                    @endif
                    <button onclick="openRejectModal()" class="w-full mt-3 py-2 text-xs font-semibold text-red-400 hover:text-red-300 transition">Reject Order</button>
                @elseif($order->status == 'Assigned' || $order->status == 'Processing' || $order->status == 'In Progress' || $order->status == 'Review')
                    @if($order->status == 'Assigned' && $order->current_step >= 3)
                        <div class="text-center p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm font-semibold mb-3"><i class="fas fa-check"></i> Ready for Staff</div>
                        @if(auth()->user()->role == 'staff' && auth()->id() == $order->staff_id)
                            <a href="{{ url('admin/orders/' . $order->id . '/start-work') }}" class="block w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-center text-sm transition"><i class="fas fa-play mr-2"></i> Start Work</a>
                        @endif
                    @endif
                    @if(($order->status == 'Processing' || $order->status == 'In Progress') && $order->current_step == 6)
                        <div class="text-center py-2.5 text-blue-400 text-sm font-semibold mb-3"><i class="fas fa-spinner fa-spin mr-1"></i> Step 6/8</div>
                        @if(auth()->user()->role == 'staff' && auth()->id() == $order->staff_id)
                            <form action="{{ url('admin/orders/' . $order->id . '/complete') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                @csrf
                                <label class="block text-slate-400 text-xs mb-1.5 font-medium">Upload report (PDF, DOC, DOCX, ZIP — max 10MB)</label>
                                <input type="file" name="report_file" accept=".pdf,.doc,.docx,.zip"
                                    class="w-full text-xs text-slate-400 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white mb-2 bg-slate-800/50 rounded-xl border border-border-subtle">
                                @error('report_file')
                                    <p class="text-amber-400 text-xs mb-2">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="block w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs text-center transition">
                                    <i class="fas fa-check-circle mr-2"></i> Submit for Review
                                </button>
                            </form>
                        @endif
                    @endif
                    @if($order->status == 'Review' || $order->current_step == 7)
                        <div class="text-center p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm font-semibold mb-3"><i class="fas fa-hourglass-half mr-2"></i> Pending Approval</div>
                        {{-- Admin Report Preview: prominent so admin can check before Approve & Finish --}}
                        @if(!empty($order->report_file) && \Storage::disk('public')->exists($order->report_file))
                            <div class="mb-4 p-3 rounded-xl bg-slate-800/50 border border-emerald-500/20">
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-2">Staff submitted report</p>
                                <a href="{{ \Storage::url($order->report_file) }}" target="_blank" rel="noopener"
                                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 font-semibold text-sm border border-emerald-500/30 transition">
                                    <i class="fas fa-file-pdf"></i> Preview / Download Report
                                </a>
                                <p class="text-[11px] text-slate-500 mt-1.5 truncate" title="{{ $order->report_file }}">{{ basename($order->report_file) }}</p>
                            </div>
                        @elseif(!empty($order->report_file))
                            <div class="text-center text-xs text-amber-400 mb-3">Report path exists but file not accessible.</div>
                        @else
                            <div class="text-center text-xs text-slate-500 mb-3">No report file uploaded.</div>
                        @endif
                        @if(auth()->user()->role == 'admin')
                            <form action="{{ url('admin/orders/' . $order->id . '/admin-approve') }}" method="POST">
                                @csrf
                                <button type="submit" class="block w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs text-center transition shadow-lg shadow-emerald-900/20">
                                    <i class="fas fa-flag-checkered mr-2"></i> Approve & Finish
                                </button>
                            </form>
                        @endif
                    @endif
                @elseif($order->status == 'Completed' || $order->status == 'Paid')
                    <div class="text-center p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm font-semibold"><i class="fas fa-check-circle mr-1"></i> Order Finished</div>
                @elseif($order->status == 'Rejected')
                    <div class="text-center p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm"><i class="fas fa-times-circle mr-1"></i> {{ Str::limit($order->rejection_reason ?? 'Rejected', 40) }}</div>
                @endif
            </div>

            <!-- Payment Verification -->
            <div class="exec-card p-6 relative overflow-hidden {{ $order->is_payment_verified ? 'border-emerald-500/30' : '' }}">
                @if($order->is_payment_verified)
                    <div class="absolute top-0 right-0 bg-emerald-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">VERIFIED</div>
                @endif
                <h3 class="text-sm font-semibold text-slate-300 mb-5 flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar {{ $order->is_payment_verified ? 'text-emerald-400' : 'text-slate-500' }}"></i> Payment
                </h3>
                <div class="bg-slate-800/30 rounded-xl p-4 mb-4 flex items-center justify-center border border-border-subtle min-h-[80px]">
                    @if($order->receipt_path && \Storage::disk('public')->exists($order->receipt_path))
                        <a href="{{ Storage::url($order->receipt_path) }}" target="_blank" class="block">
                            <img src="{{ Storage::url($order->receipt_path) }}" class="h-16 object-contain mx-auto rounded" alt="Receipt">
                        </a>
                    @else
                        <div class="text-center text-slate-600 text-xs"><i class="fas fa-image text-xl opacity-40"></i><br>No Receipt</div>
                    @endif
                </div>
                @if(auth()->user()->role == 'admin')
                    <div class="grid grid-cols-2 gap-2">
                        <form action="{{ url('admin/orders/'.$order->id.'/verify-payment') }}" method="GET" class="contents">
                            <button type="submit" class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition {{ $order->is_payment_verified ? 'opacity-40 cursor-not-allowed' : '' }}" {{ $order->is_payment_verified ? 'disabled' : '' }}><i class="fas fa-check mr-1"></i> Verified</button>
                        </form>
                        <button onclick="openRejectModal('Payment Failed')" class="py-2.5 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white text-xs font-semibold transition"><i class="fas fa-times mr-1"></i> Failed</button>
                    </div>
                @else
                    <div class="text-center text-xs text-slate-500 py-2.5 border border-border-subtle rounded-xl">Admin Action Required</div>
                @endif
            </div>

            <!-- Content Verification -->
            <div class="exec-card p-6 relative overflow-hidden {{ $order->is_content_verified ? 'border-emerald-500/30' : '' }}">
                @if($order->is_content_verified)
                    <div class="absolute top-0 right-0 bg-emerald-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">ACCEPTED</div>
                @endif
                <h3 class="text-sm font-semibold text-slate-300 mb-5 flex items-center gap-2">
                    <i class="fas fa-layer-group {{ $order->is_content_verified ? 'text-emerald-400' : 'text-slate-500' }}"></i> Content
                </h3>
                <div class="bg-slate-800/30 rounded-xl p-4 mb-4 border border-border-subtle text-xs text-slate-400 space-y-1.5">
                    <p><strong class="text-slate-300">Plan:</strong> {{ $order->plan }}</p>
                    <p><strong class="text-slate-300">Strategy:</strong> {{ $order->strategy }}</p>
                    <p class="truncate"><strong class="text-slate-300">URL:</strong> {{ $order->website_url ?? '—' }}</p>
                </div>
                @if(auth()->user()->role == 'admin')
                    <div class="grid grid-cols-2 gap-2">
                        <form action="{{ url('admin/orders/'.$order->id.'/verify-content') }}" method="GET" class="contents">
                            <button type="submit" class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition {{ $order->is_content_verified ? 'opacity-40 cursor-not-allowed' : '' }}" {{ $order->is_content_verified ? 'disabled' : '' }}><i class="fas fa-check mr-1"></i> Accepted</button>
                        </form>
                        <button onclick="openRejectModal('Content Inappropriate')" class="py-2.5 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white text-xs font-semibold transition"><i class="fas fa-times mr-1"></i> Reject</button>
                    </div>
                @else
                    <div class="text-center text-xs text-slate-500 py-2.5 border border-border-subtle rounded-xl">Admin Action Required</div>
                @endif
            </div>
        </div>

        <!-- Customer & Plan Details -->
        <div class="space-y-8">
            <div class="exec-card p-7">
                <h3 class="text-lg font-semibold text-white mb-5 pb-4 border-b border-border-subtle">Customer Details</h3>
                <div class="grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <span class="block text-slate-500 text-xs uppercase font-medium mb-1.5 tracking-wider">Full Name</span>
                        <span class="text-white font-medium">{{ $order->customer_name }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase font-medium mb-1.5 tracking-wider">Email Address</span>
                        <span class="text-white font-medium">{{ $order->customer_email }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase font-medium mb-1.5 tracking-wider">Phone Number</span>
                        <span class="text-white font-medium">{{ $order->phone }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase font-medium mb-1.5 tracking-wider">Company Name</span>
                        <span class="text-white font-medium">{{ $order->company_name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="block text-slate-500 text-xs uppercase font-medium mb-1.5 tracking-wider">Website URL</span>
                        <a href="{{ $order->website_url }}" target="_blank"
                            class="text-blue-400 hover:text-blue-300 underline truncate block text-sm">{{ $order->website_url }}</a>
                    </div>
                </div>
            </div>

            <div class="exec-card p-7">
                <h3 class="text-lg font-semibold text-white mb-5 pb-4 border-b border-border-subtle">Subscription & Strategy</h3>
                <div class="grid grid-cols-2 gap-6 text-sm">
                    <div class="p-5 bg-slate-800/30 rounded-xl border border-border-subtle">
                        <span class="block text-slate-500 text-xs uppercase font-medium mb-2 tracking-wider">Selected Plan</span>
                        <span class="text-brand-red font-bold text-xl">{{ $order->plan }}</span>
                        <span class="block text-slate-400 text-sm mt-1">RM {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="p-5 bg-slate-800/30 rounded-xl border border-border-subtle">
                        <span class="block text-slate-500 text-xs uppercase font-medium mb-2 tracking-wider">Strategy</span>
                        <span class="text-white font-semibold text-lg">{{ $order->strategy ?? 'Standard' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Reason Modal (for "Failed" payment or "Reject" content) -->
    <div id="rejectModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="exec-card w-full max-w-md p-7 relative">
            <button type="button" onclick="closeRejectModal()" class="absolute top-5 right-5 text-slate-500 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-xl font-bold text-white mb-1">Reject Order</h3>
            <p class="text-slate-400 text-sm mb-4">Select a reason or enter your own. This will be saved to the order and included in the customer email.</p>
            <form action="{{ url('admin/orders/' . $order->id . '/reject-content') }}" method="POST" id="rejectForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Quick reason</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setRejectReason('Invalid Receipt')" class="reject-preset px-3 py-1.5 rounded-lg border border-border-subtle text-slate-400 hover:border-slate-500 hover:text-white text-xs font-medium transition">Invalid Receipt</button>
                        <button type="button" onclick="setRejectReason('Wrong Amount')" class="reject-preset px-3 py-1.5 rounded-lg border border-border-subtle text-slate-400 hover:border-slate-500 hover:text-white text-xs font-medium transition">Wrong Amount</button>
                        <button type="button" onclick="setRejectReason('Payment Not Received')" class="reject-preset px-3 py-1.5 rounded-lg border border-border-subtle text-slate-400 hover:border-slate-500 hover:text-white text-xs font-medium transition">Payment Not Received</button>
                        <button type="button" onclick="setRejectReason('Content Policy Violation')" class="reject-preset px-3 py-1.5 rounded-lg border border-border-subtle text-slate-400 hover:border-slate-500 hover:text-white text-xs font-medium transition">Content Policy Violation</button>
                        <button type="button" onclick="setRejectReason('Content Inappropriate')" class="reject-preset px-3 py-1.5 rounded-lg border border-border-subtle text-slate-400 hover:border-slate-500 hover:text-white text-xs font-medium transition">Content Inappropriate</button>
                        <button type="button" onclick="setRejectReason('')" class="reject-preset px-3 py-1.5 rounded-lg border border-border-subtle text-slate-400 hover:border-slate-500 hover:text-white text-xs font-medium transition">Other</button>
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-slate-400 text-sm mb-2 font-medium">Rejection reason <span class="text-slate-500">(saved to order &amp; email)</span></label>
                    <textarea name="reason" id="rejectReasonInput" rows="3" required placeholder="Type or select a reason above..."
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl p-4 text-white focus:border-slate-500 focus:outline-none placeholder-slate-600 text-sm transition"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()"
                        class="flex-1 py-3 rounded-xl border border-border-subtle text-slate-400 hover:text-white hover:bg-white/5 transition font-semibold text-sm">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-brand-red text-white font-semibold hover:bg-brand-red-hover transition shadow-lg shadow-red-900/20 text-sm">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setRejectReason(text) {
            var el = document.getElementById('rejectReasonInput');
            if (el) el.value = text;
        }
        function openRejectModal(preset) {
            document.getElementById('rejectModal').classList.remove('hidden');
            var el = document.getElementById('rejectReasonInput');
            if (!el) return;
            if (preset === 'Payment Failed') el.value = 'Payment verification failed (e.g. wrong amount, unclear receipt, or payment not received).';
            else if (preset === 'Content Inappropriate') el.value = 'Content does not meet our guidelines.';
            else if (preset) el.value = preset;
            else el.value = '';
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
@endsection
