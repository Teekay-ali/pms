<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DailyLogController;
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

});

require __DIR__.'/auth.php';
