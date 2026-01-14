<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $fillable = [
        'nama_posyandu',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
