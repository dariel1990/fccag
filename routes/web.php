<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityTypeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CellGroupController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentOfficerController;
use App\Http\Controllers\Impostor\RoomController as ImpostorRoomController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\Music\MusicMemberController;
use App\Http\Controllers\Music\ScheduleController;
use App\Http\Controllers\Music\ScheduleRoleController;
use App\Http\Controllers\Music\ServiceTypeController;
use App\Http\Controllers\Music\SetlistController;
use App\Http\Controllers\Music\SetlistSongController;
use App\Http\Controllers\Music\SongController;
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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');
Route::get('/about', fn () => Inertia::render('About'))->name('public.about');
Route::get('/history', fn () => Inertia::render('History'))->name('public.history');
Route::get('/programs', fn () => Inertia::render('Programs'))->name('public.programs');
Route::get('/news', fn () => Inertia::render('News'))->name('public.news');
Route::get('/blog', [PublicPostController::class, 'index'])->name('blog.public.index');
Route::get('/blog/{post:slug}', [PublicPostController::class, 'show'])->name('blog.public.show');

// Public share link for setlist Live view (token-protected, no auth required)
Route::get('share/setlists/{token}/live', [SetlistController::class, 'publicLive'])
    ->name('setlists.public.live');

// TEMPORARY: tail of laravel.log to debug 500s on prod.
Route::get('deploy/log', function () {
    $path = storage_path('logs/laravel.log');

    if (! file_exists($path)) {
        return response('No log file at '.$path, 200)->header('Content-Type', 'text/plain');
    }

    $contents = file_get_contents($path);
    $tail = substr($contents, -8000);

    return response($tail, 200)->header('Content-Type', 'text/plain');
});

// TEMPORARY: one-shot deploy hook. Visit /deploy/run once, then DELETE this block.
Route::get('deploy/run', function () {
    $output = [];

    // Clear stale caches first so new routes/configs are picked up.
    Artisan::call('config:clear');
    $output['config:clear'] = Artisan::output();

    Artisan::call('route:clear');
    $output['route:clear'] = Artisan::output();

    Artisan::call('view:clear');
    $output['view:clear'] = Artisan::output();

    Artisan::call('migrate', ['--force' => true]);
    $output['migrate'] = Artisan::output();

    // Check whether the impostor controller is loadable.
    $output['controller_exists'] = class_exists(\App\Http\Controllers\Impostor\RoomController::class)
        ? 'yes'
        : 'NO — file missing or wrong path/casing';

    Artisan::call('config:cache');
    $output['config:cache'] = Artisan::output();

    Artisan::call('route:cache');
    $output['route:cache'] = Artisan::output();

    return response()->json($output);
});

// Impostor game (fully public — no auth required, identity via session token)
Route::prefix('impostor')->name('impostor.')->group(function (): void {
    Route::get('/', [ImpostorRoomController::class, 'index'])->name('lobby');
    Route::post('identify', [ImpostorRoomController::class, 'setName'])->name('identify');
    Route::post('rooms', [ImpostorRoomController::class, 'store'])->name('rooms.store');
    Route::post('rooms/join', [ImpostorRoomController::class, 'join'])->name('rooms.join');
    Route::get('rooms/{code}', [ImpostorRoomController::class, 'show'])->name('rooms.show');
    Route::post('rooms/{code}/leave', [ImpostorRoomController::class, 'leave'])->name('rooms.leave');
    Route::post('rooms/{code}/start', [ImpostorRoomController::class, 'start'])->name('rooms.start');
    Route::post('rooms/{code}/open-voting', [ImpostorRoomController::class, 'openVoting'])->name('rooms.open-voting');
    Route::post('rooms/{code}/vote', [ImpostorRoomController::class, 'vote'])->name('rooms.vote');
    Route::post('rooms/{code}/resolve', [ImpostorRoomController::class, 'resolve'])->name('rooms.resolve');
    Route::post('rooms/{code}/next-round', [ImpostorRoomController::class, 'nextRound'])->name('rooms.next-round');
});

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

    // Music module
    Route::prefix('music')->name('music.')->group(function (): void {
        Route::middleware('permission:songs')->group(function (): void {
            Route::resource('songs', SongController::class)->except(['create', 'edit']);
        });

        Route::middleware('permission:setlists')->group(function (): void {
            Route::resource('setlists', SetlistController::class)->except(['create', 'edit']);
            Route::get('setlists/{setlist}/live', [SetlistController::class, 'live'])->name('setlists.live');
            Route::post('setlists/{setlist}/share', [SetlistController::class, 'enableShare'])->name('setlists.share.enable');
            Route::delete('setlists/{setlist}/share', [SetlistController::class, 'disableShare'])->name('setlists.share.disable');
            Route::post('setlists/{setlist}/songs', [SetlistSongController::class, 'store'])->name('setlist-songs.store');
            Route::patch('setlists/{setlist}/songs/{song}', [SetlistSongController::class, 'update'])->name('setlist-songs.update');
            Route::delete('setlists/{setlist}/songs/{song}', [SetlistSongController::class, 'destroy'])->name('setlist-songs.destroy');
            Route::patch('setlists/{setlist}/reorder', [SetlistSongController::class, 'reorder'])->name('setlist-songs.reorder');
        });

        Route::middleware('permission:schedules')->group(function (): void {
            Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
            Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
            Route::patch('schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
            Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
            Route::post('schedules/generate', [ScheduleController::class, 'generateMonth'])->name('schedules.generate');
            Route::patch('schedules/{schedule}/assignments', [ScheduleController::class, 'updateAssignments'])->name('schedules.assignments.update');
        });

        Route::middleware('permission:music_members')->group(function (): void {
            Route::resource('music-members', MusicMemberController::class)->except(['create', 'edit']);
        });

        Route::middleware('permission:service_types')->group(function (): void {
            Route::resource('service-types', ServiceTypeController::class)->except(['create', 'edit']);
        });

        Route::middleware('permission:schedule_roles')->group(function (): void {
            Route::resource('schedule-roles', ScheduleRoleController::class)->except(['create', 'edit']);
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
