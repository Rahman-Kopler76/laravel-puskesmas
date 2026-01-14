<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posyandu;
use App\Models\ImunisasiBayi;
use App\Models\ImunisasiWusBumil;
use App\Models\JenisImunisasi;
use App\Exports\LaporanImunisasiExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanImunisasiController extends Controller
{
    public function index()
    {
        return view('pages.apps.pustu.imunisasi.laporan_imunisasi.index');
    }

    public function exportImunisasi(Request $request)
    {
        $filterType = $request->input('filter_type', 'monthly');
        $periode = 'Semua Data'; // Default title
        $tahun = now()->year;   // Default year

        // ==========================================================
        //         LOGIKA FILTER BARU YANG LEBIH FLEKSIBEL
        // ==========================================================

        $queryBayi = ImunisasiBayi::with(['posyandu', 'jenisImunisasi']);
        $queryBumil = ImunisasiWusBumil::with(['posyandu', 'jenisImunisasi']);

        // 1. Terapkan filter WAKTU berdasarkan jenisnya
        if ($filterType === 'monthly') {
            $bulan = $request->input('bulan', now()->month);
            $tahun = $request->input('tahun_bulanan', now()->year);
            $queryBayi->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
            $queryBumil->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
            $periode = Carbon::create()->month($bulan)->translatedFormat('F') . " {$tahun}";
        } elseif ($filterType === 'yearly') {
            $tahun = $request->input('tahun_tahunan', now()->year);
            $queryBayi->whereYear('created_at', $tahun);
            $queryBumil->whereYear('created_at', $tahun);
            $periode = "Tahun {$tahun}";
        } elseif ($filterType === 'range') {
            $start = $request->input('start_date', now()->startOfMonth()->toDateString());
            $end = $request->input('end_date', now()->endOfMonth()->toDateString());
            $queryBayi->whereBetween('created_at', [$start, $end]);
            $queryBumil->whereBetween('created_at', [$start, $end]);
            $periode = Carbon::parse($start)->format('d/m/Y') . ' - ' . Carbon::parse($end)->format('d/m/Y');
            $tahun = Carbon::parse($start)->year;
        }
        // Jika 'all', tidak ada filter waktu diterapkan

        // 2. Terapkan filter USER (logika ini tetap sama)
        $userIdToFilter = null;
        if ($request->has('user_id') && auth()->user()->role_id == 1) {
            $userIdToFilter = $request->user_id;
        } elseif (auth()->user()->role_id == 2) {
            $userIdToFilter = auth()->id();
        }
        if ($userIdToFilter) {
            $queryBayi->where('user_id', $userIdToFilter);
            $queryBumil->where('user_id', $userIdToFilter);
        }

        // 3. Eksekusi query untuk mendapatkan data
        $imunisasiBayi = $queryBayi->get();
        $imunisasiWusBumil = $queryBumil->get();

        // 4. Filter daftar Posyandu juga menggunakan filter user yang sama
        $posyanduQuery = Posyandu::query();
        if ($userIdToFilter) {
            $posyanduQuery->where('user_id', $userIdToFilter);
        }
        $allPosyandu = $posyanduQuery->orderBy('nama_posyandu')->get();

        // ==========================================================
        //                 AKHIR DARI LOGIKA FILTER
        // ==========================================================

        // Proses data menjadi struktur laporan (tidak ada perubahan di bawah ini)
        $allJenisImunisasi = JenisImunisasi::pluck('nama_imunisasi')->toArray();
        $reportData = [];
        foreach ($allPosyandu as $posyandu) {
            $rowData = ['nama_posyandu' => $posyandu->nama_posyandu];
            foreach ($allJenisImunisasi as $jenis) {
                if (!in_array($jenis, ['TT1', 'TT2', 'TT3', 'TT4', 'TT5'])) {
                    $rowData[$jenis] = ['L' => 0, 'P' => 0];
                } else {
                    $rowData['BUMIL'][$jenis] = 0;
                    $rowData['WUS'][$jenis] = 0;
                }
            }
            foreach ($imunisasiBayi->where('posyandu_id', $posyandu->id) as $data) {
                if ($data->jenisImunisasi) {
                    $jenis = $data->jenisImunisasi->nama_imunisasi;
                    $gender = $data->jenis_kelamin;
                    if (isset($rowData[$jenis][$gender])) {
                        $rowData[$jenis][$gender]++;
                    }
                }
            }
            foreach ($imunisasiWusBumil->where('posyandu_id', $posyandu->id) as $data) {
                if ($data->jenisImunisasi) {
                    $jenis = $data->jenisImunisasi->nama_imunisasi;
                    if ($data->hamil_ke > 0) {
                        if (isset($rowData['BUMIL'][$jenis])) {
                            $rowData['BUMIL'][$jenis]++;
                        }
                    } else {
                        if (isset($rowData['WUS'][$jenis])) {
                            $rowData['WUS'][$jenis]++;
                        }
                    }
                }
            }
            $reportData[] = $rowData;
        }

        $namaFile = "Laporan Imunisasi - {$periode}.xlsx";

        return Excel::download(new LaporanImunisasiExport($reportData, $periode, $tahun), $namaFile);
    }
}