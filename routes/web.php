<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\Http\Controllers\PdfController;
use App\Http\Controllers\RemittedController;
use App\Http\Controllers\GSSRemittedController;
use App\Models\CRemitted;
use App\Models\GSSRemitted; // ✅ Model for GSS table

// ==========================
// Public Home Page
// ==========================
Route::get('/', fn () => Inertia::render('welcome'))->name('home');

// ==========================
// Authenticated Routes
// ==========================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // ======================
    // Dashboard
    // ======================
    Route::get('/dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');

    // ======================
    // PAGIBIG Remitted
    // ======================
    Route::get('/remitted', [RemittedController::class, 'index'])->name('remitted.index');
    Route::post('/remitted/import', [RemittedController::class, 'importExcel'])->name('remitted.import');

    // API: All PAGIBIG Remitted Data
    Route::get('/api/remitted', fn () => response()->json([
        'remitted' => CRemitted::all()
    ]))->name('api.remitted');

    // PAGIBIG Report Page
    Route::get('/PAGIBIGreport', fn () => Inertia::render('PAGIBIGreport/index'))->name('pagibig.index');

    // API: Employee full names (PAGIBIG dropdown)
    Route::get('/api/pagibig/full-names', function () {
        return CRemitted::select(DB::raw("CONCAT(first_name, ' ', middle_name, ' ', last_name) as full_name"))
            ->distinct()
            ->orderBy('full_name')
            ->pluck('full_name');
    })->name('pagibig.full-names');

    // PAGIBIG PDF
    Route::get('/pdf-template/{full_name?}', [PdfController::class, 'generatePAGIBIGReport'])
        ->name('pdf.pagibig');

    // ======================
    // GSS Remitted
    // ======================
    Route::get('/gssremitted', [GSSRemittedController::class, 'index'])->name('gssremitted.index');
    Route::post('/gssremitted/import', [GSSRemittedController::class, 'import'])->name('gssremitted.import');

    // API: All GSS Remitted Data
    Route::get('/api/gssremitted', [GSSRemittedController::class, 'api'])->name('api.gssremitted');

    // GSS Report Page
    Route::get('/GSSreport', fn () => Inertia::render('GSSreport/index'))->name('gss.index');

// API: Employee full names (GSS dropdown)
Route::get('/api/gss/full-names', function () {
    return GSSRemitted::select(DB::raw("CONCAT(first_name, ' ', COALESCE(mi, ''), ' ', last_name) as full_name"))
        ->distinct()
        ->orderBy('full_name')
        ->pluck('full_name');
})->name('gss.full-names');


    // ⚠️ NOTE: Change first_name, middle_name, last_name 
    // to match your gssremitted table column names

    // GSS PDF
    Route::get('/pdf-gss-template/{full_name?}', [PdfController::class, 'generateGSSReport'])
        ->name('pdf.gss');



        
});
