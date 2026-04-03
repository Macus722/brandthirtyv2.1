<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - BrandThirty</title>
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    @stack('scripts_head')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-red': '#E31E24',
                        'brand-red-hover': '#c91a1f',
                        'surface': '#0f172a',
                        'surface-card': 'rgba(15, 23, 42, 0.65)',
                        'surface-raised': 'rgba(30, 41, 59, 0.5)',
                        'border-subtle': '#1e293b',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
        }

        .exec-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid #1e293b;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .exec-card:hover {
            border-color: rgba(148, 163, 184, 0.15);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.15);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.3);
        }
    </style>
</head>

<body class="bg-surface text-slate-200 antialiased flex h-screen overflow-hidden">
    @php $authUser = auth()->user(); @endphp

    <!-- Sidebar -->
    <aside
        class="w-64 bg-surface-card backdrop-blur-xl border-r border-border-subtle flex flex-col justify-between hidden md:flex flex-shrink-0">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="h-20 flex items-center px-8 border-b border-border-subtle flex-shrink-0">
                <img class="h-8 w-auto object-contain" src="{{ asset('Images/B30_logo-04.png') }}" alt="BrandThirty">
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-5 space-y-1.5 overflow-y-auto">
                <a href="{{ url('admin') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                    {{ request()->is('admin') && !request()->has('status') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-home w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ url('admin/orders') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                    {{ request()->is('admin/orders*') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-tasks w-5 text-center"></i>
                    <span>Order Management</span>
                </a>

                <a href="{{ url('admin/clients') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                    {{ request()->is('admin/clients*') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-briefcase w-5 text-center"></i>
                    <span>Client Management</span>
                </a>

                @if($authUser && $authUser->role == 'admin')
                    <a href="{{ url('admin/services') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                {{ request()->is('admin/services*') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-sliders-h w-5 text-center"></i>
                        <span>Service Manager</span>
                    </a>
                @endif

                @if($authUser && $authUser->is_super_admin)
                    <a href="{{ url('admin/staff') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                {{ request()->is('admin/staff*') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-users-cog w-5 text-center"></i>
                        <span>Staff Management</span>
                    </a>

                    <a href="{{ url('admin/reports/sales') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                {{ request()->is('admin/reports/sales*') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-chart-bar w-5 text-center"></i>
                        <span>Sales Report</span>
                    </a>

                    <a href="{{ url('admin/network') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                {{ request()->is('admin/network*') ? 'bg-brand-red text-white shadow-lg shadow-red-900/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-network-wired w-5 text-center"></i>
                        <span>Network Management</span>
                    </a>
                @endif

                <div class="pt-5 mt-5 border-t border-border-subtle">
                    <button onclick="window.location.href='{{ url('admin/logout') }}'"
                        class="w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-all duration-200 text-sm font-medium">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </nav>

            <!-- User Info -->
            <div class="p-5 border-t border-border-subtle flex-shrink-0">
                <div class="flex items-center gap-3 px-3">
                    <div
                        class="w-9 h-9 rounded-full bg-brand-red flex items-center justify-center text-sm font-bold text-white">
                        {{ strtoupper(substr($authUser->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">{{ $authUser->name ?? 'Admin' }}</div>
                        <div class="text-xs text-slate-500 capitalize">{{ $authUser->role ?? 'Admin' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Mobile Header -->
        <header
            class="h-16 bg-surface-card backdrop-blur-xl border-b border-border-subtle flex md:hidden items-center justify-between px-5 flex-shrink-0">
            <img class="h-6 w-auto" src="{{ asset('Images/B30_logo-04.png') }}" alt="BrandThirty">
            <button onclick="confirmLogout()" class="text-slate-400 hover:text-white transition"><i
                    class="fas fa-sign-out-alt"></i></button>
        </header>

        <!-- Main Content Scrollable Area -->
        <main class="flex-1 overflow-y-auto p-6 sm:p-10 relative">
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: "{{ session('success') }}",
                            background: '#0f172a',
                            color: '#e2e8f0',
                            confirmButtonColor: '#E31E24',
                            backdrop: 'rgba(0,0,0,0.6)',
                        });
                    });
                </script>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: "Are you sure you want to end your session?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E31E24',
                cancelButtonColor: '#334155',
                confirmButtonText: 'Yes, Logout',
                background: '#0f172a',
                color: '#e2e8f0',
                backdrop: 'rgba(0,0,0,0.6)',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ url('admin/logout') }}";
                }
            })
        }
    </script>
    @yield('scripts')
</body>

</html>