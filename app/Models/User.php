<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['username', 'email', 'password', 'role', 'doctor_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Se l'utente è un medico, restituisce i suoi pazienti.
     */
    public function pazienti(): HasMany
    {
        return $this->hasMany(User::class, 'doctor_id');
    }

    /**
     * Se l'utente è un paziente, restituisce il suo medico.
     */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Restituisce i dati dei sensori (temp/hum) collegati al paziente.
     */
    public function datiSensori(): HasMany
    {
        return $this->hasMany(Data::class, 'patient_id');
    }

    /**
     * Restituisce le prescrizioni (terapie) del paziente.
     */
    public function prescrizioni(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }
}
