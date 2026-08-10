<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Spatie roles
        $roles = ['super_admin', 'instructor', 'intern'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Super Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@lythub.com'],
            [
                'name' => 'Lythub Admin',
                'password' => Hash::make('Password@123'),
                'role' => 'super_admin',
                'is_active' => true,
                'department' => 'Administration',
            ]
        );
        $admin->syncRoles(['super_admin']);

        // Instructor
        $instructor = User::updateOrCreate(
            ['email' => 'instructor@lythub.com'],
            [
                'name' => 'John Instructor',
                'password' => Hash::make('Password@123'),
                'role' => 'instructor',
                'is_active' => true,
                'department' => 'Training',
            ]
        );
        $instructor->syncRoles(['instructor']);

        // Sample Intern
        $intern = User::updateOrCreate(
            ['email' => 'intern@lythub.com'],
            [
                'name' => 'Jane Intern',
                'password' => Hash::make('Password@123'),
                'role' => 'intern',
                'is_active' => true,
                'department' => 'Cybersecurity',
            ]
        );
        $intern->syncRoles(['intern']);

        // Extra demo interns
        $demoInterns = [
            ['name' => 'Kwame Mensah', 'email' => 'kwame@lythub.com', 'department' => 'Cybersecurity'],
            ['name' => 'Amara Diallo', 'email' => 'amara@lythub.com', 'department' => 'Web Development'],
            ['name' => 'Tunde Adeyemi', 'email' => 'tunde@lythub.com', 'department' => 'Database'],
        ];
        foreach ($demoInterns as $data) {
            $u = User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => Hash::make('Password@123'), 'role' => 'intern', 'is_active' => true])
            );
            $u->syncRoles(['intern']);
        }
    }
}
