<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order # {{ $order->order_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-black': '#050505',
                        'brand-dark': '#111111',
                        'brand-red': '#FF2D46',
                        'brand-red-hover': '#d91b32',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-brand-black text-white min-h-screen py-10 px-4">

    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold">Edit Order</h1>
            <a href="{{ url('admin') }}" class="text-gray-400 hover:text-white transition"><i
                    class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard</a>
        </div>

        <div class="bg-brand-dark border border-white/10 rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ url('admin/update', $order->id) }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <!-- Personal Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Customer Name</label>
                        <input type="text" name="customer_name" value="{{ $order->customer_name }}" required
                            class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email</label>
                        <input type="email" name="customer_email" value="{{ $order->customer_email }}" required
                            class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ $order->phone }}" required
                        class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition">
                </div>

                <!-- Company Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Company Name</label>
                        <input type="text" name="company_name" value="{{ $order->company_name }}"
                            class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Website URL</label>
                        <input type="text" name="website_url" value="{{ $order->website_url }}"
                            class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition">
                    </div>
                </div>

                <!-- Order Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Plan</label>
                        <select name="plan"
                            class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition">
                            <option value="access" {{ strtolower($order->plan) == 'access' ? 'selected' : '' }}>Access
                                Plan</option>
                            <option value="growth" {{ strtolower($order->plan) == 'growth' ? 'selected' : '' }}>Growth
                                Plan</option>
                            <option value="authority" {{ strtolower($order->plan) == 'authority' ? 'selected' : '' }}>
                                Authority Plan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Strategy</label>
                        <select name="strategy"
                            class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition">
                            <option value="conservative" {{ strtolower($order->strategy) == 'conservative' ? 'selected' : '' }}>Conservative
                            </option>
                            <option value="balanced" {{ strtolower($order->strategy) == 'balanced' ? 'selected' : '' }}>
                                Balanced</option>
                            <option value="aggressive" {{ strtolower($order->strategy) == 'aggressive' ? 'selected' : '' }}>Aggressive
                            </option>
                            <option value="pro" {{ strpos(strtolower($order->strategy), 'pro') !== false ? 'selected' : '' }}>Pro Copywriting</option>
                            <option value="ai" {{ strpos(strtolower($order->strategy), 'ai') !== false ? 'selected' : '' }}>AI Assisted</option>
                        </select>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Total Amount (RM)</label>
                    <input type="number" step="0.01" name="total_amount" value="{{ $order->total_amount }}" required
                        class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition">
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-brand-red hover:bg-brand-red-hover text-white font-bold py-3 rounded-lg transition shadow-lg hover:shadow-red-900/40">
                        Update Order
                    </button>
                    <a href="{{ url('admin') }}"
                        class="flex-1 bg-transparent border border-white/10 hover:border-white/30 text-white font-bold py-3 rounded-lg transition text-center">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

</body>

</html>