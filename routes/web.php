<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\AdminDemoRequestController;
use App\Http\Controllers\Admin\AdminLandingPageAudienceController;
use App\Http\Controllers\Admin\AdminLandingPageController;
use App\Http\Controllers\Admin\AdminLandingPageFeatureController;
use App\Http\Controllers\Admin\AdminLandingPagePricingController;
use App\Http\Controllers\Admin\AdminLandingPageSlideController;
use App\Http\Controllers\Admin\AdminWebBuilderController;
use App\Http\Controllers\Admin\SchoolManagementController;
use App\Http\Controllers\AI\AdminAIProviderController;
use App\Http\Controllers\AI\AdminAISettingsController;
use App\Http\Controllers\AI\SchoolAISettingsController;
use App\Http\Controllers\Academic\AcademicController;
use App\Http\Controllers\AI\TeacherAIAssistantController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Classroom\ClassroomApiController;
use App\Http\Controllers\Classroom\LessonJoinController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Classroom\ClassroomController;
use App\Http\Controllers\Classroom\LiveLessonController;
use App\Http\Controllers\School\CourseController;
use App\Http\Controllers\Coding\CodingController;
use App\Http\Controllers\Coding\CodingSessionApiController;
use App\Http\Controllers\Coding\CodingSessionController;
use App\Http\Controllers\Classroom\LessonReplayController;
use App\Http\Controllers\Gamification\GamificationController;
use App\Http\Controllers\School\BrandingController;
use App\Http\Controllers\School\OrganizationProfileController;
use App\Http\Controllers\Parent\ParentPortalController;
use App\Http\Controllers\Library\MaterialLibraryController;
use App\Http\Controllers\Worksheet\WorksheetsController;
use App\Http\Controllers\Dashboard\ParentDashboardController;
use App\Http\Controllers\Dashboard\SchoolAdminDashboardController;
use App\Http\Controllers\Dashboard\StudentDashboardController;
use App\Http\Controllers\Dashboard\SuperAdminDashboardController;
use App\Http\Controllers\Dashboard\TeacherDashboardController;
use App\Http\Controllers\Platform\LandingPageController;
use App\Http\Controllers\Platform\PlatformController;
use App\Models\LiveClassroom;
use App\Http\Controllers\School\ClassController;
use App\Http\Controllers\School\SubjectController;
use App\Http\Controllers\School\TeacherController as SchoolTeacherController;
use App\Http\Controllers\School\StudentController as SchoolStudentController;
use App\Http\Controllers\School\ParentController as SchoolParentController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::post('/demo-request', [LandingPageController::class, 'storeDemoRequest'])->name('demo.request');
Route::post('/request-demo', [LandingPageController::class, 'storeDemoRequest'])->name('demo.request.legacy');
Route::view('/demo-classroom', 'demo.classroom')->name('demo.classroom');
Route::view('/live-classroom-demo', 'demo.classroom')->name('demo.live-classroom');
Route::get('/join', [LessonJoinController::class, 'show'])->name('join');
Route::post('/join', [LessonJoinController::class, 'store'])->name('join.store');
Route::get('/join/{roomCode}', [LessonJoinController::class, 'showRoom'])->name('join.room');
Route::get('/classrooms/join', function () {
    return redirect()->route('join', array_filter([
        'room_code' => request()->query('room_code') ?? request()->query('join_code'),
    ]));
})->name('classrooms.join');
Route::post('/classrooms/join', [LessonJoinController::class, 'store'])->name('classrooms.join-by-code');
Route::get('/coding/join', function () {
    return redirect()->route('join', array_filter([
        'room_code' => request()->query('join_code'),
    ]));
})->name('coding.sessions.join.form');
Route::post('/coding/join', [LessonJoinController::class, 'store'])->name('coding.sessions.join');
Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->to(classbridge_dashboard_route(auth()->user()->role?->slug));
})->name('dashboard');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/no-role', fn() => view('auth.no-role'))->name('no-role');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Super Admin
    Route::prefix('admin')->name('super-admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::prefix('web-builder')->name('web-builder.')->group(function () {
            Route::get('/', [AdminWebBuilderController::class, 'index'])->name('index');
            Route::get('/blocks', [AdminWebBuilderController::class, 'sections'])->name('sections');
            Route::patch('/blocks/{section}', [AdminWebBuilderController::class, 'updateSection'])->name('sections.update');
            Route::resource('slides', AdminLandingPageSlideController::class)->except(['show']);
            Route::resource('features', AdminLandingPageFeatureController::class)->except(['show']);
            Route::resource('audiences', AdminLandingPageAudienceController::class)->except(['show']);
            Route::resource('pricing', AdminLandingPagePricingController::class)->except(['show']);
            Route::get('/demo-requests', [AdminDemoRequestController::class, 'index'])->name('demo-requests.index');
            Route::patch('/demo-requests/{demoRequest}/status', [AdminDemoRequestController::class, 'updateStatus'])->name('demo-requests.status');
        });
        Route::prefix('landing-page')->name('landing-page.')->group(function () {
            Route::get('/', [AdminLandingPageController::class, 'index'])->name('index');
            Route::get('/sections', [AdminLandingPageController::class, 'sections'])->name('sections');
            Route::patch('/sections/{section}', [AdminLandingPageController::class, 'updateSection'])->name('sections.update');
            Route::resource('slides', AdminLandingPageSlideController::class)->except(['show']);
            Route::resource('features', AdminLandingPageFeatureController::class)->except(['show']);
            Route::resource('audiences', AdminLandingPageAudienceController::class)->except(['show']);
            Route::resource('pricing', AdminLandingPagePricingController::class)->except(['show']);
        });
        Route::view('/organizations', 'components.coming-soon', [
            'title' => 'Organizations',
            'description' => 'A platform-wide organization overview for schools, tutoring centers, academies, and private tutors.',
        ])->name('organizations.index');
        Route::resource('schools', SchoolManagementController::class)->except(['show']);
        Route::put('/schools/{school}/toggle-suspend', [SchoolManagementController::class, 'toggleSuspend'])->name('schools.toggle-suspend');
        Route::view('/tutors', 'components.coming-soon', [
            'title' => 'Tutors',
            'description' => 'Private tutors and online lesson businesses will be managed here.',
        ])->name('tutors.index');
        Route::view('/users', 'components.coming-soon', ['title' => 'Users', 'description' => 'User management is coming soon.'])->name('users.index');
        Route::view('/subscription-plans', 'components.coming-soon', ['title' => 'Subscription Plans', 'description' => 'Plan management is available in Billing.'])->name('plans.index');
        Route::view('/school-subscriptions', 'components.coming-soon', ['title' => 'School Subscriptions', 'description' => 'View and manage school subscriptions here.'])->name('subscriptions.index');
        Route::view('/audit-logs', 'components.coming-soon', ['title' => 'Audit Logs', 'description' => 'Platform audit logs coming soon.'])->name('audit-logs');
        Route::view('/settings', 'components.coming-soon', ['title' => 'Platform Settings', 'description' => 'Global platform settings coming soon.'])->name('settings');
        Route::get('/demo-requests', [AdminDemoRequestController::class, 'index'])->name('demo-requests.index');
        Route::patch('/demo-requests/{demoRequest}/status', [AdminDemoRequestController::class, 'updateStatus'])->name('demo-requests.status');
        Route::get('/demo-submissions', [AdminDemoRequestController::class, 'index'])->name('demo.submissions');
        Route::patch('/demo-submissions/{demoRequest}/status', [AdminDemoRequestController::class, 'updateStatus'])->name('demo.submissions.update');
    });

    // School Management (school_owner & school_admin manage resources; teachers have limited view access)
    Route::prefix('school')->name('school.')->middleware('role:school_owner,school_admin,teacher')->group(function () {
        Route::get('/dashboard', [SchoolAdminDashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/profile', '/organization/profile')->name('profile')->middleware('role:school_owner,school_admin');
        Route::redirect('/onboarding', '/organization/onboarding')->name('onboarding')->middleware('role:school_owner,school_admin');
        Route::resource('classes', ClassController::class)->except(['show']);
        Route::get('/classes/{class}', [ClassController::class, 'show'])->name('classes.show');
        Route::post('/classes/{class}/assign-teacher', [ClassController::class, 'assignTeacher'])->name('classes.assign-teacher')->middleware('role:school_owner,school_admin');
        Route::delete('/classes/{class}/remove-teacher/{teacher}', [ClassController::class, 'removeTeacher'])->name('classes.remove-teacher')->middleware('role:school_owner,school_admin');
        Route::post('/classes/{class}/assign-student', [ClassController::class, 'assignStudent'])->name('classes.assign-student')->middleware('role:school_owner,school_admin');
        Route::delete('/classes/{class}/remove-student/{student}', [ClassController::class, 'removeStudent'])->name('classes.remove-student')->middleware('role:school_owner,school_admin');
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('teachers', SchoolTeacherController::class)->except(['store', 'update', 'destroy'])->middleware('role:school_owner,school_admin,teacher');
        Route::resource('teachers', SchoolTeacherController::class)->only(['store', 'update', 'destroy'])->middleware('role:school_owner,school_admin');
        Route::resource('students', SchoolStudentController::class)->except(['store', 'update', 'destroy'])->middleware('role:school_owner,school_admin,teacher');
        Route::resource('students', SchoolStudentController::class)->only(['store', 'update', 'destroy'])->middleware('role:school_owner,school_admin');
        Route::resource('parents', SchoolParentController::class)->middleware('role:school_owner,school_admin');
        Route::post('/parents/{parent}/link-child', [SchoolParentController::class, 'linkChild'])->name('parents.link-child')->middleware('role:school_owner,school_admin');
        Route::delete('/parents/{parent}/unlink-child/{student}', [SchoolParentController::class, 'unlinkChild'])->name('parents.unlink-child')->middleware('role:school_owner,school_admin');
        Route::redirect('/live-classrooms', '/classrooms')->name('live-classrooms');
        Route::view('/homework', 'components.coming-soon', ['title' => 'Homework', 'description' => 'Homework management coming soon.'])->name('homework');
        Route::view('/quizzes', 'components.coming-soon', ['title' => 'Quizzes', 'description' => 'Quiz management coming soon.'])->name('quizzes');
        Route::view('/reports', 'components.coming-soon', ['title' => 'Reports', 'description' => 'Reports dashboard coming soon.'])->name('reports');
        Route::view('/settings', 'components.coming-soon', ['title' => 'School Settings', 'description' => 'School settings coming soon.'])->name('settings');
    });

    Route::prefix('courses')->name('courses.')->middleware('role:school_owner,school_admin,teacher')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/create', [CourseController::class, 'create'])->name('create');
        Route::post('/', [CourseController::class, 'store'])->name('store');
        Route::get('/{course}', [CourseController::class, 'show'])->name('show');
        Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{course}', [CourseController::class, 'update'])->name('update');
        Route::patch('/{course}/archive', [CourseController::class, 'archive'])->name('archive');
        Route::post('/{course}/assign', [CourseController::class, 'assign'])->name('assign');
        Route::post('/{course}/subjects', [CourseController::class, 'storeSubject'])->name('subjects.store');
    });

    Route::prefix('organization')->name('organization.')->middleware('role:school_owner,school_admin')->group(function () {
        Route::get('/profile', [OrganizationProfileController::class, 'edit'])->name('profile');
        Route::post('/profile', [OrganizationProfileController::class, 'update'])->name('profile.update');
        Route::get('/onboarding', [PlatformController::class, 'onboarding'])->name('onboarding');
    });

    // Teacher dashboard
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/whiteboard', '/live-interactive-classroom')->name('whiteboard');
        Route::redirect('/text-pad', '/live-interactive-classroom')->name('text-pad');
        Route::view('/classes', 'components.coming-soon', ['title' => 'My Classes', 'description' => 'Manage your assigned classes.'])->name('classes');
        Route::view('/students', 'components.coming-soon', ['title' => 'My Students', 'description' => 'View and manage your students.'])->name('students');
        Route::redirect('/live-classrooms', '/classrooms')->name('live-classrooms');
        Route::redirect('/coding', '/live-interactive-classroom')->name('coding');
        Route::view('/homework', 'components.coming-soon', ['title' => 'Homework', 'description' => 'Assign and grade homework.'])->name('homework');
        Route::view('/quizzes', 'components.coming-soon', ['title' => 'Quizzes', 'description' => 'Create and manage quizzes.'])->name('quizzes');
        Route::view('/submissions', 'components.coming-soon', ['title' => 'Submissions', 'description' => 'Review student submissions.'])->name('submissions');
        Route::view('/reports', 'components.coming-soon', ['title' => 'Reports', 'description' => 'View class and student reports.'])->name('reports');
        Route::view('/library', 'components.coming-soon', ['title' => 'Material Library', 'description' => 'Browse and share teaching materials.'])->name('library');
    });

    // Student dashboard
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/whiteboard-activities', '/live-interactive-classroom')->name('whiteboard');
        Route::view('/classes', 'components.coming-soon', ['title' => 'My Classes', 'description' => 'View your enrolled classes.'])->name('classes');
        Route::redirect('/live-classrooms', '/classrooms/join')->name('live-classrooms');
        Route::view('/homework', 'components.coming-soon', ['title' => 'Homework', 'description' => 'View and submit your homework.'])->name('homework');
        Route::view('/quizzes', 'components.coming-soon', ['title' => 'Quizzes', 'description' => 'Take quizzes assigned to you.'])->name('quizzes');
        Route::view('/projects', 'components.coming-soon', ['title' => 'Coding Projects', 'description' => 'Work on your coding projects.'])->name('projects');
        Route::view('/worksheets', 'components.coming-soon', ['title' => 'Worksheets', 'description' => 'Access your worksheets.'])->name('worksheets');
        Route::view('/badges', 'components.coming-soon', ['title' => 'Badges', 'description' => 'View your earned badges.'])->name('badges');
        Route::view('/certificates', 'components.coming-soon', ['title' => 'Certificates', 'description' => 'Download your certificates.'])->name('certificates');
        Route::view('/reports', 'components.coming-soon', ['title' => 'Reports', 'description' => 'View your progress reports.'])->name('reports');
    });

    // Parent dashboard
    Route::prefix('parent')->name('parent.')->middleware('role:parent')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::view('/child-progress', 'components.coming-soon', ['title' => 'Child Progress', 'description' => 'View a friendly progress summary for your linked children.'])->name('progress');
        Route::view('/live-sessions', 'components.coming-soon', ['title' => 'Live Session Schedule', 'description' => 'The live session schedule for your children will appear here.'])->name('live-sessions');
        Route::view('/children', 'components.coming-soon', ['title' => 'My Children', 'description' => 'View your linked children.'])->name('children');
        Route::view('/homework', 'components.coming-soon', ['title' => 'Homework', 'description' => 'Track your child\'s homework.'])->name('homework');
        Route::view('/quizzes', 'components.coming-soon', ['title' => 'Quiz Scores', 'description' => 'View your child\'s quiz results.'])->name('quizzes');
        Route::view('/achievements', 'components.coming-soon', ['title' => 'Badges & Certificates', 'description' => 'View your child\'s achievements.'])->name('achievements');
        Route::view('/messages', 'components.coming-soon', ['title' => 'Messages', 'description' => 'Communicate with teachers.'])->name('messages');
        Route::view('/payments', 'components.coming-soon', ['title' => 'Payments', 'description' => 'Manage school fee payments.'])->name('payments');
    });

    // ============ PHASE 3: LIVE CLASSROOMS ============
    Route::get('/live-interactive-classroom', fn () => redirect()->route('classrooms.index'))->name('live-interactive-classroom');
    Route::middleware('role:school_owner,school_admin,teacher')->group(function () {
        Route::get('/live-lessons/create', [LiveLessonController::class, 'create'])->name('live-lessons.create');
        Route::post('/live-lessons', [LiveLessonController::class, 'store'])->name('live-lessons.store');
        Route::post('/live-lessons/courses', [LiveLessonController::class, 'storeCourse'])->name('live-lessons.courses.store');
        Route::post('/live-lessons/subjects', [LiveLessonController::class, 'storeSubject'])->name('live-lessons.subjects.store');
    });
    Route::prefix('classrooms')->name('classrooms.')->middleware('role:super_admin,school_owner,school_admin,teacher,student,parent')->group(function () {
        Route::get('/', [ClassroomController::class, 'index'])->name('index');
        Route::get('/create', fn () => redirect()->route('live-lessons.create'))->name('create')->middleware('role:school_owner,school_admin,teacher');
        Route::post('/', [LiveLessonController::class, 'store'])->name('store')->middleware('role:school_owner,school_admin,teacher');
        Route::get('/{classroom}/whiteboard', fn (LiveClassroom $classroom) => redirect()->route('classrooms.show', ['classroom' => $classroom, 'mode' => 'whiteboard']))->name('whiteboard');
        Route::get('/{classroom}/coding', fn (LiveClassroom $classroom) => redirect()->route('classrooms.show', ['classroom' => $classroom, 'mode' => 'coding']))->name('coding');
        Route::get('/{classroom}/text-pad', fn (LiveClassroom $classroom) => redirect()->route('classrooms.show', ['classroom' => $classroom, 'mode' => 'text']))->name('text-pad');
        Route::get('/{classroom}/mathematics', fn (LiveClassroom $classroom) => redirect()->route('classrooms.show', ['classroom' => $classroom, 'mode' => 'mathematics']))->name('mathematics');
        Route::get('/{classroom}/presentation', fn (LiveClassroom $classroom) => redirect()->route('classrooms.show', ['classroom' => $classroom, 'mode' => 'presentation']))->name('presentation');
        Route::get('/{classroom}', [ClassroomController::class, 'show'])->name('show');
        Route::post('/{classroom}/start', [ClassroomController::class, 'startSession'])->name('start-session');
        Route::post('/{classroom}/end', [ClassroomController::class, 'endSession'])->name('end-session');
    });

    Route::prefix('api/classroom/{session}')->name('api.classroom.')->middleware('auth')->group(function () {
        Route::post('/whiteboard', [ClassroomApiController::class, 'saveWhiteboardElement'])->name('whiteboard.save');
        Route::get('/whiteboard', [ClassroomApiController::class, 'fetchWhiteboard'])->name('whiteboard.fetch');
        Route::delete('/whiteboard', [ClassroomApiController::class, 'clearWhiteboard'])->name('whiteboard.clear');
        Route::get('/whiteboard/snapshots', [ClassroomApiController::class, 'listWhiteboardSnapshots'])->name('whiteboard.snapshots.index');
        Route::post('/whiteboard/snapshots', [ClassroomApiController::class, 'createWhiteboardSnapshot'])->name('whiteboard.snapshots.store');
        Route::post('/whiteboard/snapshots/{snapshot}/restore', [ClassroomApiController::class, 'restoreWhiteboardSnapshot'])->name('whiteboard.snapshots.restore');
        Route::post('/textpad', [ClassroomApiController::class, 'saveTextPad'])->name('textpad.save');
        Route::get('/textpad', [ClassroomApiController::class, 'fetchTextPad'])->name('textpad.fetch');
        Route::get('/code', [ClassroomApiController::class, 'fetchCode'])->name('code.fetch');
        Route::post('/code', [ClassroomApiController::class, 'saveCode'])->name('code.save');
        Route::post('/save-session', [ClassroomApiController::class, 'saveSession'])->name('session.save');
        Route::post('/messages', [ClassroomApiController::class, 'sendMessage'])->name('messages.send');
        Route::get('/messages', [ClassroomApiController::class, 'fetchMessages'])->name('messages.fetch');
        Route::post('/pointer', [ClassroomApiController::class, 'updatePointer'])->name('pointer.update');
        Route::get('/pointers', [ClassroomApiController::class, 'fetchPointers'])->name('pointers.fetch');
        Route::get('/participants', [ClassroomApiController::class, 'fetchParticipants'])->name('participants.fetch');
        Route::post('/leave', [ClassroomApiController::class, 'leave'])->name('leave');
        Route::post('/permissions', [ClassroomApiController::class, 'updatePermissions'])->name('permissions.update');
        Route::post('/mode', [ClassroomApiController::class, 'updateMode'])->name('mode.update');
        Route::get('/state', [ClassroomApiController::class, 'sessionState'])->name('state');
    });

    Route::prefix('coding')->name('coding.sessions.')->middleware('role:super_admin,school_owner,school_admin,teacher,student,parent')->group(function () {
        Route::get('/sessions/{session}', [CodingSessionController::class, 'show'])->name('show');
        Route::post('/sessions/{session}/start', [CodingSessionController::class, 'start'])->name('start')->middleware('role:super_admin,school_owner,school_admin,teacher');
        Route::post('/sessions/{session}/end', [CodingSessionController::class, 'end'])->name('end')->middleware('role:super_admin,school_owner,school_admin,teacher');
    });

    Route::prefix('api/coding-sessions/{session}')->name('api.coding.')->middleware('auth')->group(function () {
        Route::get('/state', [CodingSessionApiController::class, 'state'])->name('state');
        Route::post('/files/{file}', [CodingSessionApiController::class, 'saveFile'])->name('files.save');
        Route::post('/files', [CodingSessionApiController::class, 'createFile'])->name('files.create');
        Route::post('/files/active', [CodingSessionApiController::class, 'setActiveFile'])->name('files.active');
        Route::post('/messages', [CodingSessionApiController::class, 'sendMessage'])->name('messages.send');
        Route::get('/messages', [CodingSessionApiController::class, 'fetchMessages'])->name('messages.fetch');
        Route::post('/cursor', [CodingSessionApiController::class, 'updateCursor'])->name('cursor.update');
        Route::post('/permissions', [CodingSessionApiController::class, 'updatePermissions'])->name('permissions.update');
        Route::post('/control/take', [CodingSessionApiController::class, 'takeControl'])->name('control.take');
        Route::post('/control/release', [CodingSessionApiController::class, 'releaseControl'])->name('control.release');
        Route::post('/highlight', [CodingSessionApiController::class, 'highlightSelection'])->name('highlight.store');
        Route::post('/raise-hand', [CodingSessionApiController::class, 'raiseHand'])->name('hands.raise');
        Route::post('/request-help', [CodingSessionApiController::class, 'requestHelp'])->name('help.request');
        Route::post('/lesson-steps', [CodingSessionApiController::class, 'updateLessonSteps'])->name('lesson-steps.update');
        Route::post('/submit', [CodingSessionApiController::class, 'submitWork'])->name('submit');
        Route::post('/save-session', [CodingSessionApiController::class, 'saveSession'])->name('session.save');
        Route::post('/leave', [CodingSessionApiController::class, 'leave'])->name('leave');
    });

    // ============ PHASE 4: CODING CLASSROOM ============
    Route::prefix('coding/assignments')->name('coding.assignments.')->middleware('role:super_admin,school_owner,school_admin,teacher')->group(function () {
        Route::get('/', [CodingController::class, 'assignments'])->name('index');
        Route::get('/create', [CodingController::class, 'createAssignment'])->name('create');
        Route::post('/', [CodingController::class, 'storeAssignment'])->name('store');
        Route::get('/{assignment}/edit', [CodingController::class, 'editAssignment'])->name('edit');
        Route::put('/{assignment}', [CodingController::class, 'updateAssignment'])->name('update');
        Route::delete('/{assignment}', [CodingController::class, 'deleteAssignment'])->name('destroy');
        Route::get('/{assignment}/preview', [CodingController::class, 'previewAssignment'])->name('preview');
    });
    Route::get('/coding/review/{assignment}', [CodingController::class, 'reviewSubmissions'])->name('coding.review')->middleware('role:super_admin,school_owner,school_admin,teacher');
    Route::get('/coding/submission/{submission}', [CodingController::class, 'viewSubmission'])->name('coding.submission.view')->middleware('role:super_admin,school_owner,school_admin,teacher');
    Route::post('/coding/submission/{submission}/review', [CodingController::class, 'saveReview'])->name('coding.submission.review')->middleware('role:super_admin,school_owner,school_admin,teacher');
    Route::get('/coding/workspace/{assignment}', [CodingController::class, 'workspace'])->name('coding.workspace')->middleware('role:super_admin,school_owner,school_admin,teacher,student');
    Route::get('/coding/my-submissions', [CodingController::class, 'mySubmissions'])->name('coding.my-submissions')->middleware('role:student');
    Route::post('/coding/projects/{project}/save', [CodingController::class, 'saveProject'])->name('coding.save')->middleware('role:super_admin,school_owner,school_admin,teacher,student');
    Route::post('/coding/projects/{project}/reset', [CodingController::class, 'resetToStarter'])->name('coding.reset')->middleware('role:super_admin,school_owner,school_admin,teacher,student');
    Route::post('/coding/submission/{submission}/submit', [CodingController::class, 'submitAssignment'])->name('coding.submit')->middleware('role:super_admin,school_owner,school_admin,teacher,student');
    Route::get('/coding/progress', [CodingController::class, 'progressReport'])->name('coding.progress')->middleware('role:super_admin,school_owner,school_admin');

    // ============ PHASE 5: AI TEACHING ASSISTANT ============
    Route::prefix('admin/ai')->name('ai.admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/providers', [AdminAIProviderController::class, 'index'])->name('providers.index');
        Route::get('/providers/create', [AdminAIProviderController::class, 'create'])->name('providers.create');
        Route::post('/providers', [AdminAIProviderController::class, 'store'])->name('providers.store');
        Route::get('/providers/{provider}/edit', [AdminAIProviderController::class, 'edit'])->name('providers.edit');
        Route::put('/providers/{provider}', [AdminAIProviderController::class, 'update'])->name('providers.update');
        Route::post('/providers/{provider}/set-default', [AdminAIProviderController::class, 'setDefault'])->name('providers.set-default');
        Route::post('/providers/{provider}/test', [AdminAIProviderController::class, 'test'])->name('providers.test');
        Route::post('/providers/{provider}/toggle-status', [AdminAIProviderController::class, 'toggleStatus'])->name('providers.toggle-status');
        Route::get('/settings', [AdminAISettingsController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminAISettingsController::class, 'updateSettings'])->name('settings.update');
        Route::put('/settings/school/{school}', [AdminAISettingsController::class, 'updateSchoolSettings'])->name('settings.update-school');
        Route::get('/usage', [AdminAISettingsController::class, 'usage'])->name('usage');
        Route::get('/history', [AdminAISettingsController::class, 'history'])->name('history');
    });

    Route::prefix('school/ai')->name('ai.school.')->middleware('role:school_owner,school_admin')->group(function () {
        Route::get('/settings', [SchoolAISettingsController::class, 'settings'])->name('settings');
        Route::put('/settings', [SchoolAISettingsController::class, 'updateSettings'])->name('settings.update');
        Route::get('/usage', [SchoolAISettingsController::class, 'usage'])->name('usage');
        Route::get('/history', [SchoolAISettingsController::class, 'history'])->name('history');
    });

    Route::prefix('teacher/ai-assistant')->name('ai.teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/', [TeacherAIAssistantController::class, 'index'])->name('index');
        Route::post('/generate', [TeacherAIAssistantController::class, 'generate'])->name('generate');
        Route::post('/save', [TeacherAIAssistantController::class, 'save'])->name('save');
        Route::get('/history', [TeacherAIAssistantController::class, 'history'])->name('history');
    });

    // ============ PHASE 6: ACADEMIC ACTIVITIES ============
    Route::prefix('academic/homeworks')->name('academic.homeworks.')->middleware('role:school_owner,school_admin,teacher')->group(function () {
        Route::get('/', [AcademicController::class, 'homeworks'])->name('index');
        Route::get('/create', [AcademicController::class, 'createHomework'])->name('create');
        Route::post('/', [AcademicController::class, 'storeHomework'])->name('store');
        Route::get('/{homework}', [AcademicController::class, 'showHomework'])->name('show');
        Route::post('/submission/{submission}/grade', [AcademicController::class, 'gradeSubmission'])->name('grade');
    });
    Route::get('/my-homework', [AcademicController::class, 'myHomework'])->name('academic.my-homework')->middleware('role:student');
    Route::post('/homework/{homework}/submit', [AcademicController::class, 'submitHomework'])->name('academic.homeworks.submit')->middleware('role:student');

    Route::prefix('academic/quizzes')->name('academic.quizzes.')->middleware('role:school_owner,school_admin,teacher')->group(function () {
        Route::get('/', [AcademicController::class, 'quizzes'])->name('index');
        Route::get('/create', [AcademicController::class, 'createQuiz'])->name('create');
        Route::post('/', [AcademicController::class, 'storeQuiz'])->name('store');
        Route::get('/{quiz}/questions', [AcademicController::class, 'quizQuestions'])->name('questions');
        Route::post('/{quiz}/questions', [AcademicController::class, 'storeQuestion'])->name('questions.store');
        Route::get('/{quiz}/attempts', [AcademicController::class, 'quizAttempts'])->name('attempts');
    });
    Route::get('/quiz/{quiz}/take', [AcademicController::class, 'takeQuiz'])->name('academic.quizzes.take')->middleware('role:student');
    Route::post('/quiz/{quiz}/submit', [AcademicController::class, 'submitQuiz'])->name('academic.quizzes.submit')->middleware('role:student');
    Route::get('/quiz/{quiz}/result', [AcademicController::class, 'quizResult'])->name('academic.quizzes.result')->middleware('role:student');

    Route::prefix('academic/attendance')->name('academic.attendance.')->middleware('role:school_owner,school_admin,teacher')->group(function () {
        Route::get('/', [AcademicController::class, 'attendance'])->name('index');
        Route::post('/mark', [AcademicController::class, 'markAttendance'])->name('mark');
    });
    Route::get('/my-children-attendance', [AcademicController::class, 'attendance'])->name('academic.attendance.parent')->middleware('role:parent');

    Route::prefix('academic/feedback')->name('academic.feedback.')->middleware('role:school_owner,school_admin,teacher,parent')->group(function () {
        Route::get('/', [AcademicController::class, 'feedback'])->name('index');
        Route::post('/', [AcademicController::class, 'storeFeedback'])->name('store')->middleware('role:school_owner,school_admin,teacher');
    });

    Route::prefix('academic/reports')->name('academic.reports.')->middleware('role:school_owner,school_admin,teacher,parent')->group(function () {
        Route::get('/', [AcademicController::class, 'reports'])->name('index');
        Route::post('/generate', [AcademicController::class, 'generateReport'])->name('generate')->middleware('role:school_owner,school_admin,teacher');
        Route::get('/{report}', [AcademicController::class, 'showReport'])->name('show');
        Route::post('/{report}/publish', [AcademicController::class, 'publishReport'])->name('publish')->middleware('role:school_owner,school_admin,teacher');
    });

    // ============ PREMIUM PHASE 5: PARENT PORTAL ============
    Route::prefix('parent-portal')->name('parent-portal.')->middleware('role:parent')->group(function () {
        Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/report/{report}', [ParentPortalController::class, 'viewReport'])->name('report.view');
    });

    // Teacher/Admin: generate smart report
    Route::post('/parent-portal/generate-report/{student}', [ParentPortalController::class, 'generateReport'])->name('parent-portal.generate-report')->middleware('role:teacher,school_owner,school_admin');
    Route::post('/parent-portal/publish-report/{report}', [ParentPortalController::class, 'publishReport'])->name('parent-portal.publish-report')->middleware('role:teacher,school_owner,school_admin');

    // ============ PREMIUM PHASE 6: LESSON REPLAY ============
    Route::prefix('lesson-replays')->name('lesson-replays.')->middleware('role:teacher,school_owner,school_admin,student,parent')->group(function () {
        Route::get('/', [LessonReplayController::class, 'index'])->name('index');
        Route::get('/timeline/{session}', [LessonReplayController::class, 'timeline'])->name('timeline');
        Route::post('/generate/{session}', [LessonReplayController::class, 'generate'])->name('generate')->middleware('role:teacher,school_owner,school_admin');
        Route::get('/{replay}', [LessonReplayController::class, 'show'])->name('show');
        Route::put('/{replay}', [LessonReplayController::class, 'update'])->name('update')->middleware('role:teacher,school_owner,school_admin');
        Route::delete('/{replay}', [LessonReplayController::class, 'destroy'])->name('destroy')->middleware('role:teacher,school_owner,school_admin');
    });

    // ============ PREMIUM PHASE 8: INTERACTIVE WORKSHEETS ============
    Route::prefix('worksheets')->name('worksheets.')->middleware('role:teacher,school_owner,school_admin')->group(function () {
        Route::get('/', [WorksheetsController::class, 'index'])->name('index');
        Route::get('/create', [WorksheetsController::class, 'create'])->name('create');
        Route::post('/', [WorksheetsController::class, 'store'])->name('store');
        Route::post('/ai-generate', [WorksheetsController::class, 'generateAI'])->name('ai-generate');
        Route::get('/{worksheet}/attempts', [WorksheetsController::class, 'attempts'])->name('attempts');
        Route::post('/attempt/{attempt}/grade', [WorksheetsController::class, 'grade'])->name('grade');
    });
    Route::get('/student-worksheets', [WorksheetsController::class, 'studentIndex'])->name('worksheets.student')->middleware('role:student');
    Route::get('/student-worksheets/{worksheet}', [WorksheetsController::class, 'attempt'])->name('worksheets.attempt')->middleware('role:student');
    Route::post('/student-worksheets/submit/{attempt}', [WorksheetsController::class, 'submit'])->name('worksheets.submit')->middleware('role:student');

    // ============ PREMIUM PHASE 9: TEACHING MATERIAL LIBRARY ============
    Route::prefix('library')->name('library.')->middleware('role:teacher,school_owner,school_admin')->group(function () {
        Route::get('/', [MaterialLibraryController::class, 'index'])->name('index');
        Route::post('/folders', [MaterialLibraryController::class, 'createFolder'])->name('folders.create');
        Route::post('/materials', [MaterialLibraryController::class, 'store'])->name('materials.store');
        Route::post('/save-from-ai', [MaterialLibraryController::class, 'saveFromAI'])->name('materials.save-from-ai');
        Route::get('/templates', [MaterialLibraryController::class, 'templates'])->name('templates');
        Route::post('/templates', [MaterialLibraryController::class, 'storeTemplate'])->name('templates.store');
    });

    // ============ PREMIUM PHASE 7: BRANDING & CERTIFICATES ============
    // School branding
    Route::get('/school/branding', [BrandingController::class, 'branding'])->name('school.branding')->middleware('role:school_owner,school_admin');
    Route::post('/school/branding', [BrandingController::class, 'updateBranding'])->name('school.branding.update')->middleware('role:school_owner,school_admin');
    Route::get('/school/domains', [BrandingController::class, 'domains'])->name('school.domains')->middleware('role:school_owner,school_admin');
    Route::post('/school/domains', [BrandingController::class, 'requestDomain'])->name('school.domains.request')->middleware('role:school_owner,school_admin');
    // Certificates
    Route::get('/school/certificates', [BrandingController::class, 'certificates'])->name('school.certificates')->middleware('role:school_owner,school_admin,teacher');
    Route::post('/school/certificates/issue', [BrandingController::class, 'issueCertificate'])->name('school.certificates.issue')->middleware('role:school_owner,school_admin,teacher');
    Route::post('/school/certificates/{cert}/revoke', [BrandingController::class, 'revokeCertificate'])->name('school.certificates.revoke')->middleware('role:school_owner,school_admin');
    // Public verification
    Route::get('/verify-certificate', [BrandingController::class, 'verifyCertificate'])->name('certificate.verify');

    // ============ PREMIUM PHASE 2: GAMIFICATION ============

    // Teacher: award badges/points + leaderboard
    Route::post('/gamification/award', [GamificationController::class, 'award'])->name('gamification.award')->middleware('role:school_owner,school_admin,teacher');
    Route::get('/gamification/leaderboard', [GamificationController::class, 'leaderboard'])->name('gamification.leaderboard')->middleware('role:school_owner,school_admin,teacher');

    // Student: my progress
    Route::get('/gamification/my-progress', [GamificationController::class, 'myProgress'])->name('gamification.my-progress')->middleware('role:student');

    // Parent: view child progress
    Route::get('/gamification/child/{student}', [GamificationController::class, 'childProgress'])->name('gamification.child-progress')->middleware('role:parent');

    // ============ PHASE 7: BILLING & SUBSCRIPTIONS ============
    Route::prefix('admin/billing')->name('billing.admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/plans', [BillingController::class, 'plans'])->name('plans');
        Route::get('/plans/create', [BillingController::class, 'createPlan'])->name('plans.create');
        Route::post('/plans', [BillingController::class, 'storePlan'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [BillingController::class, 'editPlan'])->name('plans.edit');
        Route::put('/plans/{plan}', [BillingController::class, 'updatePlan'])->name('plans.update');
        Route::get('/subscriptions', [BillingController::class, 'subscriptions'])->name('subscriptions');
        Route::post('/subscriptions/assign', [BillingController::class, 'assignPlan'])->name('subscriptions.assign');
        Route::get('/invoices', [BillingController::class, 'invoices'])->name('invoices');
        Route::get('/payments', [BillingController::class, 'payments'])->name('payments');
        Route::post('/payments/{payment}/mark-paid', [BillingController::class, 'markPaymentPaid'])->name('payments.mark-paid');
    });

    // ============ PAYMENT GATEWAY (PAYSTACK) ============
    Route::prefix('school/billing')->middleware('role:school_owner,school_admin')->group(function () {
        Route::get('/', [BillingController::class, 'schoolBilling'])->name('billing.school');
        Route::get('/plans', [PaymentController::class, 'showPlans'])->name('payment.plans');
        Route::post('/checkout/{plan}', [PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::get('/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    });
});
// Webhook route (no auth, no CSRF)
Route::post('/webhooks/paystack', [PaymentController::class, 'webhook'])->name('payment.webhook');
