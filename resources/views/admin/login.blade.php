<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BrandThirty</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

<body class="bg-brand-black text-white h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8">
        <div class="text-center mb-8">
            <img class="h-12 w-auto mx-auto object-contain brightness-100" src="{{ asset('Images/B30_logo-04.png') }}"
                alt="BrandThirty">
        </div>
        <div class="bg-brand-dark border border-white/10 rounded-2xl p-8 shadow-2xl">
            <h2 class="text-xl font-bold mb-6 text-center">Admin Access</h2>

            @if(isset($error))
                <div class='bg-red-500/20 text-red-500 p-3 rounded text-sm mb-4 text-center border border-red-500/50'>
                    {{ $error }}
                </div>
            @endif

            <form method="POST" action="{{ url('/admin/login') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Username</label>
                    <input type="text" name="username"
                        class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition"
                        placeholder="admin" required>
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Password</label>
                    <input type="password" name="password"
                        class="w-full bg-brand-black border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-red transition"
                        placeholder="123" required>
                </div>
                <!-- Login Button -->
                <button type="submit"
                    class="w-full py-3 bg-brand-red hover:bg-brand-red-hover text-white font-bold rounded-lg transition-all shadow-lg hover:shadow-red-900/40">
                    Login
                </button>
            </form>
        </div>
    </div>
</body>

</html>