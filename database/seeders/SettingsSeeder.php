<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            ['key' => 'price_access', 'value' => '980'],
            ['key' => 'price_growth', 'value' => '2380'],
            ['key' => 'price_authority', 'value' => '3980'],
            ['key' => 'price_ultimate', 'value' => '4980'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
