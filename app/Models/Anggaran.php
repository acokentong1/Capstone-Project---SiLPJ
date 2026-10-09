<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggaran extends Model
{
    use HasFactory;

    protected $table = 'anggarans';

    protected $fillable = ['user_id', 'school_profile_id', 'tahun', 'jumlah'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolProfile()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_profile_id');
    }
}
