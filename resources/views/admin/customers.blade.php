@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Customer Management</h1>
                <p class="text-slate-500 text-sm mt-1">View and manage your client base.</p>
            </div>

            <form action="{{ url('admin/customers') }}" method="GET" class="relative w-full sm:w-64">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customers..."
                    class="w-full bg-slate-800/50 border border-border-subtle rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-slate-500 focus:outline-none transition">
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($customers as $customer)
                <div class="exec-card p-7 relative overflow-hidden group hover:-translate-y-0.5 transition-all duration-300
                    {{ $customer->is_vip ? 'border-amber-500/30' : '' }}">

                    @if($customer->is_vip)
                        <div class="absolute top-0 right-0 bg-amber-500 text-black text-[10px] font-bold px-3 py-1 rounded-bl-xl z-10">
                            <i class="fas fa-crown mr-1"></i> VIP
                        </div>
                    @endif

                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-12 h-12 rounded-full {{ $customer->is_vip ? 'bg-amber-500/15 text-amber-400' : 'bg-slate-700 text-slate-300' }} flex items-center justify-center text-xl font-bold flex-shrink-0">
                            {{ substr($customer->name, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <h3 class="text-white font-semibold truncate" title="{{ $customer->name }}">{{ $customer->name }}</h3>
                            <div class="text-xs text-slate-500 truncate mt-0.5">{{ $customer->email }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-slate-800/30 rounded-xl p-3.5 text-center border border-border-subtle">
                            <div class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider">Total Spent</div>
                            <div class="text-lg font-bold mt-1 {{ $customer->is_vip ? 'text-amber-400' : 'text-white' }}">
                                RM {{ number_format($customer->total_spent, 0) }}
                            </div>
                        </div>
                        <div class="bg-slate-800/30 rounded-xl p-3.5 text-center border border-border-subtle">
                            <div class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider">Orders</div>
                            <div class="text-lg font-bold text-white mt-1">{{ $customer->order_count }}</div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-xs text-slate-500 mb-5 border-t border-border-subtle pt-4">
                        <span>Last Seen:</span>
                        <span>{{ $customer->last_order_at ? \Carbon\Carbon::parse($customer->last_order_at)->diffForHumans() : 'Never' }}</span>
                    </div>

                    <a href="https://wa.me/{{ $customer->phone }}" target="_blank"
                        class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                    </a>
                </div>
            @endforeach
        </div>

        @if($customers->isEmpty())
            <div class="h-64 flex flex-col items-center justify-center text-slate-500">
                <i class="fas fa-users-slash text-4xl mb-4 opacity-30"></i>
                <p class="text-sm">No customers found.</p>
            </div>
        @endif
    </div>
@endsection
