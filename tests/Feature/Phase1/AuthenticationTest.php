<?php

namespace Tests\Feature\Phase1;

use App\Models\Role;
use App\Models\School;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign in to ClassBridge AI');
    }

    public function test_registration_page_loads(): void
    {
        $this->seed();
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create Your School');
    }

    public function test_super_admin_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'admin@classbridge.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('super-admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_school_admin_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'principal@demoacademy.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('school.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_teacher_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'teacher@demoacademy.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_student_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'student@demoacademy.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_parent_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'parent@demoacademy.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('parent.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_school_owner_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'owner@demoacademy.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('school.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_invalid_credentials_rejected(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'admin@classbridge.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_role_middleware_blocks_unauthorized(): void
    {
        $this->seed();

        // Login as student
        $this->post('/login', [
            'email' => 'student@demoacademy.com',
            'password' => 'password',
        ]);

        // Try to access super admin dashboard as student
        $response = $this->get(route('super-admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_logout(): void
    {
        $this->seed();

        $this->post('/login', [
            'email' => 'admin@classbridge.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $this->post('/logout');

        $this->assertGuest();
    }

    public function test_school_registration(): void
    {
        // Seed roles and plans
        Role::insert([
            ['name' => 'School Admin', 'slug' => 'school_admin', 'description' => 'School admin', 'created_at' => now(), 'updated_at' => now()],
        ]);

        SubscriptionPlan::insert([
            ['name' => 'Starter', 'slug' => 'starter', 'description' => 'Free', 'price_monthly' => 0, 'price_yearly' => 0, 'max_students' => 10, 'max_teachers' => 2, 'max_classes' => 5, 'ai_requests_per_month' => 50, 'has_whiteboard' => true, 'has_code_editor' => false, 'has_ai_assistant' => false, 'has_attendance' => true, 'has_homework' => true, 'has_parent_reports' => false, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $plan = SubscriptionPlan::first();

        $response = $this->post('/register', [
            'school_name' => 'New Test School',
            'school_email' => 'school@test.com',
            'name' => 'Test Admin',
            'email' => 'testadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'plan_id' => $plan->id,
        ]);

        $response->assertRedirect(route('school.dashboard'));

        $this->assertDatabaseHas('schools', ['name' => 'New Test School']);
        $this->assertDatabaseHas('users', ['email' => 'testadmin@test.com']);

        $school = School::where('name', 'New Test School')->first();
        $this->assertNotNull($school->activeSubscription());
    }
}