<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Jenderal',
            'email' => 'jenderal.software@gmail.com',
            'password' => Hash::make('berkahtelur2025'),
        ]);



        // Seed data for egg products in the correct order
        $this->call([
            JenisBarangSeeder::class,
            SatuanSeeder::class,
            BarangSeeder::class,
            RolePermissionSeeder::class,
            SyncUserRolesSeeder::class,
        ]);

        // Uncomment below to seed RAGAS test data for chatbot testing
        // $this->call([
        //     RagasTestDataSeeder::class,
        // ]);
    }
}
