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
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public landing site routes
Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::get('/about', fn () => Inertia::render('About'))->name('public.about');
Route::get('/history', fn () => Inertia::render('History'))->name('public.history');
Route::get('/programs', fn () => Inertia::render('Programs'))->name('public.programs');
Route::get('/news', fn () => Inertia::render('News'))->name('public.news');

// Public blog routes
Route::get('/blog', [PublicPostController::class, 'index'])->name('blog.public.index');
Route::get('/blog/{post:slug}', [PublicPostController::class, 'show'])->name('blog.public.show');

// All admin routes require authentication
Route::middleware(['auth:sanctum,web', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('activity-types', ActivityTypeController::class)->except(['show']);

    Route::resource('participants', ParticipantController::class);

    Route::resource('activities', ActivityController::class);
    Route::post('activities/{activity}/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('reports/quarterly', [ReportController::class, 'quarterlyReport'])->name('reports.quarterly');

    Route::resource('cell-groups', CellGroupController::class)->except(['show']);

    Route::resource('classifications', ClassificationController::class)->except(['show']);

    Route::resource('ministries', MinistryController::class)->except(['show']);

    Route::resource('departments', DepartmentController::class);
    Route::post('departments/{department}/officers', [DepartmentOfficerController::class, 'store'])->name('department-officers.store');
    Route::put('departments/{department}/officers/{officer}', [DepartmentOfficerController::class, 'update'])->name('department-officers.update');
    Route::delete('departments/{department}/officers/{officer}', [DepartmentOfficerController::class, 'destroy'])->name('department-officers.destroy');

    Route::resource('pastors', PastorController::class)->except(['show']);

    // Blog / post admin management (under /posts to avoid URL conflict with public /blog)
    Route::get('posts', [PostController::class, 'index'])->name('blog.index');
    Route::get('posts/create', [PostController::class, 'create'])->name('blog.create');
    Route::post('posts', [PostController::class, 'store'])->name('blog.store');
    Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('blog.edit');
    Route::put('posts/{post}', [PostController::class, 'update'])->name('blog.update');
    Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('blog.destroy');
});

require __DIR__.'/settings.php';
