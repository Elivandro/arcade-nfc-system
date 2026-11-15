<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * NFC CARD ID: 7B19FC53
         * name: Test User
         * 
         * NFC CARD ID: 6C1F768E
         * name: Test User 2
         */

        Customer::factory()
            ->hasCards(['uid' => '7B19FC53'])
            ->create([
            'name' => 'Test User',
            'document' => '123456789',
            'phone' => '+55 11 99999-9999',
            'email' => 'user1@email.com',
        ]);

        Customer::factory()
            ->hasCards(['uid' => '6C1F768E'])
            ->create([
            'name' => 'Test User 2',
            'document' => '987654321',
            'phone' => '+55 11 88888-8888',
            'email' => 'user2@email.com',
        ]);
    }
}
