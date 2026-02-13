<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class StaffController extends Controller
{
    private function checkLogin()
    {
        if (!auth()->check()) {
            redirect('/admin/login')->send();
            exit;
        }
    }

    public function index()
    {
        $this->checkLogin();

        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.staff.index', compact('users'));
    }

    public function store(Request $request)
    {
        $this->checkLogin();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
            'role' => 'required|in:admin,staff',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'New staff member added successfully.');
    }

    public function update(Request $request, $id)
    {
        $this->checkLogin();
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string',
            'role' => 'required|in:admin,staff',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role;
        $user->save();

        return redirect()->back()->with('success', 'Staff member updated.');
    }

    public function destroy($id)
    {
        $this->checkLogin();
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Staff member deleted.');
    }
}
