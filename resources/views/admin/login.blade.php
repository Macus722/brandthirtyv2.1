<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login - BrandThirty</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-black': '#050505',
                        'brand-red': '#FF2D46',
                        'brand-red-hover': '#d91b32',
                        'midnight': '#0f172a', // Midnight Blue
                        'charcoal': '#111827', // Deep Charcoal
                        'dark-red': '#450a0a', // Darkened Brand Red
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.8s ease-out forwards',
                        'slide-up': 'slideUp 0.8s ease-out forwards',
                        'gradient-slow': 'gradient 15s ease infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        gradient: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .liquid-bg {
            background: linear-gradient(-45deg, #0f172a, #111827, #450a0a, #0f172a);
            background-size: 400% 400%;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.6); /* Adjusted opacity for better contrast with animated bg */
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .input-focus-ring:focus-within {
            box-shadow: 0 0 0 2px rgba(255, 45, 70, 0.2);
            border-color: #FF2D46;
        }
    </style>
</head>

<body class="h-screen flex flex-col items-center justify-center overflow-hidden antialiased text-slate-300 selection:bg-brand-red selection:text-white relative">

    <!-- Liquid Gradient Background Layer -->
    <div class="fixed inset-0 liquid-bg animate-gradient-slow z-0"></div>

    <!-- Overlay pattern for texture (optional, subtle noise or mesh could go here) -->
    <div class="fixed inset-0 bg-black/20 z-0 pointer-events-none"></div>

    <div class="w-full max-w-[420px] px-6 relative z-10 animate-slide-up">
        
        <!-- Logo Section -->
        <div class="text-center mb-10">
            <img class="h-10 mx-auto object-contain brightness-100 opacity-90 hover:opacity-100 transition-opacity duration-300 drop-shadow-lg" 
                 src="{{ asset('Images/B30_logo-04.png') }}"
                 alt="BrandThirty">
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-xl overflow-hidden relative">
            <!-- Brand Red Power Line -->
            <div class="h-1 w-full bg-brand-red absolute top-0 left-0 shadow-[0_0_15px_rgba(255,45,70,0.5)]"></div>

            <div class="p-8 pt-10">
                <div class="mb-8 text-center">
                    <h2 class="text-xl font-semibold text-white tracking-tight">Admin Access</h2>
                    <p class="text-xs text-slate-400 mt-2 font-medium">Please verify your identity to continue.</p>
                </div>

                @if(isset($error))
                    <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg text-xs font-medium mb-6 text-center animate-fade-in">
                        {{ $error }}
                    </div>
                @endif

                <form method="POST" action="{{ url('/admin/login') }}">
                    @csrf
                    
                    <!-- Username Input -->
                    <div class="mb-5 group">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within:text-brand-red transition-colors">Username</label>
                        <input type="text" name="username"
                            class="w-full bg-slate-900/40 border border-white/10 rounded-lg px-4 py-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:bg-slate-900/70 transition-all duration-300 input-focus-ring backdrop-blur-sm"
                            placeholder="Enter your username" required autocomplete="off">
                    </div>

                    <!-- Password Input -->
                    <div class="mb-8 group">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider group-focus-within:text-brand-red transition-colors">Password</label>
                        </div>
                        <input type="password" name="password"
                            class="w-full bg-slate-900/40 border border-white/10 rounded-lg px-4 py-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:bg-slate-900/70 transition-all duration-300 input-focus-ring backdrop-blur-sm"
                            placeholder="••••••••" required>
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                        class="w-full py-3.5 bg-brand-red hover:bg-brand-red-hover text-white text-sm font-semibold rounded-lg transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg hover:shadow-brand-red/25 active:translate-y-0 tracking-wide">
                        Sign In
                    </button>
                </form>
            </div>
        </div>

        <!-- Professional Footer -->
        <div class="mt-8 text-center animate-fade-in" style="animation-delay: 0.2s;">
            <p class="text-[10px] text-slate-500 font-medium tracking-wide">
                &copy; 2026 BrandThirty Media Authority. <br>
                <span class="opacity-75">Secure Admin Access.</span>
            </p>
        </div>
    </div>

</body>
</html>
