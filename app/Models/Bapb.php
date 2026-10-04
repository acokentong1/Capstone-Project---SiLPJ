<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bapb extends Model
{
    protected $fillable = [
        'user_id',
        'belanja_id',
        'nomor_bapb',
        'tanggal_bapb',
        'hasil_pemeriksaan',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bapb' => 'date',
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
