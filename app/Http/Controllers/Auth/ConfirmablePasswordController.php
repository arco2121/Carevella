<?php

namespace App\Http\Controllers\Auth;

require_once base_path('routes/functions.php');

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View;

class ConfirmablePasswordController extends Controller
{
    public function show(): View
    {
        // Usa la tua funzione e passa un titolo
        return renderPage('auth.confirm-password', ['title' => 'Conferma Password']);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        // Redirect dinamico basato sul ruolo invece della rotta 'dashboard' fissa
        $user = auth()->user();
        $target = $user->role === 'doctor' ? '/medico' : '/paziente';

        return redirect()->intended($target);
    }
}
