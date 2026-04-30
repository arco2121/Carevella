<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionLog extends Model
{
    protected $fillable = [
        'patient_id',
        'prescription_id',
        'date',
        'taken',
        'taken_at',
    ];

    protected $casts = [
        'taken'    => 'boolean',
        'date'     => 'date',
        'taken_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
