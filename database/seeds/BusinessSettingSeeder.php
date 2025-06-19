<?php

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BusinessSetting::insert([
            'id' => 1,
            'type' => 'system_default_currency',
            'value' => 1,
        ]);
    }
}
