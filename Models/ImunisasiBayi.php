<?php
// app/Models/ImunisasiBayi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImunisasiBayi extends Model
{
    use HasFactory;

    protected $table = 'imunisasi_bayi';
    protected $guarded = ['id'];

    protected $fillable = ['nama_bayi', 'posyandu_id', 'nama_orang_tua', 'tanggal_lahir', 'jenis_kelamin', 'jenis_imunisasi_id', 'alamat_lengkap', 'nik_orang_tua', 'nik_bayi', 'user_id'];

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
