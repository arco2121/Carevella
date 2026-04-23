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
Route::get('/dashboard-paziente', function () {
    return view('dashboards.dashboard_paziente');
});


Route::get('/dashboard-medico', function () {
    // Recupera l'istanza del medico attualmente autenticato
    $medico = auth()->user();

    // Estrae solo i pazienti assegnati al medico loggato, verificando il ruolo esatto sul DB
    $listaPazienti = $medico->pazienti()->where('role', 'patient')->get();

    // Estrae l'elenco completo dei farmaci disponibili
    $listaMedicine = Medicine::all();

    // Utilizza la funzione custom per caricare il layout, passando i parametri richiesti dal Blade
    return renderPage("dashboards.dashboard_medico", [
        'title' => 'Dashboard Medico',
        'pazienti' => $listaPazienti,
        'medicine' => $listaMedicine
    ]);
})->middleware('auth');


Route::post('/sendMqtt', [MqttController::class, 'send']);

require __DIR__.'/auth.php';
