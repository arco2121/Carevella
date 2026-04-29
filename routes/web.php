<?php

require_once "functions.php";

use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MqttController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Middleware\CheckRole;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => renderPage("index", ['title' => 'IOT Project']));
Route::get('/test', fn() => renderPage("test", ['title' => 'Test MQTT']));

Route::post('/sendMqtt', [MqttController::class, 'send']);

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return redirect('/dashboard-' . auth()->user()->role);
    })->name('dashboard');

    Route::get('/profilo', function () {
        $user = auth()->user();
        $data = ['title' => 'Profilo', 'user' => $user];

        if ($user->role === 'paziente') {
            $data['medici'] = User::where('role', 'medico')->get();
        } elseif ($user->role === 'medico') {
            $data['pazienti_list'] = $user->pazienti()->where('role', 'paziente')->get();
        }

        return renderPage('dashboards.profilo', $data);
    })->name('profilo');

    Route::post('/profilo/assegna-medico', function (Request $request) {
        $request->validate([
            'doctor_id' => ['required', 'exists:users,id', function ($attr, $value, $fail) {
                if (User::where('id', $value)->where('role', 'medico')->doesntExist()) {
                    $fail('L\'utente selezionato non è un medico.');
                }
            }],
        ]);

        $paziente = auth()->user();

        if ((int) $paziente->doctor_id !== (int) $request->doctor_id) {
            Prescription::where('patient_id', $paziente->id)->delete();
            $paziente->update(['doctor_id' => $request->doctor_id]);
            $msg = 'Medico aggiornato! Le prescrizioni precedenti sono state rimosse; il nuovo medico imposterà il tuo piano.';
        } else {
            $msg = 'Il medico selezionato è già quello assegnato.';
        }

        return redirect()->route('profilo')->with('success', $msg);
    })->middleware(CheckRole::class . ':paziente')->name('profilo.assegna-medico');

    Route::get('/api/paziente/{id}/prescrizioni', function ($id) {
        $medico   = auth()->user();
        $paziente = $medico->pazienti()->where('id', $id)->firstOrFail();

        $prescrizioni = $paziente->prescrizioni()
            ->with('medicine')
            ->get()
            ->map(fn ($p) => [
                'day'            => $p->day,
                'step'           => $p->step,
                'scheduled_time' => $p->scheduled_time,
                'medicine_name'  => $p->medicine->name,
                'amount'         => $p->amount,
            ]);

        return response()->json($prescrizioni);
    })->middleware(CheckRole::class . ':medico');

    Route::middleware(CheckRole::class . ':paziente')->group(function () {
        Route::get('/dashboard-paziente', fn() => renderPage("dashboards.dashboard_paziente", ['title' => 'Dashboard Paziente']))
            ->name('dashboard-paziente');
    });

    Route::middleware(CheckRole::class . ':medico')->group(function () {
        Route::get('/dashboard-medico', function () {
            $medico        = auth()->user();
            $listaPazienti = $medico->pazienti()->where('role', 'paziente')->get();
            $listaMedicine = Medicine::all();

            return renderPage("dashboards.dashboard_medico", [
                'title'    => 'Dashboard Medico',
                'pazienti' => $listaPazienti,
                'medicine' => $listaMedicine,
            ]);
        })->name('dashboard-medico');

        Route::get('/dashboard/prescrizioni', [PrescriptionController::class, 'index'])
            ->name('prescriptions.index');

        Route::post('/dashboard/prescrizioni', [PrescriptionController::class, 'store'])
            ->name('prescriptions.store');

        Route::post('/dashboard/prescrizioni/{patientId}/clear', [PrescriptionController::class, 'clear'])
            ->name('prescriptions.clear');

        Route::get('/dashboard/farmaci', [MedicineController::class, 'index'])
            ->name('medicines.index');

        Route::post('/dashboard/farmaci', [MedicineController::class, 'store'])
            ->name('medicines.store');

        Route::put('/dashboard/farmaci/{medicine}', [MedicineController::class, 'update'])
            ->name('medicines.update');

        Route::delete('/dashboard/farmaci/{medicine}', [MedicineController::class, 'destroy'])
            ->name('medicines.destroy');
    });

    Route::middleware(CheckRole::class . ':famiglia')->group(function () {
        Route::get('/dashboard-famiglia', fn() => renderPage("dashboards.dashboard_famiglia", ['title' => 'Dashboard Famiglia']))
            ->name('dashboard-famiglia');
    });

});

require __DIR__.'/auth.php';
