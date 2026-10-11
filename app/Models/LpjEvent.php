<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LpjEvent extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['details' => 'array', 'created_at' => 'datetime'];
    }
    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }
}
