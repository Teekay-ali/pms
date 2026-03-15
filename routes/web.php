<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\HR\DepartmentController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/activity', [App\Http\Controllers\ActivityController::class, 'index'])->name('activity.index');

    // Settings (alias for profile)
    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings');


    // Discussions
    Route::get('/discussions', [DiscussionController::class, 'index'])->name('discussions.index');
    Route::post('/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
    Route::get('/discussions/{discussion}', [DiscussionController::class, 'show'])->name('discussions.show');
    Route::put('/discussions/{discussion}', [DiscussionController::class, 'update'])->name('discussions.update');
    Route::delete('/discussions/{discussion}', [DiscussionController::class, 'destroy'])->name('discussions.destroy');
    Route::post('/discussions/{discussion}/pin', [DiscussionController::class, 'pin'])->name('discussions.pin');
    Route::post('/discussions/{discussion}/lock', [DiscussionController::class, 'lock'])->name('discussions.lock');
    Route::post('/discussions/{discussion}/replies', [DiscussionController::class, 'storeReply'])->name('discussions.replies.store');
    Route::delete('/discussions/{discussion}/replies/{reply}', [DiscussionController::class, 'destroyReply'])->name('discussions.replies.destroy');
    Route::post('/discussions/react/{type}/{id}', [DiscussionController::class, 'react'])->name('discussions.react');

    // Punch List
    Route::post('/projects/{project}/punch-list', [App\Http\Controllers\PunchListController::class, 'store'])->name('punch-list.store');
    Route::put('/projects/{project}/punch-list/{item}', [App\Http\Controllers\PunchListController::class, 'update'])->name('punch-list.update');
    Route::delete('/projects/{project}/punch-list/{item}', [App\Http\Controllers\PunchListController::class, 'destroy'])->name('punch-list.destroy');

    // Daily Logs
    Route::post('/projects/{project}/daily-logs', [DailyLogController::class, 'store'])->name('daily-logs.store');
    Route::put('/projects/{project}/daily-logs/{dailyLog}', [DailyLogController::class, 'update'])->name('daily-logs.update');
    Route::delete('/projects/{project}/daily-logs/{dailyLog}', [DailyLogController::class, 'destroy'])->name('daily-logs.destroy');
    Route::get('/projects/{project}/weather', [DailyLogController::class, 'fetchWeather'])->name('projects.weather');

    // Attachments
    Route::post('/attachments/{modelType}/{modelId}', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.members.add');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.members.remove');

    // Tasks
    Route::resource('tasks', TaskController::class);

    // Resources / Inventory
    Route::resource('resources', ResourceController::class)->only(['index', 'store', 'update', 'destroy']);

    // Expenses
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::patch('/expenses/{expense}/reject',  [ExpenseController::class, 'reject'])->name('expenses.reject');

    // Vendors
    Route::resource('vendors', VendorController::class)->only(['index', 'store', 'update', 'destroy']);

    // Users & Role Management
    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');

    // HR Module
    Route::prefix('hr')->name('hr.')->group(function () {
        // Departments
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Employees
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::post('/employees/{employee}/photo', [EmployeeController::class, 'uploadPhoto'])->name('employees.photo');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        // Leave
        Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
        Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
        Route::post('/leave/{leave}/approve', [LeaveController::class, 'approve'])->name('leave.approve');
        Route::post('/leave/{leave}/reject', [LeaveController::class, 'reject'])->name('leave.reject');
        Route::delete('/leave/{leave}', [LeaveController::class, 'destroy'])->name('leave.destroy');
    });

});

require __DIR__.'/auth.php';
