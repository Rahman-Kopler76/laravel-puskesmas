<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function showRegisterForm()
    {
        return view('pages.apps.petugas.users.users_page_add');
    }


    public function registerPustu(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 2,
        ]);

        return redirect()->route('usersData')->with('success', 'User created successfully');
    }

    public function settingAccount() {
        $users = User::find(auth()->user()->id);
        return view('pages.apps.petugas.users.setting_data_user', compact('users'));
    }

    public function showUserData()
    {
        $users = User::with('role')->where('role_id', 2)->get();
        return view('pages.apps.petugas.users.users_page', compact('users'));
    }

    public function editUserData(string $id)
    {
        $users = User::findOrFail($id);
        return view('pages.apps.petugas.users.users_page_edit', compact('users'));
    }


    public function updateUserData(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('usersData')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->back()->with('success', 'Akun berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus akun.');
        }
    }

}