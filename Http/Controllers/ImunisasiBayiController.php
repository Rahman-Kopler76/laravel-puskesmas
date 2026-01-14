<?php

namespace App\Http\Controllers;

use App\Models\ImunisasiBayi;
use App\Models\JenisImunisasi;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use App\Exports\ImunisasiBayiExport;
use Maatwebsite\Excel\Facades\Excel;

class ImunisasiBayiController extends Controller
{
    public function index()
    {
        $query = ImunisasiBayi::with('jenisImunisasi')->latest();

        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $dataImunisasi = $query->paginate(10);
        return view('pages.apps.pustu.imunisasi.bayi.index', compact('dataImunisasi'));
    }

    public function create()
    {
        $posyanduExists = Posyandu::exists();

        if (!$posyanduExists) {
            return redirect()->route('imunisasi-bayi.index')
                ->with('show_posyandu_alert', true);
        }

        $query = Posyandu::query();
        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $posyanduList = $query->orderBy('nama_posyandu')->get();

        $imunisasiKecuali = ['TT1', 'TT2', 'TT3', 'TT4', 'TT5'];

        $jenisImunisasiList = JenisImunisasi::whereNotIn('nama_imunisasi', $imunisasiKecuali)->orderBy('nama_imunisasi')->get();

        return view('pages.apps.pustu.imunisasi.bayi.create', compact('jenisImunisasiList', 'posyanduList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_bayi' => 'required|string|max:255',
            'posyandu_id' => 'required|exists:posyandus,id',
            'nama_orang_tua' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat_lengkap' => 'required|string',
            'nik_orang_tua' => 'nullable|string|max:255',
            'nik_bayi' => 'nullable|string|max:255',
            'jenis_imunisasi_id' => 'nullable|exists:jenis_imunisasi,id',
        ]);

        $data['user_id'] = auth()->user()->id;

        ImunisasiBayi::create($data);

        return redirect()->route('imunisasi-bayi.index')->with('success', 'Data Imunisasi Bayi berhasil ditambahkan.');
    }

    public function edit(ImunisasiBayi $imunisasiBayi)
    {
        $query = Posyandu::query();
        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $posyanduList = $query->orderBy('nama_posyandu')->get();

        $imunisasiKecuali = ['TT1', 'TT2', 'TT3', 'TT4', 'TT5'];

        $jenisImunisasiList = JenisImunisasi::whereNotIn('nama_imunisasi', $imunisasiKecuali)->orderBy('nama_imunisasi')->get();

        return view('pages.apps.pustu.imunisasi.bayi.edit', compact('imunisasiBayi', 'jenisImunisasiList', 'posyanduList'));
    }

    public function update(Request $request, ImunisasiBayi $imunisasiBayi)
    {
        $request->validate([
            'nama_bayi' => 'required|string|max:255',
            'posyandu_id' => 'required|exists:posyandus,id',
            'nama_orang_tua' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat_lengkap' => 'required|string',
            'nik_orang_tua' => 'nullable|string|max:255',
            'nik_bayi' => 'nullable|string|max:255',
            'jenis_imunisasi_id' => 'nullable|exists:jenis_imunisasi,id',
        ]);

        $imunisasiBayi->update($request->all());

        return redirect()->route('imunisasi-bayi.index')->with('success', 'Data Imunisasi Bayi berhasil diperbarui.');
    }

    public function destroy(ImunisasiBayi $imunisasiBayi)
    {
        $imunisasiBayi->delete();

        return redirect()->route('imunisasi-bayi.index')->with('success', 'Data Imunisasi Bayi berhasil dihapus.');
    }

    public function export()
    {
        $data = ImunisasiBayi::with('jenisImunisasi')->get()->groupBy('nama_posyandu');
        return Excel::download(new ImunisasiBayiExport($data), 'data_imunisasi_bayi_' . date('Y-m-d') . '.xlsx');
    }
}