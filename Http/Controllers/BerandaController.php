<?php

namespace App\Http\Controllers;

use App\Models\Beranda;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BerandaController extends Controller
{
    /**
     * Menampilkan halaman beranda publik.
     */
    public function index()
    {
        $beranda = Beranda::first();
        $services = Service::all();

        return view('pages.web.beranda-page', compact('beranda', 'services'));
    }

    /**
     * Menampilkan form untuk mengedit konten beranda.
     * Diasumsikan route ini diproteksi oleh middleware auth.
     */
    public function edit()
    {
        // Ambil data yang ada, atau buat baru jika belum ada.
        $beranda = Beranda::firstOrCreate(['id' => 1]);
        return view('pages.apps.petugas.setting.beranda.edit', compact('beranda'));
    }

    /**
     * Mengupdate konten beranda.
     */
    public function update(Request $request)
    {
        $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string',
            'about_title' => 'required|string|max:255',
            'about_description' => 'required|string',
            'about_points' => 'required|string',
            'about_image_1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'about_image_2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $beranda = Beranda::find(1);
        $data = $request->except('_token', '_method');

        $imageFields = ['about_image_1', 'about_image_2', 'feature_image'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($beranda->$field && Storage::disk('public')->exists($beranda->$field)) {
                    Storage::disk('public')->delete($beranda->$field);
                }
                $data[$field] = $request->file($field)->store('landing/img', 'public');
            }
        }

        $beranda->update($data);

        return redirect()->route('admin.beranda.edit')->with('success', 'Konten beranda berhasil diperbarui!');
    }
}