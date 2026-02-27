<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddReceiptPathColumn extends Command
{
    protected $signature = 'db:add-receipt-column';
    protected $description = 'Add receipt_path column to orders table';

    public function handle()
    {
        try {
            // Check if column already exists
            $columns = DB::select("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = 'orders' 
                AND column_name = 'receipt_path'
            ");

            if (count($columns) > 0) {
                $this->info('✓ Column receipt_path already exists');
                return 0;
            }

            // Add the column
            DB::statement('ALTER TABLE orders ADD COLUMN receipt_path VARCHAR(255) NULL');
            
            $this->info('✓ Column receipt_path added successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
