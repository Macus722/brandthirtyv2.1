@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Staff Management</h1>
                <p class="text-slate-500 text-sm mt-1">Manage admin access and staff roles.</p>
            </div>
            <button onclick="document.getElementById('addStaffModal').classList.remove('hidden')"
                class="bg-brand-red hover:bg-brand-red-hover text-white font-semibold py-2.5 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-red-900/20">
                <i class="fas fa-plus mr-2"></i> Add New Staff
            </button>
        </div>

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-5 rounded-xl mb-8 text-sm">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($users as $user)
                <div class="exec-card p-7 relative group">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-slate-700 flex items-center justify-center font-bold text-xl text-white">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">{{ $user->name }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $user->role == 'admin' ? 'bg-brand-red text-white' : 'bg-slate-700 text-slate-300' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                            <a href="{{ url('admin/staff/delete/' . $user->id) }}" onclick="return confirm('Are you sure?')"
                                class="text-slate-600 hover:text-red-400 transition text-xs">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-border-subtle grid grid-cols-2 gap-4 text-center">
                        <div>
                            <span class="block text-slate-600 text-xs uppercase font-semibold tracking-wider">Joined</span>
                            <span class="text-slate-300 text-sm mt-1 block">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-600 text-xs uppercase font-semibold tracking-wider">Status</span>
                            <span class="text-emerald-400 text-sm font-semibold mt-1 block">Active</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 text-slate-500 exec-card border-dashed">
                    <i class="fas fa-users text-4xl mb-4 opacity-30"></i>
                    <p class="text-sm">No staff members found.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addStaffModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="exec-card w-full max-w-md p-8 relative">
            <button onclick="document.getElementById('addStaffModal').classList.add('hidden')"
                class="absolute top-5 right-5 text-slate-500 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>

            <h3 class="text-xl font-bold text-white mb-7">Add New Staff Member</h3>

            <form action="{{ url('admin/staff') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-500 block mb-2 tracking-wider">Full Name</label>
                    <input type="text" name="name" required
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-500 block mb-2 tracking-wider">Email Address</label>
                    <input type="email" name="email" required
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-500 block mb-2 tracking-wider">Password</label>
                    <input type="password" name="password" required
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-500 block mb-2 tracking-wider">Role</label>
                    <select name="role"
                        class="w-full bg-slate-800/50 border border-border-subtle rounded-xl px-5 py-3 text-white focus:border-slate-500 focus:outline-none transition text-sm">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-xl bg-brand-red text-white font-semibold hover:bg-brand-red-hover transition-all duration-200 shadow-lg shadow-red-900/20 mt-2">
                    Create Account
                </button>
            </form>
        </div>
    </div>
@endsection
