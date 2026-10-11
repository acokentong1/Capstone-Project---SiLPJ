<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LpjAttachment extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['contents_base64'];
    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }
}
