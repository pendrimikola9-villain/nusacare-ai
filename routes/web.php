<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AiHubController;

// Zona A: Publik & Autentikasi (Halaman 1, 2, 3)
Route::view('/', 'landing')->name('landing');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('demo.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Zona B & C: Portal Terautentikasi
Route::middleware('auth')->group(function () {
    // Zona B: Portal Keluarga & Masyarakat (Halaman 4, 5, 6, 7, 8)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/scanner', [AiHubController::class, 'scannerPage'])->name('scanner');
    Route::post('/scanner/analyze', [AiHubController::class, 'processScan'])->name('scanner.analyze');
    Route::delete('/scanner/{id}', [AiHubController::class, 'deleteScan'])->name('scanner.delete');
    Route::get('/scanner/{id}/download', [AiHubController::class, 'downloadScan'])->name('scanner.download');

    Route::get('/wellness-plan', [AiHubController::class, 'wellnessPage'])->name('wellness');
    Route::post('/wellness-plan/generate', [AiHubController::class, 'generateProgram'])->name('wellness.generate');
    Route::patch('/wellness-plan/{id}/toggle/{stepIndex}', [AiHubController::class, 'toggleStep'])->name('wellness.toggle');
    Route::get('/wellness-plan/{id}/ics', [AiHubController::class, 'downloadIcs'])->name('wellness.ics');

    Route::get('/profil', [DashboardController::class, 'profil'])->name('profil');
    Route::put('/profil', [DashboardController::class, 'updateProfil'])->name('profil.update');

    Route::get('/konsultasi', [DashboardController::class, 'konsultasiPage'])->name('konsultasi');
    Route::post('/konsultasi', [DashboardController::class, 'konsultasiAi'])->name('konsultasi.send');
    Route::delete('/konsultasi/reset', [DashboardController::class, 'resetKonsultasi'])->name('konsultasi.reset');

    Route::get('/tracker', [DashboardController::class, 'trackerPage'])->name('tracker');
    Route::post('/tracker', [DashboardController::class, 'storeTracker'])->name('tracker.store');
    Route::post('/tracker/challenge', [DashboardController::class, 'generateTrackerChallenge'])->name('tracker.challenge');
    
    Route::get('/radar-riwayat', [DashboardController::class, 'radarHistory'])->name('radar');
    Route::post('/radar-riwayat/first-aid', [DashboardController::class, 'processFirstAid'])->name('radar.first-aid');

    // Zona C: Portal Tenaga Medis / Puskesmas (Halaman 9 & 10)
    Route::prefix('medis')->name('medis.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'medisDashboard'])->name('dashboard');
        Route::get('/pasien/{id}', [DashboardController::class, 'medisDetail'])->name('detail');
        Route::patch('/pasien/{id}/status', [DashboardController::class, 'updateStatus'])->name('status');
    });
});
