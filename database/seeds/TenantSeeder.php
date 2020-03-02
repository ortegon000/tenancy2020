<?php

use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\User::class)->times(10)->create();
        factory(\App\Tenant\Product::class)->times(10)->create();
    }
}
