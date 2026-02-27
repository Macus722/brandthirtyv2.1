<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel');

use Illuminate\Support\Facades\DB;

try {
    // Check if column exists
    $result = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'orders' AND column_name = 'receipt_path'");
    if (empty($result)) {
        echo "Column receipt_path does NOT exist.\n";
        echo "Attempting to add it...\n";
        
        DB::statement('ALTER TABLE orders ADD COLUMN receipt_path VARCHAR(255)');
        echo "Column receipt_path added successfully!\n";
    } else {
        echo "Column receipt_path already exists.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
