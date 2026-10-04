<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaPesanan extends Model
{
    protected $fillable = [
        'user_id',
        'belanja_id',
        'nomor_nota',
        'tanggal_nota',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_nota' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }
}
