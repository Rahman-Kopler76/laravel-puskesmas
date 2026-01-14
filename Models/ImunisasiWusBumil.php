<?php
// app/Models/ImunisasiWusBumil.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImunisasiWusBumil extends Model
{
    use HasFactory;

    protected $table = 'imunisasi_wus_bumil';
    protected $guarded = ['id'];

    protected $fillable = ['posyandu_id', 'nama_wus_bumil', 'nama_suami', 'umur', 'hamil_ke', 'jenis_imunisasi_id', 'alamat_lengkap', 'nik', 'user_id'];

    public function jenisImunisasi()
    {
        return $this->belongsTo(JenisImunisasi::class);
    }

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}