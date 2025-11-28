<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero crear los roles
        $this->call(RoleSeeder::class);

        // Crear organización por defecto
        $organization = \App\Models\Organization::create([
            'name' => 'NebulaDesk Inc',
        ]);

        // Crear usuario administrador
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@nebuladesk.com',
            'password' => bcrypt('Admin123!'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'organization_id' => $organization->id,
        ]);

        // Usuario de prueba adicional
        User::factory()->create([
            'name' => 'Test Agent',
            'email' => 'agent@nebuladesk.com',
            'password' => bcrypt('Agent123!'),
            'role' => 'agent',
            'email_verified_at' => now(),
            'organization_id' => $organization->id,
        ]);

        User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@nebuladesk.com',
            'password' => bcrypt('Customer123!'),
            'role' => 'customer',
            'email_verified_at' => now(),
            'organization_id' => $organization->id,
        ]);
    }
}
