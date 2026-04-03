@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-12">
            <h1 class="text-3xl font-bold text-white tracking-tight">Client Management</h1>
            <p class="text-slate-500 text-sm mt-1">Manage your online and offline client relationships.</p>
        </div>

        {{-- Option Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- Online Sales Card --}}
            <a href="{{ url('admin/clients/online') }}"
                class="exec-card p-10 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300 cursor-pointer block">

                {{-- Decorative gradient orb --}}
                <div
                    class="absolute -top-10 -right-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/20 transition-all duration-500">
                </div>

                <div class="relative z-10">
                    {{-- Icon --}}
                    <div
                        class="w-16 h-16 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center mb-7 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-globe text-2xl text-blue-400"></i>
                    </div>

                    {{-- Title --}}
                    <h2 class="text-2xl font-bold text-white mb-3">Online Sales</h2>
                    <p class="text-slate-400 text-sm leading-relaxed mb-8">
                        View and track all customer orders received through the website. Read-only overview of your digital
                        sales pipeline.
                    </p>

                    {{-- Stats --}}
                    <div class="flex items-center gap-6 mb-8">
                        <div class="bg-slate-800/50 rounded-xl px-5 py-3 border border-border-subtle">
                            <div class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Total Orders
                            </div>
                            <div class="text-2xl font-bold text-blue-400 mt-0.5">{{ number_format($onlineCount) }}</div>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div
                        class="flex items-center gap-2 text-blue-400 font-semibold text-sm group-hover:gap-3 transition-all duration-300">
                        <span>View Online Sales List</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </div>
                </div>
            </a>

            {{-- Offline Sales Card --}}
            <a href="{{ url('admin/clients/offline') }}"
                class="exec-card p-10 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300 cursor-pointer block">

                {{-- Decorative gradient orb --}}
                <div
                    class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-all duration-500">
                </div>

                <div class="relative z-10">
                    {{-- Icon --}}
                    <div
                        class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-7 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-handshake text-2xl text-emerald-400"></i>
                    </div>

                    {{-- Title --}}
                    <h2 class="text-2xl font-bold text-white mb-3">Offline Sales</h2>
                    <p class="text-slate-400 text-sm leading-relaxed mb-8">
                        Add and manage offline enterprise clients with subscription-based payment tracking. Monitor monthly
                        dues and outstanding balances.
                    </p>

                    {{-- Stats --}}
                    <div class="flex items-center gap-6 mb-8">
                        <div class="bg-slate-800/50 rounded-xl px-5 py-3 border border-border-subtle">
                            <div class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Active Clients
                            </div>
                            <div class="text-2xl font-bold text-emerald-400 mt-0.5">{{ number_format($offlineCount) }}</div>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div
                        class="flex items-center gap-2 text-emerald-400 font-semibold text-sm group-hover:gap-3 transition-all duration-300">
                        <span>Manage Offline Sales</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </div>
                </div>
            </a>

        </div>
    </div>
@endsection