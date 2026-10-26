<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('GeoAttend');
        $response->assertSee('Masuk');
    }

    public function test_admin_can_authenticate_and_redirects_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@geoattend.ac.id',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_lecturer_can_authenticate_and_redirects_to_lecturer_dashboard(): void
    {
        $lecturer = User::create([
            'name' => 'Dosen User',
            'email' => 'dosen@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'dosen@geoattend.ac.id',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($lecturer);
        $response->assertRedirect(route('lecturer.dashboard'));
    }

    public function test_student_can_authenticate_and_redirects_to_student_dashboard(): void
    {
        $student = User::create([
            'name' => 'Mahasiswa User',
            'email' => 'student@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'student@geoattend.ac.id',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($student);
        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::create([
            'name' => 'Disabled User',
            'email' => 'disabled@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'disabled@geoattend.ac.id',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Active User',
            'email' => 'user@geoattend.ac.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
