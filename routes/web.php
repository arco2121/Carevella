<?php

require_once "functions.php";

use App\Http\Controllers\MqttController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Middleware\CheckRole;
use App\Models\Medicine;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => renderPage("index", ['title' => 'Home']));
Route::get('/test', fn() => renderPage("test", ['title' => 'Test MQTT']));

Route::post('/sendMqtt', [MqttController::class, 'send']);


Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return redirect('/dashboard-' . auth()->user()->role);
    })->name('dashboard');

    Route::middleware(CheckRole::class . ':patient')->group(function () {
        Route::get('/dashboard-patient', fn() => renderPage("dashboards.dashboard_paziente", ['title' => 'Dashboard Paziente']))
            ->name('dashboard-patient');
    });

    Route::middleware(CheckRole::class . ':doctor')->group(function () {
        Route::get('/dashboard-medico', function () {

            $medico = auth()->user();

            $listaPazienti = $medico->pazienti()->where('role', 'patient')->get();

            $listaMedicine = Medicine::all();

            return renderPage("dashboards.dashboard_medico", [
                'title' => 'Dashboard Medico',
                'pazienti' => $listaPazienti,
                'medicine' => $listaMedicine
            ]);
        })->name('dashboard-medico');

        Route::get('/dashboard/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
        Route::post('/dashboard/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    });

    Route::middleware(CheckRole::class . ':family')->group(function () {
        Route::get('/dashboard-famiglia', fn() => renderPage("dashboards.dashboard_paziente", ['title' => 'Dashboard Famiglia']))
            ->name('dashboard-famiglia');
    });

});

require __DIR__.'/auth.php';
