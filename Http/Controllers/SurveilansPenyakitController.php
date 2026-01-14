<?php

namespace App\Http\Controllers;

use App\Models\SurveilansPenyakit;
use App\Models\Penyakit;
use Illuminate\Http\Request;

class SurveilansPenyakitController extends Controller
{
    public function index()
    {
        $query = SurveilansPenyakit::with('penyakit')->latest();

        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $records = $query->paginate(10);

        return view('pages.apps.pustu.penyakit.index', compact('records'));
    }

    public function create()
    {
        $penyakitList = Penyakit::orderBy('nama_penyakit')->get();
        return view('pages.apps.pustu.penyakit.create', compact('penyakitList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'penyakit_id' => 'required|exists:penyakit,id',
            'nama_pasien' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_kunjungan' => 'required|date|before_or_equal:today',
        ]);

        $data['user_id'] = auth()->user()->id;
        SurveilansPenyakit::create($data);

        return redirect()->route('surveilans-penyakit.index')->with('success', 'Data Surveilans berhasil ditambahkan.');
    }

    public function edit(SurveilansPenyakit $surveilans_penyakit)
    {
        $penyakitList = Penyakit::orderBy('nama_penyakit')->get();
        return view('pages.apps.pustu.penyakit.edit', compact('surveilans_penyakit', 'penyakitList'));
    }

    public function update(Request $request, SurveilansPenyakit $surveilans_penyakit)
    {
        $request->validate([
            'penyakit_id' => 'required|exists:penyakit,id',
            'nama_pasien' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_kunjungan' => 'required|date|before_or_equal:today',
        ]);

        $surveilans_penyakit->update($request->all());

        return redirect()->route('surveilans-penyakit.index')->with('success', 'Data Surveilans berhasil diperbarui.');
    }

    public function destroy(SurveilansPenyakit $surveilans_penyakit)
    {
        $surveilans_penyakit->delete();
        return redirect()->route('surveilans-penyakit.index')->with('success', 'Data Surveilans berhasil dihapus.');
    }
}