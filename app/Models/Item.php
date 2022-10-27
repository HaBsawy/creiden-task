<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage_id', 'name', 'description'
    ];

    public function storage()
    {
        return $this->belongsTo(Storage::class);
    }
}
