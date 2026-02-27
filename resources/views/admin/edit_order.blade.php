@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-10 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Edit Order</h1>
                <p class="text-slate-500 text-sm mt-1">Order #{{ $order->order_id }}</p>
            </div>
            <a href="{{ url('admin') }}" class="text-slate-400 hover:text-white transition-colors duration-200 text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <div class="exec-card p-8">
            <form method="POST" action="{{ url('admin/update', $order->id) }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-7">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2 tracking-wider">Customer Name</label>
                        <input type="text" name="customer_name" value="{{ $order->customer_name }}" required
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:outline-none focus:border-slate-500 transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2 tracking-wider">Email</label>
                        <input type="email" name="customer_email" value="{{ $order->customer_email }}" required
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:outline-none focus:border-slate-500 transition text-sm">
                    </div>
                </div>

                <div class="mb-7">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2 tracking-wider">Phone</label>
                    <input type="text" name="phone" value="{{ $order->phone }}" required
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:outline-none focus:border-slate-500 transition text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-7">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2 tracking-wider">Company Name</label>
                        <input type="text" name="company_name" value="{{ $order->company_name }}"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:outline-none focus:border-slate-500 transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2 tracking-wider">Website URL</label>
                        <input type="text" name="website_url" value="{{ $order->website_url }}"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:outline-none focus:border-slate-500 transition text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-7">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2 tracking-wider">Plan</label>
                        <select name="plan"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:outline-none focus:border-slate-500 transition text-sm">
                            <option value="access" {{ strtolower($order->plan) == 'access' ? 'selected' : '' }}>Access Plan</option>
                            <option value="growth" {{ strtolower($order->plan) == 'growth' ? 'selected' : '' }}>Growth Plan</option>
                            <option value="authority" {{ strtolower($order->plan) == 'authority' ? 'selected' : '' }}>Authority Plan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2 tracking-wider">Strategy</label>
                        <select name="strategy"
                            class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:outline-none focus:border-slate-500 transition text-sm">
                            <option value="conservative" {{ strtolower($order->strategy) == 'conservative' ? 'selected' : '' }}>Conservative</option>
                            <option value="balanced" {{ strtolower($order->strategy) == 'balanced' ? 'selected' : '' }}>Balanced</option>
                            <option value="aggressive" {{ strtolower($order->strategy) == 'aggressive' ? 'selected' : '' }}>Aggressive</option>
                            <option value="pro" {{ strpos(strtolower($order->strategy), 'pro') !== false ? 'selected' : '' }}>Pro Copywriting</option>
                            <option value="ai" {{ strpos(strtolower($order->strategy), 'ai') !== false ? 'selected' : '' }}>AI Assisted</option>
                        </select>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2 tracking-wider">Total Amount (RM)</label>
                    <input type="number" step="0.01" name="total_amount" value="{{ $order->total_amount }}" required
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:outline-none focus:border-slate-500 transition text-sm">
                </div>

                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-brand-red hover:bg-brand-red-hover text-white font-semibold py-3 rounded-xl transition-all duration-200 shadow-lg shadow-red-900/20">
                        Update Order
                    </button>
                    <a href="{{ url('admin') }}"
                        class="flex-1 bg-transparent border border-border-subtle hover:border-slate-500 text-white font-semibold py-3 rounded-xl transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
