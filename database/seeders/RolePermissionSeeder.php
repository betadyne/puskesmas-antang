<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view-dashboard',
            'call-queue',
            'skip-queue',
            'recall-queue',
            'finish-queue',
            'view-own-poli-queue',
            'view-reports',
            'register-online',
            'view-own-queue-status',
            'view-display-tv',
            'check-queue-by-nomor',
            'manage-users',
            'manage-polis',
            'manage-patients',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);
        $pasienRole = Role::firstOrCreate(['name' => 'pasien']);
        $publicRole = Role::firstOrCreate(['name' => 'public']);

        // Admin permissions - all permissions
        $adminRole->givePermissionTo(Permission::all());

        // Petugas permissions
        $petugasPermissions = [
            'view-dashboard',
            'call-queue',
            'skip-queue',
            'recall-queue',
            'finish-queue',
            'view-own-poli-queue',
            'view-reports',
        ];
        $petugasRole->givePermissionTo($petugasPermissions);

        // Pasien permissions
        $pasienPermissions = [
            'register-online',
            'view-own-queue-status',
        ];
        $pasienRole->givePermissionTo($pasienPermissions);

        // Public permissions
        $publicPermissions = [
            'view-display-tv',
            'check-queue-by-nomor',
        ];
        $publicRole->givePermissionTo($publicPermissions);

        // Create admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@puskesmas-antang.com',
        ], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create sample petugas user
        $petugas = User::firstOrCreate([
            'email' => 'petugas@puskesmas-antang.com',
        ], [
            'name' => 'Petugas Poli Umum',
            'password' => Hash::make('password'),
            'poli_id' => 1, // Assign to Poli Umum
        ]);
        $petugas->assignRole('petugas');
    }
}
