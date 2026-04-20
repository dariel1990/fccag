<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityTypeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CellGroupController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentOfficerController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PastorController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Si\SiActivityCategoryController;
use App\Http\Controllers\Si\SiActivityController;
use App\Http\Controllers\Si\SiAttendanceController;
use App\Http\Controllers\Si\SiMemberController;
use App\Http\Controllers\Si\SiReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public frontend disabled — redirect to login
Route::get('/', fn () => redirect()->route('login'))->name('home');

// Route::get('/about', fn () => Inertia::render('About'))->name('public.about');
// Route::get('/history', fn () => Inertia::render('History'))->name('public.history');
// Route::get('/programs', fn () => Inertia::render('Programs'))->name('public.programs');
// Route::get('/news', fn () => Inertia::render('News'))->name('public.news');
// Route::get('/blog', [PublicPostController::class, 'index'])->name('blog.public.index');
// Route::get('/blog/{post:slug}', [PublicPostController::class, 'show'])->name('blog.public.show');

// All admin routes require authentication
Route::middleware(['auth:sanctum,web', 'verified'])->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('superadmin')->group(function (): void {
        Route::resource('users', \App\Http\Controllers\UserController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware('permission:activity_types')->group(function (): void {
        Route::resource('activity-types', ActivityTypeController::class)->except(['show', 'create', 'edit']);
    });

    Route::middleware('permission:participants')->group(function (): void {
        Route::resource('participants', ParticipantController::class)->except(['create', 'edit']);
    });

    Route::middleware('permission:activities')->group(function (): void {
        Route::resource('activities', ActivityController::class)->except(['create', 'edit']);
        Route::post('activities/{activity}/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('reports/quarterly', [ReportController::class, 'quarterlyReport'])->name('reports.quarterly');
    });

    Route::middleware('permission:cell_groups')->group(function (): void {
        Route::resource('cell-groups', CellGroupController::class)->except(['show', 'create', 'edit']);
    });

    Route::middleware('permission:classifications')->group(function (): void {
        Route::resource('classifications', ClassificationController::class)->except(['show', 'create', 'edit']);
    });

    Route::middleware('permission:ministries')->group(function (): void {
        Route::resource('ministries', MinistryController::class)->except(['show', 'create', 'edit']);
    });

    Route::middleware('permission:departments')->group(function (): void {
        Route::resource('departments', DepartmentController::class)->except(['create', 'edit']);
        Route::post('departments/{department}/officers', [DepartmentOfficerController::class, 'store'])->name('department-officers.store');
        Route::put('departments/{department}/officers/{officer}', [DepartmentOfficerController::class, 'update'])->name('department-officers.update');
        Route::delete('departments/{department}/officers/{officer}', [DepartmentOfficerController::class, 'destroy'])->name('department-officers.destroy');
    });

    Route::middleware('permission:pastors')->group(function (): void {
        Route::resource('pastors', PastorController::class)->except(['show', 'create', 'edit']);
    });

    // SI (Survival Intervention) module
    Route::prefix('si')->name('si.')->group(function (): void {
        Route::middleware('permission:si_activity_categories')->group(function (): void {
            Route::resource('activity-categories', SiActivityCategoryController::class)->except(['show'])->names('activity-categories');
        });

        Route::middleware('permission:si_members')->group(function (): void {
            Route::resource('members', SiMemberController::class)->except(['show'])->names('members');
            Route::get('members/{member}/details', [SiMemberController::class, 'details'])->name('members.details');
            Route::patch('members/{member}/assessments', [SiMemberController::class, 'updateAssessments'])->name('members.assessments.update');
        });

        Route::middleware('permission:si_activities')->group(function (): void {
            Route::resource('activities', SiActivityController::class)->names('activities');
            Route::post('activities/{siActivity}/attendance', [SiAttendanceController::class, 'store'])->name('attendance.store');
            Route::get('reports', [SiReportController::class, 'index'])->name('reports.index');
        });
    });

    // Blog / post admin management (under /posts to avoid URL conflict with public /blog)
    Route::middleware('permission:posts')->group(function (): void {
        Route::get('posts', [PostController::class, 'index'])->name('blog.index');
        Route::get('posts/create', [PostController::class, 'create'])->name('blog.create');
        Route::post('posts', [PostController::class, 'store'])->name('blog.store');
        Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('blog.edit');
        Route::put('posts/{post}', [PostController::class, 'update'])->name('blog.update');
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('blog.destroy');
    });
});

require __DIR__.'/settings.php';
