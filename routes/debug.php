<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;

/**
 * Debug Routes - Check Payment Proof Upload Issues
 * Access via: http://yoursite.test/debug/receipt-status
 */

Route::prefix('debug')->group(function () {
    
    Route::get('/receipt-status', function () {
        $orders = Order::where('receipt_path', '!=', null)->limit(5)->get();
        
        $results = [];
        
        foreach ($orders as $order) {
            $exists = Storage::disk('public')->exists($order->receipt_path);
            $path = $order->receipt_path;
            $url = Storage::url($order->receipt_path);
            $fullPath = storage_path('app/public/' . $order->receipt_path);
            
            $results[] = [
                'order_id' => $order->order_id,
                'receipt_path_in_db' => $path,
                'storage_disk_exists' => $exists,
                'storage_url' => $url,
                'full_filesystem_path' => $fullPath,
                'file_physically_exists' => file_exists($fullPath),
            ];
        }
        
        return response()->json([
            'message' => 'Receipt Status Check',
            'storage_symlink_exists' => file_exists(public_path('storage')),
            'storage_app_public_exists' => is_dir(storage_path('app/public')),
            'orders' => $results,
        ], 200);
    });

    Route::get('/storage-info', function () {
        return response()->json([
            'default_disk' => config('filesystems.default'),
            'public_disk_root' => config('filesystems.disks.public.root'),
            'public_disk_url' => config('filesystems.disks.public.url'),
            'storage_path' => storage_path(),
            'public_path' => public_path(),
            'symlink_exists' => is_link(public_path('storage')),
            'symlink_target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : 'N/A',
        ]);
    });
});
