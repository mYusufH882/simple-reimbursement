<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManagerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==========================================
// PUBLIC ROUTES (No Authentication Required)
// ==========================================

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// ==========================================
// PROTECTED ROUTES (Authentication Required)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // ==========================================
    // AUTH MANAGEMENT ROUTES
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // ==========================================
    // REIMBURSEMENT ROUTES
    // ==========================================
    Route::prefix('reimbursements')->group(function () {

        // Public reimbursement routes (all authenticated users)
        Route::get('/', [ReimbursementController::class, 'index']);
        Route::get('category-usage', [ReimbursementController::class, 'getCategoryUsage']);
        Route::post('/', [ReimbursementController::class, 'store']);

        // Routes with ownership validation (employees can only access their own)
        Route::middleware('owner')->group(function () {
            Route::get('{id}', [ReimbursementController::class, 'show']);
            Route::put('{id}', [ReimbursementController::class, 'update']);
            Route::delete('{id}', [ReimbursementController::class, 'destroy']);
        });

        // Manager-only routes for approval/rejection
        Route::middleware('role:manager,admin')->group(function () {
            Route::post('{id}/approve-reject', [ReimbursementController::class, 'approveReject']);
        });
    });

    // ==========================================
    // CATEGORY ROUTES
    // ==========================================
    Route::prefix('categories')->group(function () {

        // Public category routes (all authenticated users can view)
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('{id}', [CategoryController::class, 'show']);
        Route::get('{id}/check-limit', [CategoryController::class, 'checkLimit']);

        // Admin/Manager-only routes for statistics
        Route::middleware('role:manager,admin')->group(function () {
            Route::get('statistics', [CategoryController::class, 'getAllStatistics']);
            Route::get('{id}/statistics', [CategoryController::class, 'getStatistics']);
        });

        // Admin-only routes for CRUD operations
        Route::middleware('role:admin')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('{id}', [CategoryController::class, 'update']);
            Route::delete('{id}', [CategoryController::class, 'destroy']);
        });
    });

    // ==========================================
    // ADMIN ROUTES
    // ==========================================
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Dashboard
        Route::get('dashboard', [AdminController::class, 'getDashboard']);

        // User management routes
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'getUsers']);
            Route::post('/', [AdminController::class, 'createUser']);
            Route::get('{id}', [AdminController::class, 'getUser']);
            Route::put('{id}', [AdminController::class, 'updateUser']);
            Route::put('{id}/role', [AdminController::class, 'updateUserRole']);
            Route::post('{id}/reset-password', [AdminController::class, 'resetUserPassword']);
            Route::delete('{id}', [AdminController::class, 'deleteUser']);
        });
    });

    // ==========================================
    // MANAGER ROUTES
    // ==========================================
    Route::middleware('role:manager,admin')->prefix('manager')->group(function () {

        // Dashboard
        Route::get('dashboard', [ManagerController::class, 'getDashboard']);

        // Reimbursement management
        Route::get('pending-reimbursements', [ManagerController::class, 'getPendingReimbursements']);
        Route::get('reimbursements', [ManagerController::class, 'getAllReimbursements']);
        Route::get('statistics', [ManagerController::class, 'getStatistics']);

        // Users list (for filtering purposes)
        Route::get('users', [ManagerController::class, 'getUsers']);
        Route::get('users/{id}', [ManagerController::class, 'getUserDetail']);
    });

    // Get current user info (legacy support)
    Route::get('user', function (Request $request) {
        return $request->user();
    });
});

// ==========================================
// FALLBACK ROUTES
// ==========================================

// 404 for unmatched API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'auth' => [
                'POST /api/auth/register',
                'POST /api/auth/login',
                'GET /api/auth/me',
                'PUT /api/auth/profile',
                'POST /api/auth/logout'
            ],
            'reimbursements' => [
                'GET /api/reimbursements',
                'POST /api/reimbursements',
                'GET /api/reimbursements/{id}',
                'PUT /api/reimbursements/{id}',
                'POST /api/reimbursements/{id}/approve-reject'
            ],
            'categories' => [
                'GET /api/categories',
                'GET /api/categories/{id}',
                'POST /api/categories (admin)',
                'PUT /api/categories/{id} (admin)',
                'GET /api/categories/statistics (manager+)'
            ],
            'admin' => [
                'GET /api/admin/dashboard',
                'GET /api/admin/users',
                'POST /api/admin/users',
                'PUT /api/admin/users/{id}',
                'DELETE /api/admin/users/{id}'
            ],
            'manager' => [
                'GET /api/manager/dashboard',
                'GET /api/manager/pending-reimbursements',
                'GET /api/manager/statistics'
            ]
        ]
    ], 404);
});
