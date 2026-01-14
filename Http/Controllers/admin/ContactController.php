<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Beranda;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Menampilkan form untuk mengedit informasi kontak.
     */
    public function edit()
    {
        $kontak = Beranda::firstOrCreate(['id' => 1]);
        return view('pages.apps.petugas.setting.contact.contact', compact('kontak'));
    }

    /**
     * Mengupdate informasi kontak di database.
     */
    public function update(Request $request)
    {
        $request->validate([
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string',
            'google_maps_link' => 'nullable|url',
        ]);

        $kontak = Beranda::find(1);
        $kontak->update($request->only([
            'contact_phone',
            'contact_email',
            'contact_address',
            'google_maps_link'
        ]));

        return redirect()->route('admin.contact.edit')->with('success', 'Informasi kontak berhasil diperbarui!');
    }
}