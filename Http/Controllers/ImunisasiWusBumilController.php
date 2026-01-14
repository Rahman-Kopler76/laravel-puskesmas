<?php

namespace App\Http\Controllers;

use App\Models\ImunisasiWusBumil;
use App\Models\JenisImunisasi;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use App\Exports\ImunisasiWusBumilExport;
use Maatwebsite\Excel\Facades\Excel;

class ImunisasiWusBumilController extends Controller
{
    public function index()
    {
        $query = ImunisasiWusBumil::with(['posyandu', 'jenisImunisasi'])->latest();

        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $dataImunisasi = $query->paginate(10);

        return view('pages.apps.pustu.imunisasi.bumil.index', compact('dataImunisasi'));
    }

    public function create()
    {
        $posyanduExists = Posyandu::exists();

        if (!$posyanduExists) {
            return redirect()->route('imunisasi-wus-bumil.index')
                ->with('show_posyandu_alert', true);
        }

        $query = Posyandu::query();
        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $posyanduList = $query->orderBy('nama_posyandu')->get();

        $imunisasiKhususBumil = ['TT1', 'TT2', 'TT3', 'TT4', 'TT5'];

        $jenisImunisasiList = JenisImunisasi::whereIn('nama_imunisasi', $imunisasiKhususBumil)->orderBy('nama_imunisasi')->get();

        return view('pages.apps.pustu.imunisasi.bumil.create', compact('jenisImunisasiList', 'posyanduList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'posyandu_id' => 'required|exists:posyandus,id',
            'nama_wus_bumil' => 'required|string|max:255',
            'nama_suami' => 'required|string|max:255',
            'umur' => 'required|integer|min:0',
            'hamil_ke' => 'nullable|integer|min:0',
            'jenis_imunisasi_id' => 'required|exists:jenis_imunisasi,id',
            'alamat_lengkap' => 'required|string',
            'nik' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = auth()->user()->id;
        ImunisasiWusBumil::create($data);

        return redirect()->route('imunisasi-wus-bumil.index')->with('success', 'Data Imunisasi WUS/Bumil berhasil ditambahkan.');
    }

    public function edit(ImunisasiWusBumil $imunisasiWusBumil)
    {
        $query = Posyandu::query();
        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $posyanduList = $query->orderBy('nama_posyandu')->get();


        $imunisasiKhususBumil = ['TT1', 'TT2', 'TT3', 'TT4', 'TT5'];

        $jenisImunisasiList = JenisImunisasi::whereIn('nama_imunisasi', $imunisasiKhususBumil)->orderBy('nama_imunisasi')->get();

        return view('pages.apps.pustu.imunisasi.bumil.edit', compact('imunisasiWusBumil', 'jenisImunisasiList', 'posyanduList'));
    }

    public function update(Request $request, ImunisasiWusBumil $imunisasiWusBumil)
    {
        $request->validate([
            'posyandu_id' => 'required|exists:posyandus,id',
            'nama_wus_bumil' => 'required|string|max:255',
            'nama_suami' => 'required|string|max:255',
            'umur' => 'required|integer|min:0',
            'hamil_ke' => 'nullable|integer|min:0',
            'jenis_imunisasi_id' => 'required|exists:jenis_imunisasi,id',
            'alamat_lengkap' => 'required|string',
            'nik' => 'nullable|string|max:255',
        ]);

        $imunisasiWusBumil->update($request->all());

        return redirect()->route('imunisasi-wus-bumil.index')->with('success', 'Data Imunisasi WUS/Bumil berhasil diperbarui.');
    }

    public function destroy(ImunisasiWusBumil $imunisasiWusBumil)
    {
        $imunisasiWusBumil->delete();

        return redirect()->route('imunisasi-wus-bumil.index')->with('success', 'Data Imunisasi WUS/Bumil berhasil dihapus.');
    }

    public function export()
    {
        $data = ImunisasiWusBumil::with('jenisImunisasi')->get()->groupBy('nama_posyandu');
        return Excel::download(new ImunisasiWusBumilExport($data), 'data_imunisasi_wus_bumil_' . date('Y-m-d') . '.xlsx');
    }
}