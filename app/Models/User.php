<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Definisce le colonne abilitate al mass assignment (es. tramite User::create)
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'doctor_id'
    ];

    // Esclude questi campi sensibili durante la serializzazione in array o JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Applica il cast automatico dei tipi per i campi specificati
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relazione uno-a-molti: un medico gestisce più pazienti
    public function pazienti(): HasMany
    {
        return $this->hasMany(User::class, 'doctor_id');
    }

    // Relazione uno-a-uno (inversa): un paziente è assegnato a un solo medico
    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // Relazione uno-a-molti: un paziente genera più record di sensori
    public function datiSensori(): HasMany
    {
        return $this->hasMany(Data::class, 'patient_id');
    }

    // Relazione uno-a-molti: un paziente possiede più prescrizioni mediche
    public function prescrizioni(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }
}
