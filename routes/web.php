<?php

use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalController::class, 'home'])->name('home');
Route::get('/kas', [PortalController::class, 'cash'])->name('cash');
Route::get('/agenda', [PortalController::class, 'events'])->name('events');
Route::get('/momen', [PortalController::class, 'moments'])->name('moments');
Route::get('/momen/drive/{fileId}', [PortalController::class, 'driveImage'])->where('fileId', '[A-Za-z0-9_-]+')->name('drive.image');
Route::get('/login', [PortalController::class, 'loginForm'])->name('login');
Route::post('/login', [PortalController::class, 'login'])->name('login.submit');
Route::post('/logout', [PortalController::class, 'logout'])->name('logout');
Route::post('/admin/{section}', [PortalController::class, 'store'])->name('admin.store');
Route::delete('/admin/{section}/{id}', [PortalController::class, 'destroy'])->name('admin.destroy');
