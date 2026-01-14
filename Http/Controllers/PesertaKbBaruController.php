<?php

namespace App\Http\Controllers;

use App\Models\PesertaKbBaru;
use App\Models\Posyandu;
use Illuminate\Http\Request;

class PesertaKbBaruController extends Controller
{
    public function index()
    {
        $query = PesertaKbBaru::with('posyandu')->latest();

        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $records = $query->paginate(10);

        return view('pages.apps.pustu.keluarga_berencana.index', compact('records'));
    }

    public function create()
    {
        if (Posyandu::count() === 0) {
            return redirect()->route('peserta-kb.index')
                ->with('error_posyandu', 'Data Posyandu kosong. Silakan isi terlebih dahulu.');
        }

        $query = Posyandu::query();
        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }
        $posyanduList = $query->orderBy('nama_posyandu')->get();
        return view('pages.apps.pustu.keluarga_berencana.create', compact('posyanduList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'posyandu_id' => 'required|exists:posyandus,id',
            'nama_pasien' => 'required|string|max:255',
            'tanggal_pelayanan' => 'required|date',
            'jenis_kontrasepsi' => 'required|in:PIL,SUNTIK,KONDOM,IUD,IMPLAN,MOW,MOP',
            'jalur_layanan' => 'required|in:UMUM,BPJS/K,PASCA SALIN',
        ]);

        $data['user_id'] = auth()->user()->id;
        PesertaKbBaru::create($data);

        return redirect()->route('peserta-kb.index')->with('success', 'Data Peserta KB Baru berhasil ditambahkan.');
    }

    public function edit(PesertaKbBaru $peserta_kb)
    {
        $query = Posyandu::query();
        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }
        $posyanduList = $query->orderBy('nama_posyandu')->get();
        return view('pages.apps.pustu.keluarga_berencana.edit', compact('peserta_kb', 'posyanduList'));
    }

    public function update(Request $request, PesertaKbBaru $peserta_kb)
    {
        $request->validate([
            'posyandu_id' => 'required|exists:posyandus,id',
            'nama_pasien' => 'required|string|max:255',
            'tanggal_pelayanan' => 'required|date',
            'jenis_kontrasepsi' => 'required|in:PIL,SUNTIK,KONDOM,IUD,IMPLAN,MOW,MOP',
            'jalur_layanan' => 'required|in:UMUM,BPJS/K,PASCA SALIN',
        ]);

        $peserta_kb->update($request->all());

        return redirect()->route('peserta-kb.index')->with('success', 'Data Peserta KB Baru berhasil diperbarui.');
    }

    public function destroy(PesertaKbBaru $peserta_kb)
    {
        $peserta_kb->delete();
        return redirect()->route('peserta-kb.index')->with('success', 'Data Peserta KB Baru berhasil dihapus.');
    }
}
