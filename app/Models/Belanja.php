<?php
// Tambahkan fillable school_profile_id pada model Belanja.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Belanja extends Model
{
    use HasFactory;

    protected $table = 'belanjas';

    protected $fillable = [
        'user_id',
        'school_profile_id',
        'tanggal',
        'tahun_anggaran',
        'nomor_bukti',
        'uraian',
        'kategori',
        'jumlah',
        'harga_satuan',
        'total',
        'nota',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolProfile()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_profile_id');
    }

    public function notaPesanan()
    {
        return $this->hasOne(NotaPesanan::class);
    }

    public function kwitansi()
    {
        return $this->hasOne(Kwitansi::class);
    }

    public function bapb()
    {
        return $this->hasOne(Bapb::class);
    }
}
