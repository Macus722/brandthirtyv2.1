@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold">Staff Management</h1>
                <p class="text-gray-500 mt-1">Manage admin access and staff roles.</p>
            </div>
            <button onclick="document.getElementById('addStaffModal').classList.remove('hidden')"
                class="bg-brand-red hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg transition shadow-lg shadow-red-900/50">
                <i class="fas fa-plus mr-2"></i> Add New Staff
            </button>
        </div>

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($users as $user)
                <div
                    class="bg-brand-dark border border-white/10 rounded-2xl p-6 shadow-xl relative group hover:border-brand-red/50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-700 to-black flex items-center justify-center font-bold text-xl border border-white/10">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-white">{{ $user->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-bold border {{ $user->role == 'admin' ? 'bg-brand-red text-white border-brand-red' : 'bg-gray-800 text-gray-300 border-gray-700' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                            <a href="{{ url('admin/staff/delete/' . $user->id) }}" onclick="return confirm('Are you sure?')"
                                class="text-gray-600 hover:text-red-500 transition text-xs">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/5 grid grid-cols-2 gap-4 text-center">
                        <div>
                            <span class="block text-gray-600 text-xs uppercase font-bold">Joined</span>
                            <span class="text-gray-400 text-sm">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-600 text-xs uppercase font-bold">Status</span>
                            <span class="text-green-500 text-sm font-bold">Active</span>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-3 text-center py-12 text-gray-500 bg-brand-dark border border-white/5 rounded-2xl border-dashed">
                    <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                    <p>No staff members found.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addStaffModal"
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-brand-dark border border-white/10 rounded-2xl w-full max-w-md p-8 shadow-2xl relative">
            <button onclick="document.getElementById('addStaffModal').classList.add('hidden')"
                class="absolute top-4 right-4 text-gray-500 hover:text-white">
                <i class="fas fa-times"></i>
            </button>

            <h3 class="text-xl font-bold text-white mb-6">Add New Staff Member</h3>

            <form action="{{ url('admin/staff') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-bold uppercase text-gray-500 block mb-2">Full Name</label>
                    <input type="text" name="name" required
                        class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition">
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-gray-500 block mb-2">Email Address</label>
                    <input type="email" name="email" required
                        class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition">
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-gray-500 block mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition">
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-gray-500 block mb-2">Role</label>
                    <select name="role"
                        class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-brand-red focus:outline-none transition">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-xl bg-brand-red text-white font-bold hover:bg-red-600 transition shadow-lg shadow-red-900/50 mt-4">
                    Create Account
                </button>
            </form>
        </div>
    </div>
@endsection