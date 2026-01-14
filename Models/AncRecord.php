<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AncRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'rekam_medis',
        'kohort',
        'nama_pasien',
        'alamat',
        'nik',
        'petugas',
        'k1',
        'k2',
        'k3',
        'k4',
        'k5',
        'k6',
        'user_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'k1' => 'array',
        'k2' => 'array',
        'k3' => 'array',
        'k4' => 'array',
        'k5' => 'array',
        'k6' => 'array',
    ];

    public static function getAncItems()
    {
        return [
            1 => 'Pemeriksaan Dokter',
            2 => 'Tekanan darah',
            3 => 'Timbang berat badan',
            4 => 'Tinggi badan',
            5 => 'Nilai status gizi (ukur LILA)',
            6 => 'Ukur tinggi fundus uteri',
            7 => 'Tentukan presentasi janin dan denyut jantung janin',
            8 => 'Skrining status imunisasi dan berikan suntikan tetanus toksoid (TT) bila diperlukan',
            9 => 'Beri tablet tambah darah',
            10 => 'Pemeriksaan laboratorium meliputi: Golongan darah, Kadar Hb, Gluko-Protein urin, termasuk pemeriksaan HIV',
            11 => 'Tata laksana',
            12 => 'Temu wicara/konseling'
        ];
    }

    public static function getKunjunganTypes()
    {
        return [
            'k1' => '0-12 minggu',
            'k2' => '>12-24 minggu',
            'k3' => '>12-24 minggu',
            'k4' => '>24 minggu sampai kelahiran',
            'k5' => '>24 minggu sampai kelahiran',
            'k6' => '>24 minggu sampai kelahiran'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
