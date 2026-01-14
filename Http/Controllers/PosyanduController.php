<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use Illuminate\Http\Request;

class PosyanduController extends Controller
{
    public function index()
    {
        $query = Posyandu::latest();

        if (auth()->user()->role_id == 2) {
            $query->where('user_id', auth()->id());
        }

        $data = $query->paginate(10);

        return view('pages.apps.pustu.imunisasi.posyandu.index', compact('data'));
    }

    public function create()
    {
        return view('pages.apps.pustu.imunisasi.posyandu.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_posyandu' => 'required|string|max:255|unique:posyandus,nama_posyandu,NULL,id,user_id,' . auth()->id(),
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        Posyandu::create($data);

        return redirect()->route('posyandu.index')->with('success', 'Data Posyandu berhasil ditambahkan.');
    }

    public function edit(Posyandu $posyandu)
    {
        if (auth()->user()->role_id == 2 && $posyandu->user_id !== auth()->id()) {
            abort(403);
        }
        return view('pages.apps.pustu.imunisasi.posyandu.edit', compact('posyandu'));
    }

    public function update(Request $request, Posyandu $posyandu)
    {
        if (auth()->user()->role_id == 2 && $posyandu->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'nama_posyandu' => 'required|string|max:255|unique:posyandus,nama_posyandu,' . $posyandu->id . ',id,user_id,' . auth()->id(),
        ]);

        $posyandu->update($request->all());

        return redirect()->route('posyandu.index')->with('success', 'Data Posyandu berhasil diperbarui.');
    }

    public function destroy(Posyandu $posyandu)
    {
        if (auth()->user()->role_id == 2 && $posyandu->user_id !== auth()->id()) {
            abort(403);
        }
        $posyandu->delete();
        return redirect()->route('posyandu.index')->with('success', 'Data Posyandu berhasil dihapus.');
    }
}
