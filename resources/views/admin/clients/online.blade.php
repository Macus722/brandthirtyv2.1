@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <a href="{{ url('admin/clients') }}" class="text-slate-500 hover:text-white transition">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Online Sales</h1>
                </div>
                <p class="text-slate-500 text-sm ml-7">All customer orders from the website.</p>
            </div>

            <form action="{{ url('admin/clients/online') }}" method="GET" class="relative w-full sm:w-72">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..."
                    class="w-full bg-slate-800/50 border border-border-subtle rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none transition">
            </form>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ url('admin/clients/online') }}"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200
               {{ !request('status') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'bg-slate-800/50 text-slate-400 hover:text-white hover:bg-white/5 border border-border-subtle' }}">
                All Orders
            </a>
            @foreach(['Pending', 'Processing', 'Completed', 'Rejected'] as $s)
                <a href="{{ url('admin/clients/online?status=' . $s) }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200
                   {{ request('status') === $s ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'bg-slate-800/50 text-slate-400 hover:text-white hover:bg-white/5 border border-border-subtle' }}">
                    {{ $s }}
                </a>
            @endforeach
        </div>

        {{-- Orders Table --}}
        <div class="exec-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border-subtle text-left">
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Order ID</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Customer</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Company</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Plan</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Amount</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Status</th>
                            <th class="px-6 py-4 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $statusColors = [
                                    'Pending'    => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                                    'Processing' => 'bg-blue-500/15 text-blue-400 border-blue-500/20',
                                    'In Progress'=> 'bg-blue-500/15 text-blue-400 border-blue-500/20',
                                    'Assigned'   => 'bg-cyan-500/15 text-cyan-400 border-cyan-500/20',
                                    'Review'     => 'bg-purple-500/15 text-purple-400 border-purple-500/20',
                                    'Completed'  => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                                    'Paid'       => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                                    'Rejected'   => 'bg-red-500/15 text-red-400 border-red-500/20',
                                ];

                                $planColors = [
                                    'authority' => 'bg-purple-500/15 text-purple-400',
                                    'ultimate'  => 'bg-amber-500/15 text-amber-400',
                                    'starter'   => 'bg-blue-500/15 text-blue-400',
                                ];
                            @endphp
                            <tr class="border-b border-border-subtle/50 hover:bg-white/[0.04] transition cursor-pointer"
                                onclick="openOrderModal({{ $order->id }})">
                                <td class="px-6 py-4">
                                    <span class="text-white font-mono text-xs bg-slate-800/50 px-2.5 py-1 rounded-lg">{{ $order->order_id }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white font-medium">{{ $order->customer_name }}</div>
                                    <div class="text-slate-500 text-xs mt-0.5">{{ $order->customer_email }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-300">{{ $order->company_name ?: '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg capitalize {{ $planColors[$order->plan] ?? 'bg-slate-500/15 text-slate-400' }}">
                                        {{ ucfirst($order->plan) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-white font-semibold">RM {{ number_format($order->total_amount, 0) }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $statusColors[$order->status] ?? 'bg-slate-500/15 text-slate-400' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-400 text-xs">{{ $order->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <i class="fas fa-inbox text-3xl text-slate-600 mb-3"></i>
                                    <p class="text-slate-500 text-sm">No orders found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="mt-6 flex justify-center">
                <div class="inline-flex items-center gap-1">
                    @if($orders->onFirstPage())
                        <span class="px-3 py-2 rounded-lg text-slate-600 text-sm cursor-not-allowed"><i class="fas fa-chevron-left text-xs"></i></span>
                    @else
                        <a href="{{ $orders->previousPageUrl() }}" class="px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-sm transition"><i class="fas fa-chevron-left text-xs"></i></a>
                    @endif

                    @foreach($orders->getUrlRange(max(1, $orders->currentPage()-2), min($orders->lastPage(), $orders->currentPage()+2)) as $page => $url)
                        <a href="{{ $url }}"
                           class="px-3.5 py-2 rounded-lg text-sm font-medium transition
                           {{ $page == $orders->currentPage() ? 'bg-brand-red text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            {{ $page }}
                        </a>
                    @endforeach

                    @if($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}" class="px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-sm transition"><i class="fas fa-chevron-right text-xs"></i></a>
                    @else
                        <span class="px-3 py-2 rounded-lg text-slate-600 text-sm cursor-not-allowed"><i class="fas fa-chevron-right text-xs"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- ORDER DETAIL MODALS — Rendered OUTSIDE the table              --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @foreach($orders as $order)
        @php
            $statusColors = [
                'Pending'    => 'bg-amber-500/15 text-amber-400 border border-amber-500/20',
                'Processing' => 'bg-blue-500/15 text-blue-400 border border-blue-500/20',
                'In Progress'=> 'bg-blue-500/15 text-blue-400 border border-blue-500/20',
                'Assigned'   => 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/20',
                'Review'     => 'bg-purple-500/15 text-purple-400 border border-purple-500/20',
                'Completed'  => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20',
                'Paid'       => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20',
                'Rejected'   => 'bg-red-500/15 text-red-400 border border-red-500/20',
            ];

            $planColors = [
                'authority' => 'bg-purple-500/15 text-purple-400 border border-purple-500/20',
                'ultimate'  => 'bg-amber-500/15 text-amber-400 border border-amber-500/20',
                'starter'   => 'bg-blue-500/15 text-blue-400 border border-blue-500/20',
            ];

            $planIcon = [
                'authority' => 'fas fa-crown text-purple-400',
                'ultimate'  => 'fas fa-gem text-amber-400',
                'starter'   => 'fas fa-rocket text-blue-400',
            ];

            // Workflow steps
            $steps = [
                1 => 'Order Placed',
                2 => 'Pending Payment',
                3 => 'Payment Verified',
                4 => 'Content Pending',
                5 => 'Content Review',
                6 => 'In Progress',
                7 => 'Report Uploaded',
                8 => 'Completed',
            ];
            $currentStep = $order->current_step ?? 1;
        @endphp

        {{-- Full-screen overlay --}}
        <div id="order-modal-{{ $order->id }}"
             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
             onclick="closeOrderModal({{ $order->id }})">

            {{-- Modal Container --}}
            <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-white/10 shadow-2xl bg-slate-900"
                 onclick="event.stopPropagation()"
                 style="scrollbar-width: thin; scrollbar-color: rgba(100,116,139,0.3) transparent;">

                {{-- ── Modal Header ── --}}
                <div class="relative p-8 pb-6">
                    {{-- Close Button --}}
                    <button onclick="closeOrderModal({{ $order->id }})"
                        class="absolute top-6 right-6 w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>

                    {{-- Order Title --}}
                    <div class="flex items-center gap-4 pr-14">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 border border-white/10 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shopping-cart text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-bold text-white tracking-tight">{{ $order->company_name ?: $order->customer_name }}</h2>
                            </div>
                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $statusColors[$order->status] ?? 'bg-slate-500/15 text-slate-400' }}">{{ $order->status }}</span>
                                <span class="font-mono text-xs text-slate-500 bg-slate-800/50 px-2 py-0.5 rounded">{{ $order->order_id }}</span>
                                <span class="text-xs text-slate-500">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Top Stats Grid (4 columns) ── --}}
                <div class="px-8 pb-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Plan</div>
                            <div class="flex items-center justify-center gap-2">
                                <i class="{{ $planIcon[$order->plan] ?? 'fas fa-box text-slate-400' }}"></i>
                                <span class="text-lg font-bold text-white capitalize">{{ ucfirst($order->plan) }}</span>
                            </div>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Total Amount</div>
                            <div class="text-xl font-bold text-emerald-400">RM {{ number_format($order->total_amount, 0) }}</div>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Strategy</div>
                            <div class="text-base font-bold text-white capitalize">{{ $order->strategy ?: '—' }}</div>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-5 border border-white/5 text-center">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold mb-2">Distribution</div>
                            <div class="text-base font-bold text-white">{{ $order->distribution_reach ?: '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- ── Order Progress / Workflow ── --}}
                <div class="px-8 pb-6">
                    <div class="bg-slate-800/40 rounded-2xl p-6 border border-white/5">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                                <i class="fas fa-tasks mr-1.5 text-blue-400"></i> Order Progress
                            </h3>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-500/15 text-blue-400">
                                Step {{ $currentStep }} / 8
                            </span>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="w-full bg-slate-700/50 rounded-full h-2.5 overflow-hidden mb-5">
                            <div class="h-full rounded-full transition-all duration-700 bg-gradient-to-r from-blue-600 to-blue-400"
                                style="width: {{ ($currentStep / 8) * 100 }}%"></div>
                        </div>

                        {{-- Steps Grid --}}
                        <div class="grid grid-cols-4 gap-2">
                            @foreach($steps as $stepNum => $stepLabel)
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs
                                    {{ $stepNum < $currentStep ? 'bg-emerald-500/10 text-emerald-400' : ($stepNum == $currentStep ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-slate-800/50 text-slate-600') }}">
                                    @if($stepNum < $currentStep)
                                        <i class="fas fa-check text-[10px]"></i>
                                    @elseif($stepNum == $currentStep)
                                        <i class="fas fa-circle text-[8px] animate-pulse"></i>
                                    @else
                                        <i class="fas fa-circle text-[6px]"></i>
                                    @endif
                                    <span class="font-medium truncate">{{ $stepLabel }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ── Two-column: Customer Info + Order Details ── --}}
                <div class="px-8 pb-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Customer Contact --}}
                        <div class="bg-slate-800/40 rounded-2xl p-6 border border-white/5">
                            <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-4">
                                <i class="fas fa-user mr-1.5 text-blue-400"></i> Customer Details
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user text-blue-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Name</div>
                                        <div class="text-sm text-white font-medium">{{ $order->customer_name }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-envelope text-emerald-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Email</div>
                                        <div class="text-sm text-white font-medium">{{ $order->customer_email }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-phone text-amber-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Phone</div>
                                        <div class="text-sm text-white font-medium">{{ $order->phone ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-building text-purple-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Company</div>
                                        <div class="text-sm text-white font-medium">{{ $order->company_name ?: '—' }}</div>
                                    </div>
                                </div>
                                @if($order->website_url)
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-cyan-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-globe text-cyan-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Website</div>
                                        <div class="text-sm text-blue-400 font-medium">{{ $order->website_url }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Order Info --}}
                        <div class="bg-slate-800/40 rounded-2xl p-6 border border-white/5">
                            <h3 class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-4">
                                <i class="fas fa-info-circle mr-1.5 text-blue-400"></i> Order Information
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between bg-slate-900/50 rounded-xl px-4 py-3.5 border border-white/5">
                                    <div class="text-xs text-slate-400">Payment</div>
                                    @if($order->is_payment_verified)
                                        <span class="text-xs font-bold px-3 py-1.5 rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">
                                            <i class="fas fa-check-circle mr-1"></i> Verified
                                        </span>
                                    @else
                                        <span class="text-xs font-bold px-3 py-1.5 rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/20">
                                            <i class="fas fa-clock mr-1"></i> Pending
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between bg-slate-900/50 rounded-xl px-4 py-3.5 border border-white/5">
                                    <div class="text-xs text-slate-400">Assigned Staff</div>
                                    <span class="text-sm font-medium text-white">
                                        {{ $order->staff ? $order->staff->name : '—' }}
                                    </span>
                                </div>

                                @if($order->approved_at)
                                <div class="flex items-center justify-between bg-slate-900/50 rounded-xl px-4 py-3.5 border border-white/5">
                                    <div class="text-xs text-slate-400">Approved</div>
                                    <span class="text-xs text-slate-300">{{ $order->approved_at->format('d M Y, h:i A') }}</span>
                                </div>
                                @endif

                                @if($order->completed_at)
                                <div class="flex items-center justify-between bg-slate-900/50 rounded-xl px-4 py-3.5 border border-white/5">
                                    <div class="text-xs text-slate-400">Completed</div>
                                    <span class="text-xs text-emerald-400">{{ $order->completed_at->format('d M Y, h:i A') }}</span>
                                </div>
                                @endif

                                @if($order->rejection_reason)
                                <div class="bg-red-500/5 rounded-xl px-4 py-3.5 border border-red-500/10">
                                    <div class="text-[10px] text-red-400 uppercase font-semibold mb-1">Rejection Reason</div>
                                    <p class="text-sm text-slate-300">{{ $order->rejection_reason }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Modal Footer ── --}}
                <div class="flex items-center justify-between px-8 py-6 border-t border-white/5 bg-slate-900/50">
                    @if($order->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->phone) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-emerald-900/30">
                            <i class="fab fa-whatsapp text-base"></i> WhatsApp
                        </a>
                    @else
                        <a href="mailto:{{ $order->customer_email }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-xl transition-all duration-200 shadow-lg shadow-blue-900/30">
                            <i class="fas fa-envelope text-sm"></i> Email Customer
                        </a>
                    @endif
                    <div class="flex items-center gap-3">
                        <a href="{{ url('admin/orders/' . $order->id) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold text-sm rounded-xl transition-all duration-200 border border-white/5">
                            <i class="fas fa-external-link-alt text-xs"></i> Full Order Page
                        </a>
                        <button onclick="closeOrderModal({{ $order->id }})"
                            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-sm rounded-xl border border-white/10 transition-all duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
<script>
    function openOrderModal(id) {
        const modal = document.getElementById('order-modal-' + id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.animation = 'fadeIn 0.2s ease-out';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeOrderModal(id) {
        const modal = document.getElementById('order-modal-' + id);
        if (modal) {
            modal.style.animation = 'fadeOut 0.15s ease-in';
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.animation = '';
                document.body.style.overflow = '';
            }, 140);
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="order-modal-"]').forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    const id = modal.id.replace('order-modal-', '');
                    closeOrderModal(id);
                }
            });
        }
    });
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    [id^="order-modal-"] > div::-webkit-scrollbar {
        width: 6px;
    }
    [id^="order-modal-"] > div::-webkit-scrollbar-track {
        background: transparent;
    }
    [id^="order-modal-"] > div::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.3);
        border-radius: 3px;
    }
    [id^="order-modal-"] > div::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.5);
    }
</style>
@endsection