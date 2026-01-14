<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Beranda;
use App\Models\Contact;
use App\Models\ImunisasiBayi;
use App\Models\PesertaKbBaru;
use App\Models\SurveilansPenyakit;
use App\Models\AncRecord;          // <-- Tambahkan ini
use Carbon\Carbon;

class LandingPageController extends Controller
{
    public function landingPage()
    {
        $userFilter = [];
        if (auth()->user()->role_id == 2) {
            $userFilter = ['user_id' => auth()->id()];
        }

        // --- STATISTIK KARTU (TOTAL) ---
        $imunisasiCount = ImunisasiBayi::where($userFilter)->count();
        $kbCount = PesertaKbBaru::where($userFilter)->count();
        $surveilansCount = SurveilansPenyakit::where($userFilter)->count();
        $ancCount = AncRecord::where($userFilter)->count();

        // --- GRAFIK TOTAL DATA (SEMUA WAKTU) ---
        $aktivitasLabels = ['Ibu Hamil (ANC)', 'Imunisasi Bayi', 'Peserta KB Baru', 'Surveilans'];
        $aktivitasData = [$ancCount, $imunisasiCount, $kbCount, $surveilansCount];

        // ===============================================================
        //     DATA BARU UNTUK WIDGET YANG LEBIH RAMAI
        // ===============================================================

        // 1. DATA UNTUK GRAFIK TREN 7 HARI TERAKHIR (LINE CHART)
        $trendLabels = [];
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trendLabels[] = $date->translatedFormat('D'); // Format hari (Sen, Sel, Rab, ...)

            // Menghitung total entri dari semua tabel pada hari tersebut
            $count = AncRecord::where($userFilter)->whereDate('created_at', $date)->count()
                + PesertaKbBaru::where($userFilter)->whereDate('tanggal_pelayanan', $date)->count()
                + SurveilansPenyakit::where($userFilter)->whereDate('tanggal_kunjungan', $date)->count();

            $trendData[] = $count;
        }

        // 2. DATA UNTUK DISTRIBUSI KB (DOUGHNUT CHART)
        $kbDistribution = PesertaKbBaru::where($userFilter)
            ->select('jenis_kontrasepsi', DB::raw('count(*) as total'))
            ->groupBy('jenis_kontrasepsi')
            ->pluck('total', 'jenis_kontrasepsi');

        $kbLabels = $kbDistribution->keys();
        $kbData = $kbDistribution->values();

        // 3. DATA UNTUK AKTIVITAS TERBARU
        $latestAnc = AncRecord::where($userFilter)->latest()->first();
        $latestKb = PesertaKbBaru::where($userFilter)->latest('tanggal_pelayanan')->first();
        $latestSurveilans = SurveilansPenyakit::where($userFilter)->latest('tanggal_kunjungan')->first();
        $latestImunisasi = ImunisasiBayi::where($userFilter)->latest()->first();

        return view('pages.apps.dashboard', compact(
            'imunisasiCount',
            'kbCount',
            'surveilansCount',
            'ancCount',
            'aktivitasLabels',
            'aktivitasData',
            'trendLabels',
            'trendData',
            'kbLabels',
            'kbData',
            'latestAnc',
            'latestKb',
            'latestSurveilans',
            'latestImunisasi'
        ));
    }

    public function tentangKami()
    {
        $beranda = Beranda::first();
        return view('pages.web.about', compact('beranda'));
    }

    public function kontak()
    {
        $beranda = Beranda::first();
        return view('pages.web.contact', compact('beranda'));
    }
}