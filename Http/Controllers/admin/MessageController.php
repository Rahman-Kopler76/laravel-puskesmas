<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Menampilkan daftar semua pesan yang masuk.
     */
    public function index()
    {
        $messages = Contact::latest()->paginate(10); // Ambil data terbaru, 10 per halaman
        return view('pages.apps.petugas.setting.contact.petugas_messages', compact('messages'));
    }

    /**
     * Menghapus pesan.
     */
    public function destroy(Contact $message)
    {
        $message->delete();
        return redirect()->route('admin.messages.index')->with('success', 'Pesan berhasil dihapus.');
    }
}