<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::updateOrCreate(['email' => 'dokter@example.com'], [
            'name' => 'Dr. Demo',
            'password' => bcrypt('test@1234'),
            'role' => 'doctor',
        ]);

        User::updateOrCreate(['email' => 'apoteker@example.com'], [
            'name' => 'Apt. Demo',
            'password' => bcrypt('test@1234'),
            'role' => 'pharmacist',
        ]);

        $this->call([
            IcdSeeder::class,
            TindakanSeeder::class,
            PasienSeeder::class
        ]);
    }
}
