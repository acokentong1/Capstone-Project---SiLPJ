<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Belanja extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'nomor_bukti',
        'uraian',
        'kategori',
        'jumlah',
        'harga_satuan',
        'total',
        'nota',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'integer',
            'total' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kwitansi()
    {
        return $this->hasOne(Kwitansi::class);
    }

    public function bapb()
    {
        return $this->hasOne(Bapb::class);
    }

    public function notaPesanan()
    {
        return $this->hasOne(NotaPesanan::class);
    }
}
