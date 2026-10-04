<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kwitansi extends Model
{
    protected $fillable = [
        'belanja_id',
        'nomor_kwitansi',
        'tanggal_kwitansi',
        'penerima',
        'jumlah_uang',
        'terbilang',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kwitansi' => 'date',
            'jumlah_uang' => 'decimal:2',
        ];
    }

    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }
}
