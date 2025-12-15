<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Akses;

class SyncUserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Sync existing user roles from Akses table to Spatie Permission
     */
    public function run(): void
    {
        // Get all users with their akses
        $users = User::with('akses')->get();

        foreach ($users as $user) {
            if ($user->akses && $user->akses->role) {
                $role = $user->akses->role;
                
                // Sync role if it's one of the valid roles
                if (in_array($role, ['admin', 'kasir', 'staff_gudang', 'akuntan'])) {
                    $user->syncRoles([$role]);
                    $this->command->info("User {$user->name} assigned role: {$role}");
                }
            }
        }

        $this->command->info('User roles synced successfully!');
    }
}
