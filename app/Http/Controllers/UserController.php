<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        try {
            // Ambil semua user kecuali yang memiliki role admin
            $users = User::where('role', '!=', 'admin')->get();
            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data users'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|in:manager,sales'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => strtolower($request->role) // Memastikan format role lowercase
            ]);

            return response()->json([
                'message' => 'User berhasil dibuat',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat user baru'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Mencegah penghapusan akun admin
            if ($user->role === 'admin') {
                return response()->json(['message' => 'Tidak dapat menghapus akun admin'], 403);
            }

            $user->delete();
            return response()->json(['message' => 'User berhasil dihapus'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus user'], 500);
        }
    }
} 