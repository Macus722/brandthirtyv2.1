@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold">Customer Management</h1>
                <p class="text-gray-500 text-sm mt-1">View and manage your client base.</p>
            </div>

            <form action="{{ url('admin/customers') }}" method="GET" class="relative w-full sm:w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customers..."
                    class="w-full bg-brand-dark border border-white/10 rounded-full pl-9 pr-4 py-2 text-sm text-white focus:border-brand-red focus:outline-none transition">
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($customers as $customer)
                <div
                    class="bg-brand-dark border {{ $customer->is_vip ? 'border-yellow-500/50' : 'border-white/10' }} rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:-translate-y-1 transition duration-300">

                    @if($customer->is_vip)
                        <div class="absolute top-0 right-0 bg-yellow-500 text-black text-[10px] font-bold px-3 py-1 rounded-bl-xl shiny-gold z-10"
                            style="background: linear-gradient(45deg, #FFD700, #FDB931, #FFD700); background-size: 200% auto; animation: shine 3s linear infinite;">
                            <i class="fas fa-crown mr-1"></i> VIP MEMBER
                        </div>
                    @endif

                    <div class="flex items-center gap-4 mb-4">
                        <div
                            class="w-12 h-12 rounded-full {{ $customer->is_vip ? 'bg-yellow-500/20 text-yellow-500' : 'bg-white/5 text-gray-400' }} flex items-center justify-center text-xl font-bold">
                            {{ substr($customer->name, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <h3 class="text-white font-bold truncate" title="{{ $customer->name }}">{{ $customer->name }}</h3>
                            <div class="text-xs text-gray-500 truncate">{{ $customer->email }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-black/20 rounded-lg p-3 text-center">
                            <div class="text-[10px] text-gray-500 uppercase font-bold">Total Spent</div>
                            <div class="text-lg font-bold text-white {{ $customer->is_vip ? 'text-yellow-500' : '' }}">
                                RM {{ number_format($customer->total_spent, 0) }}
                            </div>
                        </div>
                        <div class="bg-black/20 rounded-lg p-3 text-center">
                            <div class="text-[10px] text-gray-500 uppercase font-bold">Orders</div>
                            <div class="text-lg font-bold text-white">{{ $customer->order_count }}</div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-500 mb-4 border-t border-white/5 pt-4">
                        <span>Last Seen:</span>
                        <span>{{ $customer->last_order_at ? \Carbon\Carbon::parse($customer->last_order_at)->diffForHumans() : 'Never' }}</span>
                    </div>

                    <a href="https://wa.me/{{ $customer->phone }}" target="_blank"
                        class="block w-full text-center bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold py-2 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                    </a>

                </div>
            @endforeach
        </div>

        @if($customers->isEmpty())
            <div class="h-64 flex flex-col items-center justify-center text-gray-500">
                <i class="fas fa-users-slash text-4xl mb-4 opacity-50"></i>
                <p>No customers found.</p>
            </div>
        @endif

    </div>

    <style>
        @keyframes shine {
            to {
                background-position: 200% center;
            }
        }
    </style>
@endsection