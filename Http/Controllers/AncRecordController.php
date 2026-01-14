<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AncRecord;
use App\Exports\IbuHamilExport;
use Maatwebsite\Excel\Facades\Excel;

class AncRecordController extends Controller
{
    public function index()
    {
        $query = AncRecord::latest();
        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->user()->id);
        }
        $records = $query->paginate(10);
        return view('pages.apps.pustu.ibu_hamil.index', compact('records'));
    }

    public function laporanIndex()
    {
        $query = AncRecord::latest();
        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->user()->id);
        }
        $records = $query->paginate(10);
        return view('pages.apps.pustu.ibu_hamil.laporan_ibu_hamil.index', compact('records'));
    }

    public function create()
    {
        $ancItems = AncRecord::getAncItems();
        $kunjunganTypes = AncRecord::getKunjunganTypes();
        return view('pages.apps.pustu.ibu_hamil.create', compact('ancItems', 'kunjunganTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rekam_medis' => 'required|string',
            'kohort' => 'required|string',
            'nama_pasien' => 'required|string',
            'alamat' => 'required|string',
            'nik' => 'required|string',
            'petugas' => 'required|string',
            'k1' => 'nullable|array',
            'k2' => 'nullable|array',
            'k3' => 'nullable|array',
            'k4' => 'nullable|array',
            'k5' => 'nullable|array',
            'k6' => 'nullable|array',
        ]);

        foreach (['k1', 'k2', 'k3', 'k4', 'k5', 'k6'] as $kunjungan) {
            $validated[$kunjungan] = json_encode($request->input($kunjungan, []));
        }

        $validated['user_id'] = auth()->user()->id;
        AncRecord::create($validated);

        return redirect()->route('anc.index')->with('success', 'Anc Record created successfully');
    }

    public function show(AncRecord $anc)
    {
        foreach (['k1', 'k2', 'k3', 'k4', 'k5', 'k6'] as $kunjungan) {
            $anc->$kunjungan = json_decode($anc->$kunjungan) ?? [];
        }
        return response()->json($anc);
    }

    public function edit(string $id)
    {
        $ancRecord = AncRecord::findOrFail($id);
        foreach (['k1', 'k2', 'k3', 'k4', 'k5', 'k6'] as $kunjungan) {
            $ancRecord->$kunjungan = json_decode($ancRecord->$kunjungan ?? '[]', true);
        }
        $ancItems = AncRecord::getAncItems();
        $kunjunganTypes = AncRecord::getKunjunganTypes();

        return view('pages.apps.pustu.ibu_hamil.edit', compact('ancRecord', 'ancItems', 'kunjunganTypes'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'rekam_medis' => 'required|string',
            'kohort' => 'required|string',
            'nama_pasien' => 'required|string',
            'alamat' => 'required|string',
            'nik' => 'required|string',
            'petugas' => 'required|string',
            'k1' => 'nullable|array',
            'k2' => 'nullable|array',
            'k3' => 'nullable|array',
            'k4' => 'nullable|array',
            'k5' => 'nullable|array',
            'k6' => 'nullable|array',
        ]);
        foreach (['k1', 'k2', 'k3', 'k4', 'k5', 'k6'] as $kunjungan) {
            $validated[$kunjungan] = json_encode($request->input($kunjungan, []));
        }

        try {
            $ancRecord = AncRecord::findOrFail($id);
            $ancRecord->update($validated);
            return redirect()->route('anc.index')->with('success', 'Anc Record updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Anc Record update failed');
        }
    }

    public function destroy(string $id)
    {
        $ancRecord = AncRecord::findOrFail($id);
        $ancRecord->delete();
        return redirect()->route('anc.index')->with('success', 'Data ANC berhasil dihapus!');
    }

    public function exportExcel(Request $request)
    {

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        // Mulai query
        $query = AncRecord::query();
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        // Ambil data ANC sesuai filter
        $records = $query->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->orderBy('created_at', 'desc')
            ->get();

        $namaBulan = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');
        $namaFile = "Laporan Ibu Hamil - {$namaBulan} {$tahun}.xlsx";

        return Excel::download(new IbuHamilExport($records, $namaBulan, $tahun), $namaFile);
    }
}
