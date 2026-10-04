<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nama_sekolah',
        'npsn',
        'alamat',
        'kepala_sekolah',
        'nip_kepala',
        'bendahara',
        'nip_bendahara',
        'logo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
