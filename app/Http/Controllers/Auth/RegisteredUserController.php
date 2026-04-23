<?php

namespace App\Http\Controllers\Auth;

require_once base_path('routes/functions.php');

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View; // Usiamo l'interfaccia corretta per renderPage

class RegisteredUserController extends Controller
{
    /**
     * Mostra la vista di registrazione usando la tua funzione custom.
     */
    public function create(): View
    {
        return renderPage('auth.register', ['title' => 'Registrazione']);
    }

    /**
     * Gestisce la richiesta di registrazione.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validazione allineata al tuo form e al tuo database
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role'     => ['required', 'string', 'in:patient,doctor,family'], // Validiamo il ruolo
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Creazione dell'utente con i campi corretti
        $user = User::create([
            'username' => $request->username, // Usiamo username, NON name
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,     // Salviamo il ruolo scelto nel form
        ]);

        event(new Registered($user));

        Auth::login($user);

        // 3. Reindirizzamento dinamico basato sul ruolo scelto
        if ($user->role === 'doctor') {
            return redirect('/medico');
        } elseif ($user->role === 'family') {
            return redirect('/familiare');
        }

        return redirect('/paziente');
    }
}
