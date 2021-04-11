<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApiKey;

class KeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $api_key = "77b643994364a45e3f36c03d9743e188-b892f62e-10b3cef4";
        ApiKey::updateOrCreate(
            [
                'api_key' => $api_key
            ],
            [
                'api_key' => $api_key,
                'related_ip_address' => "127.0.0.1"
            ]
        );
    }
}
