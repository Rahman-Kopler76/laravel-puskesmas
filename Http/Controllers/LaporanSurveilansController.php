<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyakit;
use App\Models\SurveilansPenyakit;
use App\Exports\LaporanSurveilansExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanSurveilansController extends Controller
{
    public function index()
    {
        return view('pages.apps.pustu.penyakit.laporan_penyakit.index');
    }

    public function export(Request $request)
    {
        $filterType = $request->input('filter_type', 'monthly');
        $periode = 'Semua Data'; // Default title for 'all'
        $tahun = now()->year;   // Default year

        // ==========================================================
        //         LOGIKA FILTER BARU YANG LEBIH FLEKSIBEL
        // ==========================================================

        $query = SurveilansPenyakit::query();

        if ($filterType === 'monthly') {
            $bulan = $request->input('bulan', now()->month);
            $tahun = $request->input('tahun_bulanan', now()->year);
            $query->whereMonth('tanggal_kunjungan', $bulan)->whereYear('tanggal_kunjungan', $tahun);
            $namaBulan = Carbon::create()->month($bulan)->translatedFormat('F');
            $periode = "{$namaBulan} {$tahun}";
        } elseif ($filterType === 'yearly') {
            $tahun = $request->input('tahun_tahunan', now()->year);
            $query->whereYear('tanggal_kunjungan', $tahun);
            $periode = "Tahun {$tahun}";
        } elseif ($filterType === 'range') {
            $start = $request->input('start_date', now()->startOfMonth()->toDateString());
            $end = $request->input('end_date', now()->endOfMonth()->toDateString());
            $query->whereBetween('tanggal_kunjungan', [$start, $end]);
            $periode = Carbon::parse($start)->format('d/m/Y') . ' - ' . Carbon::parse($end)->format('d/m/Y');
            $tahun = Carbon::parse($start)->year; // Ambil tahun dari tanggal mulai
        }
        // Jika filterType adalah 'all', tidak ada filter waktu yang diterapkan.

        // Logika filter user_id (tetap sama)
        $userIdToFilter = null;
        if ($request->has('user_id') && auth()->user()->role_id == 1) {
            $userIdToFilter = $request->user_id;
        } elseif (auth()->user()->role_id == 2) {
            $userIdToFilter = auth()->id();
        }
        if ($userIdToFilter) {
            $query->where('user_id', $userIdToFilter);
        }

        $records = $query->get();
        // ==========================================================
        //                 AKHIR DARI LOGIKA FILTER
        // ==========================================================

        $allPenyakit = Penyakit::orderBy('id')->get();

        // Logika untuk memproses dan mengelompokkan data (tidak berubah)
        $reportData = [];
        $ageGroups = [
            '0-7 Hr' => ['start' => 0, 'end' => 7, 'unit' => 'day'], '8-28 Hr' => ['start' => 8, 'end' => 28, 'unit' => 'day'],
            '< 1' => ['start' => 29, 'end' => 364, 'unit' => 'day'], '1-4' => ['start' => 1, 'end' => 4, 'unit' => 'year'],
            '5-9' => ['start' => 5, 'end' => 9, 'unit' => 'year'], '10-14' => ['start' => 10, 'end' => 14, 'unit' => 'year'],
            '15-19' => ['start' => 15, 'end' => 19, 'unit' => 'year'], '20-44' => ['start' => 20, 'end' => 44, 'unit' => 'year'],
            '45-54' => ['start' => 45, 'end' => 54, 'unit' => 'year'], '55-59' => ['start' => 55, 'end' => 59, 'unit' => 'year'],
            '60-69' => ['start' => 60, 'end' => 69, 'unit' => 'year'], '70+' => ['start' => 70, 'end' => 200, 'unit' => 'year'],
        ];
        foreach ($allPenyakit as $penyakit) {
            $rowData = ['nama_penyakit' => $penyakit->nama_penyakit];
            foreach (array_keys($ageGroups) as $key) { $rowData[$key] = ['L' => 0, 'P' => 0]; }
            $rowData['total'] = ['L' => 0, 'P' => 0];
            $reportData[$penyakit->id] = $rowData;
        }
        foreach ($records as $record) {
            $tglLahir = Carbon::parse($record->tanggal_lahir);
            $tglKunjungan = Carbon::parse($record->tanggal_kunjungan);
            $ageInDays = $tglLahir->diffInDays($tglKunjungan);
            $ageInYears = $tglLahir->diffInYears($tglKunjungan);
            $gender = $record->jenis_kelamin;
            foreach ($ageGroups as $key => $group) {
                $age = ($group['unit'] === 'day') ? $ageInDays : $ageInYears;
                if ($age >= $group['start'] && $age <= $group['end']) {
                    if (isset($reportData[$record->penyakit_id][$key][$gender])) {
                        $reportData[$record->penyakit_id][$key][$gender]++;
                        $reportData[$record->penyakit_id]['total'][$gender]++;
                    }
                    break;
                }
            }
        }

        $namaFile = "Laporan Surveilans Penyakit - {$periode}.xlsx";

        return Excel::download(new LaporanSurveilansExport($reportData, $periode, $tahun), $namaFile);
    }
}
