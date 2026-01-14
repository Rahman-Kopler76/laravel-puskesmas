<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LaporanPustuController extends Controller
{
    /**
     * Menampilkan halaman daftar Pustu untuk admin.
     */
    public function index()
    {
        // Pastikan hanya admin yang bisa mengakses
        if (auth()->user()->role_id != 1) {
            abort(403);
        }

        // Ambil semua user dengan role 2 (Pustu)
        $pustuUsers = User::where('role_id', 2)->orderBy('name')->paginate(10);

        return view('pages.apps.petugas.laporan_pustu.index', compact('pustuUsers'));
    }

    /**
     * Menampilkan halaman 'hub' laporan untuk satu user Pustu.
     */
    public function show(User $user)
    {
        // ... (method show tidak berubah)
        if (auth()->user()->role_id != 1) {
            abort(403, 'HANYA ADMIN YANG DAPAT MENGAKSES HALAMAN INI.');
        }
        return view('pages.apps.petugas.laporan_pustu.show', compact('user'));
    }
}
