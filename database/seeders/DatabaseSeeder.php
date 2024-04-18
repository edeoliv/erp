<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            LeadSeeder::class,
            StageSeeder::class,
            TagSeeder::class,
            VerticalSeeder::class,
            RoleSeeder::class,
            AdminSeeder::class,
            StaffSeeder::class,
            CustomerSeeder::class,
            TaskSeeder::class,
            CustomFieldSeeder::class,
            CountySeeder::class,
            BrandSeeder::class,
        ]);
    }
}
