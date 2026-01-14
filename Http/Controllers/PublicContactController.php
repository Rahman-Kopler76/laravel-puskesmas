<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class PublicContactController extends Controller
{
    /**
     * Menyimpan pesan dari formulir kontak publik.
     */
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // 2. Simpan data ke database
        Contact::create($request->all());

        // 3. Kembalikan ke halaman kontak dengan pesan sukses
        return redirect()->route('contact')->with('success', 'Pesan Anda telah berhasil terkirim. Terima kasih!');
    }
}