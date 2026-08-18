<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\School;
use App\Models\SubscriptionPlan;
use App\Models\SchoolSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed ClassBridge AI.
     */
    public function run(): void
    {
        // -------- 6 Roles --------
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Platform owner - manages all schools and settings'],
            ['name' => 'School Owner', 'slug' => 'school_owner', 'description' => 'Owns one school - manages school profile and admins'],
            ['name' => 'School Admin', 'slug' => 'school_admin', 'description' => 'Manages assigned school - teachers, students, classes'],
            ['name' => 'Teacher', 'slug' => 'teacher', 'description' => 'Teaches classes, manages lessons and homework'],
            ['name' => 'Student', 'slug' => 'student', 'description' => 'Attends classes, completes homework and quizzes'],
            ['name' => 'Parent', 'slug' => 'parent', 'description' => 'Monitors child progress, attendance, and reports'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        // -------- 3 Subscription Plans --------
        $plans = [
            [
                'name' => 'Starter', 'slug' => 'starter',
                'description' => 'Perfect for small tutoring groups.',
                'price_monthly' => 0, 'price_yearly' => 0,
                'max_students' => 10, 'max_teachers' => 2, 'max_classes' => 5,
                'ai_requests_per_month' => 50,
                'has_whiteboard' => true, 'has_code_editor' => false,
                'has_ai_assistant' => false, 'has_attendance' => true,
                'has_homework' => true, 'has_parent_reports' => false,
                'is_active' => true, 'sort_order' => 1,
            ],
            [
                'name' => 'Growth', 'slug' => 'growth',
                'description' => 'Ideal for medium schools and academies.',
                'price_monthly' => 29.99, 'price_yearly' => 299.99,
                'max_students' => 100, 'max_teachers' => 20, 'max_classes' => 30,
                'ai_requests_per_month' => 500,
                'has_whiteboard' => true, 'has_code_editor' => true,
                'has_ai_assistant' => true, 'has_attendance' => true,
                'has_homework' => true, 'has_parent_reports' => true,
                'is_active' => true, 'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise', 'slug' => 'enterprise',
                'description' => 'For large schools with custom needs.',
                'price_monthly' => 99.99, 'price_yearly' => 999.99,
                'max_students' => 1000, 'max_teachers' => 100, 'max_classes' => 200,
                'ai_requests_per_month' => 5000,
                'has_whiteboard' => true, 'has_code_editor' => true,
                'has_ai_assistant' => true, 'has_attendance' => true,
                'has_homework' => true, 'has_parent_reports' => true,
                'is_active' => true, 'sort_order' => 3,
            ],
        ];
        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }

        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $schoolOwnerRole = Role::where('slug', 'school_owner')->first();
        $schoolAdminRole = Role::where('slug', 'school_admin')->first();
        $teacherRole = Role::where('slug', 'teacher')->first();
        $studentRole = Role::where('slug', 'student')->first();
        $parentRole = Role::where('slug', 'parent')->first();

        // -------- Super Admin --------
        User::firstOrCreate(
            ['email' => 'admin@classbridge.test'],
            [
                'name' => 'Platform Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'school_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // -------- Demo School --------
        $starterPlan = SubscriptionPlan::where('slug', 'starter')->first();
        $school = School::firstOrCreate(
            ['slug' => 'demo-academy'],
            [
                'name' => 'Demo Academy',
                'display_name' => 'Demo Academy',
                'email' => 'info@demoacademy.com',
                'phone' => '+2348000000000',
                'organization_type' => 'school',
                'country' => 'Nigeria',
                'state' => 'Lagos',
                'city' => 'Ikeja',
                'address' => '123 Learning Street',
                'website' => 'https://demoacademy.com',
                'status' => 'trial',
                'subscription_plan_id' => $starterPlan->id,
                'trial_ends_at' => now()->addDays(14),
                'timezone' => 'Africa/Lagos',
                'preferred_teaching_mode' => 'whiteboard',
                'settings' => ['allow_student_signup' => true, 'default_language' => 'en'],
            ]
        );

        // Create School Owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@demoacademy.com'],
            [
                'name' => 'Adebayo Okafor',
                'first_name' => 'Adebayo',
                'last_name' => 'Okafor',
                'password' => Hash::make('password'),
                'role_id' => $schoolOwnerRole->id,
                'school_id' => $school->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Set school owner
        $school->owner_user_id = $owner->id;
        $school->save();

        app(\App\Services\Organization\OrganizationOnboardingService::class)->syncSteps($school, ['organization_profile']);

        // Create School Admin
        User::firstOrCreate(
            ['email' => 'principal@demoacademy.com'],
            [
                'name' => 'Chioma Eze',
                'first_name' => 'Chioma',
                'last_name' => 'Eze',
                'password' => Hash::make('password'),
                'role_id' => $schoolAdminRole->id,
                'school_id' => $school->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create Teacher
        User::firstOrCreate(
            ['email' => 'teacher@demoacademy.com'],
            [
                'name' => 'Emeka Nwosu',
                'first_name' => 'Emeka',
                'last_name' => 'Nwosu',
                'password' => Hash::make('password'),
                'role_id' => $teacherRole->id,
                'school_id' => $school->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create Student
        User::firstOrCreate(
            ['email' => 'student@demoacademy.com'],
            [
                'name' => 'Tolu Adeyemi',
                'first_name' => 'Tolu',
                'last_name' => 'Adeyemi',
                'password' => Hash::make('password'),
                'role_id' => $studentRole->id,
                'school_id' => $school->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create Parent
        User::firstOrCreate(
            ['email' => 'parent@demoacademy.com'],
            [
                'name' => 'Grace Okonkwo',
                'first_name' => 'Grace',
                'last_name' => 'Okonkwo',
                'password' => Hash::make('password'),
                'role_id' => $parentRole->id,
                'school_id' => $school->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Trial subscription for demo school
        if (!SchoolSubscription::where('school_id', $school->id)->exists()) {
            SchoolSubscription::create([
                'school_id' => $school->id,
                'subscription_plan_id' => $starterPlan->id,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
                'starts_at' => now(),
            ]);
        }

        // -------- Private Tutor Workspace --------
        $tutorWorkspace = School::firstOrCreate(
            ['slug' => 'amina-bello-studio'],
            [
                'name' => 'Amina Bello Studio',
                'display_name' => 'Amina Bello Studio',
                'email' => 'tutor@demoacademy.com',
                'phone' => '+2348000000001',
                'organization_type' => 'private_tutor',
                'country' => 'Nigeria',
                'state' => 'Lagos',
                'city' => 'Surulere',
                'address' => 'Remote tutoring workspace',
                'website' => 'https://tutor.demoacademy.com',
                'status' => 'trial',
                'subscription_plan_id' => $starterPlan->id,
                'trial_ends_at' => now()->addDays(14),
                'timezone' => 'Africa/Lagos',
                'preferred_teaching_mode' => 'english',
                'settings' => ['allow_student_signup' => true, 'default_language' => 'en'],
            ]
        );

        $tutorOwner = User::firstOrCreate(
            ['email' => 'tutor@demoacademy.com'],
            [
                'name' => 'Amina Bello',
                'first_name' => 'Amina',
                'last_name' => 'Bello',
                'password' => Hash::make('password'),
                'role_id' => $schoolOwnerRole->id,
                'school_id' => $tutorWorkspace->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $tutorWorkspace->owner_user_id = $tutorOwner->id;
        $tutorWorkspace->save();

        if (!SchoolSubscription::where('school_id', $tutorWorkspace->id)->exists()) {
            SchoolSubscription::create([
                'school_id' => $tutorWorkspace->id,
                'subscription_plan_id' => $starterPlan->id,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
                'starts_at' => now(),
            ]);
        }

        app(\App\Services\Organization\OrganizationOnboardingService::class)->syncSteps($tutorWorkspace, ['tutor_profile']);

        // -------- Phase 4: Coding Assignments --------
        $this->call(CodingAssignmentSeeder::class);

        // -------- Phase 5: AI Teaching Assistant --------
        $this->call(AISeeder::class);

        // -------- Public Landing Page Content --------
        $this->call(LandingPageSeeder::class);

        // -------- Premium Phase 2: Gamification --------
        $this->call(GamificationSeeder::class);

        $this->command->info('ClassBridge AI seeded!');
        $this->command->info('---');
        $this->command->info('Super Admin: admin@classbridge.test / password');
        $this->command->info('School Owner: owner@demoacademy.com / password');
        $this->command->info('Tutor Owner: tutor@demoacademy.com / password');
        $this->command->info('School Admin: principal@demoacademy.com / password');
        $this->command->info('Teacher: teacher@demoacademy.com / password');
        $this->command->info('Student: student@demoacademy.com / password');
        $this->command->info('Parent: parent@demoacademy.com / password');
    }
}
