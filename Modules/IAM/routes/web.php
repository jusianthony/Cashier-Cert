<?php

use Illuminate\Support\Facades\Route;
use Modules\IAM\Http\Controllers\RoleController;
use Modules\IAM\Http\Controllers\PermissionController;
use Modules\IAM\Http\Controllers\UserController;

Route::prefix('iam')
    ->name('iam.')
    //->middleware(['auth:sanctum']) //, 'verified', 'approved', 'locked'
    ->group(function () {
        // Role Resource
        Route::resource('roles', RoleController::class);
        // Permissions Resource
        Route::resource('permissions', PermissionController::class);
        Route::resource('users', UserController::class);
    });

Route::get('/gsis-report', [RemittedController::class, 'index'])->name('gsis.report');
Route::get('/pdf-template/{full_name}', [PdfController::class, 'generateGSISReport'])->name('pdf.template.dynamic');
