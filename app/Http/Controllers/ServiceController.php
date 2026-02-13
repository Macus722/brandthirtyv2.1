<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;

class ServiceController extends Controller
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

        // Fetch settings for pricing similarly to how AdminController@settings did
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.services.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->checkLogin();

        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Services & Pricing updated successfully.');
    }
}
