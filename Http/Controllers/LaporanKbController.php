<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posyandu;
use App\Models\PesertaKbBaru;
use App\Exports\LaporanKbExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanKbController extends Controller
{
    public function index()
    {
        return view('pages.apps.pustu.keluarga_berencana.laporan_kb.index');
    }

    public function export(Request $request)
    {
        $filterType = $request->input('filter_type', 'monthly');
        $periode = 'Semua Data'; // Default title for 'all'
        $tahun = now()->year;   // Default year

        // ==========================================================
        //         LOGIKA FILTER BARU YANG LEBIH FLEKSIBEL
        // ==========================================================

        $kbQuery = PesertaKbBaru::query();

        // 1. Terapkan filter WAKTU berdasarkan jenisnya
        if ($filterType === 'monthly') {
            $bulan = $request->input('bulan', now()->month);
            $tahun = $request->input('tahun_bulanan', now()->year);
            $kbQuery->whereMonth('tanggal_pelayanan', $bulan)->whereYear('tanggal_pelayanan', $tahun);
            $periode = Carbon::create()->month($bulan)->translatedFormat('F') . " {$tahun}";
        } elseif ($filterType === 'yearly') {
            $tahun = $request->input('tahun_tahunan', now()->year);
            $kbQuery->whereYear('tanggal_pelayanan', $tahun);
            $periode = "Tahun {$tahun}";
        } elseif ($filterType === 'range') {
            $start = $request->input('start_date', now()->startOfMonth()->toDateString());
            $end = $request->input('end_date', now()->endOfMonth()->toDateString());
            $kbQuery->whereBetween('tanggal_pelayanan', [$start, $end]);
            $periode = Carbon::parse($start)->format('d/m/Y') . ' - ' . Carbon::parse($end)->format('d/m/Y');
            $tahun = Carbon::parse($start)->year;
        }
        // Jika filterType adalah 'all', tidak ada filter waktu yang diterapkan.

        // 2. Terapkan filter USER (logika ini tetap sama)
        $userIdToFilter = null;
        if ($request->has('user_id') && auth()->user()->role_id == 1) {
            $userIdToFilter = $request->user_id;
        } elseif (auth()->user()->role_id == 2) {
            $userIdToFilter = auth()->id();
        }
        if ($userIdToFilter) {
            $kbQuery->where('user_id', $userIdToFilter);
        }

        // 3. Eksekusi query untuk mendapatkan data KB
        $kbRecords = $kbQuery->get();

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
        $reportData = [];
        foreach ($allPosyandu as $posyandu) {
            $rowData = ['nama_desa' => $posyandu->nama_posyandu];
            $allKontrasepsi = ['PIL', 'SUNTIK', 'KONDOM', 'IUD', 'IMPLAN', 'MOW', 'MOP'];
            $allJalur = ['UMUM', 'BPJS/K', 'PASCA SALIN'];
            foreach ($allKontrasepsi as $kontrasepsi) {
                foreach ($allJalur as $jalur) {
                    $rowData[$kontrasepsi][$jalur] = 0;
                }
            }
            foreach ($kbRecords->where('posyandu_id', $posyandu->id) as $record) {
                $rowData[$record->jenis_kontrasepsi][$record->jalur_layanan]++;
            }
            $reportData[] = $rowData;
        }

        $namaFile = "Laporan KB Peserta Baru - {$periode}.xlsx";

        // Kirim $periode sebagai pengganti $namaBulan ke Class Export
        return Excel::download(new LaporanKbExport($reportData, $periode, $tahun), $namaFile);
    }
}