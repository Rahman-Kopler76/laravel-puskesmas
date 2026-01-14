<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisImunisasi extends Model
{
    use HasFactory;
    protected $table = 'jenis_imunisasi';
    protected $guarded = ['id'];
}