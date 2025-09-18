<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RawatJalanController;
use App\Http\Controllers\ResepController;

// Login
Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// ==== Dokter ====
Route::middleware(['auth', 'role:doctor'])
    ->prefix('rajal')
    ->name('rajal.')
    ->group(function () {
        Route::get('/', [RawatJalanController::class, 'index'])->name('index');
        Route::get('/datatable', [RawatJalanController::class, 'datatable'])->name('datatable');
        Route::get('/create', [RawatJalanController::class, 'addRajal'])->name('create');
        Route::get('/pasien-search', [RawatJalanController::class, 'searchPasien'])->name('pasien.search');
        Route::post('/daftar', [RawatJalanController::class, 'daftar'])->name('daftar');
        Route::get('/detail', [RawatJalanController::class, 'detail'])->name('detail');

        Route::post('/asesmen/save', [RawatJalanController::class, 'asesmenSave'])->name('asesmen.save');

        Route::get('/diagnosis/list', [RawatJalanController::class, 'diagnosisList'])->name('diagnosis.list');
        Route::post('/diagnosis/add', [RawatJalanController::class, 'diagnosisAdd'])->name('diagnosis.add');
        Route::post('/diagnosis/delete', [RawatJalanController::class, 'diagnosisDelete'])->name('diagnosis.delete');

        Route::get('/tindakan/search', [RawatJalanController::class, 'tindakanSearch'])->name('tindakan.search');
        Route::get('/tindakan/list', [RawatJalanController::class, 'tindakanList'])->name('tindakan.list');
        Route::post('/tindakan/add', [RawatJalanController::class, 'tindakanAdd'])->name('tindakan.add');
        Route::post('/tindakan/delete', [RawatJalanController::class, 'tindakanDelete'])->name('tindakan.delete');

        Route::get('/eresep/list', [RawatJalanController::class, 'eresepList'])
            ->name('eresep.list');
        Route::post('/eresep/save', [RawatJalanController::class, 'eresepSave'])->name('eresep.save');
        Route::post('/eresep/submit', [RawatJalanController::class, 'eresepSubmit'])->name('eresep.submit');

        Route::get('/asesmen/get', [RawatJalanController::class, 'asesmenGet'])->name('asesmen.get');

        Route::get('/icd/search', [RawatJalanController::class, 'icdSearch'])
            ->name('icd.search');
    });

// ==== Apoteker ====
Route::middleware(['auth', 'role:pharmacist'])
    ->prefix('resep')
    ->name('resep.')
    ->group(function () {
        Route::get('/', [ResepController::class, 'index'])->name('index');
        Route::get('/datatable', [ResepController::class, 'datatable'])->name('datatable');
        Route::get('/{uuid}', [ResepController::class, 'show'])->name('show');
        Route::post('/{uuid}/serve', [ResepController::class, 'serve'])->name('serve');
        Route::get('/{uuid}/print', [ResepController::class, 'print'])->name('print');
    });

Route::middleware('auth')->get('/api/medicines', [RawatJalanController::class, 'apiMedicines'])->name('api.medicines');
Route::middleware('auth')->get('/api/medicines/{id}/priceAt', [RawatJalanController::class, 'apiMedicinePriceAt'])->name('api.medicine.priceAt');

// LOGS
Route::get('/activity', [ActivityController::class, 'index'])->name('activity');
Route::get('/activity-datatable', [ActivityController::class, 'datatable'])->name('activity.datatable');
