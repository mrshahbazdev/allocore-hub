<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Plan;
use App\Models\Tool;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $tools = Tool::pluck('id', 'slug');

        $starter = Plan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'GmbH Analyse + Audit.',
                'price_monthly' => 49,
                'price_yearly' => 499,
                'currency' => 'EUR',
                'billing_interval' => 'monthly',
            ]
        );
        $starter->tools()->syncWithoutDetaching([
            $tools['gmbh'] => ['included' => true, 'max_quantity' => 5],
            $tools['audit'] => ['included' => true, 'max_quantity' => 3],
        ]);

        $growth = Plan::firstOrCreate(
            ['slug' => 'growth'],
            [
                'name' => 'Growth',
                'description' => 'Alle Tools inklusive.',
                'price_monthly' => 149,
                'price_yearly' => 1499,
                'currency' => 'EUR',
                'billing_interval' => 'monthly',
            ]
        );
        $growth->tools()->syncWithoutDetaching(
            $tools->mapWithKeys(fn ($id) => [$id => ['included' => true, 'max_quantity' => null]])->all()
        );

        $bundle = Bundle::firstOrCreate(
            ['slug' => 'growth-bundle'],
            [
                'name' => 'Growth Bundle',
                'description' => 'Alle Tools mit 20% Rabatt.',
                'discount_percent' => 20,
            ]
        );
        $bundle->tools()->syncWithoutDetaching($tools->values()->all());
    }
}
