<?php
use App\Http\Controllers\PrescriptionController;
/*
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/
require_once "functions.php";

use App\Http\Controllers\MqttController;
use Illuminate\Support\Facades\Route;
Route::get('/dashboard/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
Route::post('/dashboard/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
Route::get('/', fn() => renderPage("index"));
Route::get('/test', fn() => renderPage("test"));



Route::post('/sendMqtt', [MqttController::class, 'send']);

require __DIR__.'/auth.php';
