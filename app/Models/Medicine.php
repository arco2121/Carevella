<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $fillable = ['code', 'name'];

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Restituisce la label completa: "[CODICE] Nome"
     */
    public function getFullLabelAttribute(): string
    {
        return "[{$this->code}] {$this->name}";
    }
}
