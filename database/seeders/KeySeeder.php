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
        $api_key = env('MAILGUN_SECRET');
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
