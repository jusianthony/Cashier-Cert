<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\Http\Controllers\PdfController;
use App\Http\Controllers\RemittedController;
use App\Models\CRemitted;

// Main Remitted Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Show table
    Route::get('/remitted', [RemittedController::class, 'index'])->name('remitted.index');

    // Import Excel
    Route::post('/remitted/import', [RemittedController::class, 'importExcel'])->name('remitted.import');

    // API for updated data (JSON)
    Route::get('/api/remitted', function () {
        return response()->json([
            'remitted' => \App\Models\CRemitted::all()
        ]);
    })->name('api.remitted');
});

// Public home page
Route::get('/', fn () => Inertia::render('welcome'))->name('home');

// Other authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');

    Route::get('/PAGIBIGreport', fn () => Inertia::render('PAGIBIGreport/index'))->name('pagibig.index');

    // API: Employee full names for dropdown
    Route::get('/api/pagibig/full-names', function () {
        return CRemitted::select(DB::raw("CONCAT(first_name, ' ', middle_name, ' ', last_name) as full_name"))
            ->distinct()
            ->orderBy('full_name')
            ->pluck('full_name');
    })->name('pagibig.full-names');

    // PDF generation
    Route::get('/pdf-template/{full_name?}', [PdfController::class, 'generatePAGIBIGReport'])
        ->name('pdf.template');
});
