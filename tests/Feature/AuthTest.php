<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'intern', 'guard_name' => 'web']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'testadmin@lythub.com',
            'password' => Hash::make('Password@123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $user->assignRole('super_admin');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'testadmin@lythub.com',
            'password' => 'Password@123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                ],
            ]);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'testintern@lythub.com',
            'password' => Hash::make('Password@123'),
            'role' => 'intern',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'testintern@lythub.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@lythub.com',
            'password' => Hash::make('Password@123'),
            'role' => 'intern',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@lythub.com',
            'password' => 'Password@123',
        ]);

        $response->assertStatus(403);
    }
}
