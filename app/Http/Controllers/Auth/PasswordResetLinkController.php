<?php

namespace App\Http\Controllers\Auth;

require_once base_path('routes/functions.php');

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return renderPage('auth.forgot-password', ['title' => 'Recupero Password']);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
    }
}
