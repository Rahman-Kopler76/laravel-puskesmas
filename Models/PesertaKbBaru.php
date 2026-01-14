<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaKbBaru extends Model
{
    use HasFactory;

    protected $table = 'peserta_kb_baru';

    protected $fillable = [
        'posyandu_id',
        'nama_pasien',
        'tanggal_pelayanan',
        'jenis_kontrasepsi',
        'jalur_layanan',
        'user_id'
    ];

    // Relasi ke model Posyandu
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}