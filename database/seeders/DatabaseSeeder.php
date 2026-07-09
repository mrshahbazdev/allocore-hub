<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'demo-gmbh'],
            ['name' => 'Demo GmbH']
        );

        User::firstOrCreate(
            ['email' => 'owner@allocore.test'],
            [
                'company_id' => $company->id,
                'role' => User::ROLE_OWNER,
                'name' => 'Demo Owner',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
