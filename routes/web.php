<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);

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
