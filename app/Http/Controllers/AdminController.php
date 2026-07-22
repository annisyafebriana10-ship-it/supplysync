<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        // Mengambil jumlah data untuk ditampilkan di kartu ringkasan admin
        $totalUsers = DB::table('users')->count();
        $totalPorts = DB::table('ports')->count();
        $totalArticles = DB::table('articles')->count();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalPorts' => $totalPorts,
            'totalArticles' => $totalArticles,
        ]);
    }

    // =========================================================================
    // 1. MANAJEMEN PENGGUNA (USERS)
    // =========================================================================

    public function users()
    {
        // Ambil semua data user, urutkan dari yang terbaru
        $users = DB::table('users')->orderBy('created_at', 'desc')->get();
        return view('admin.users', ['users' => $users]);
    }

    public function storeUser(Request $request)
    {
        // 1. Validasi input dari form
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // Pastikan email tidak ganda
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
        ]);

        // 2. Simpan ke database dengan password yang dienkripsi
        DB::table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Enkripsi password
            'role' => $request->role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Kembalikan ke halaman tabel dengan pesan sukses
        return redirect()->route('admin.users')->with('success', 'Pengguna baru berhasil ditambahkan dan siap digunakan!');
    }

    public function updateUser(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id, // Email boleh sama asal milik user ini sendiri
            'role' => 'required|in:admin,user',
        ]);

        // Siapkan data yang akan di-update
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'updated_at' => now(),
        ];

        // Jika form password diisi, berarti admin ingin mengganti passwordnya juga
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Eksekusi update
        DB::table('users')->where('id', $id)->update($updateData);

        return redirect()->route('admin.users')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    public function deleteUser($id)
    {
        DB::table('users')->where('id', $id)->delete();
        return redirect()->route('admin.users')->with('success', 'Akun pengguna berhasil dihapus dari sistem!');
    }


    // =========================================================================
    // 2. MANAJEMEN DATASET PELABUHAN (PORTS)
    // =========================================================================

    public function ports()
    {
        // Ambil data pelabuhan dan gabungkan (join) dengan tabel negara untuk mendapatkan nama negaranya
        $ports = DB::table('ports')
            ->join('countries', 'ports.country_id', '=', 'countries.id')
            ->select('ports.*', 'countries.name as country_name')
            ->orderBy('ports.name', 'asc')
            ->get();

        // Ambil daftar 195 negara untuk ditampilkan di dropdown Select pada form tambah/edit
        $countries = DB::table('countries')->orderBy('name', 'asc')->get();

        return view('admin.ports', [
            'ports' => $ports,
            'countries' => $countries
        ]);
    }

    public function storePort(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        DB::table('ports')->insert([
            'name' => $request->name,
            'country_id' => $request->country_id,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.ports')->with('success', 'Titik pelabuhan baru berhasil ditambahkan!');
    }

    public function updatePort(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        DB::table('ports')->where('id', $id)->update([
            'name' => $request->name,
            'country_id' => $request->country_id,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.ports')->with('success', 'Data pelabuhan berhasil diperbarui!');
    }

    public function deletePort($id)
    {
        DB::table('ports')->where('id', $id)->delete();
        return redirect()->route('admin.ports')->with('success', 'Pelabuhan berhasil dihapus dari sistem!');
    }


    // =========================================================================
    // 3. MANAJEMEN ARTIKEL ANALISIS (ARTICLES)
    // =========================================================================

    public function articles()
    {
        // Ambil artikel beserta nama penulisnya (join dengan tabel users)
        $articles = DB::table('articles')
            ->join('users', 'articles.author_id', '=', 'users.id')
            ->select('articles.*', 'users.name as author_name')
            ->orderBy('articles.created_at', 'desc')
            ->get();

        return view('admin.articles', ['articles' => $articles]);
    }

    public function storeArticle(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Karena kita belum mengaktifkan fitur Login (Auth), 
        // kita ambil ID dari akun admin pertama di database sebagai penulis sementara agar sistem tidak error.
        $adminId = DB::table('users')->where('role', 'admin')->value('id');

        DB::table('articles')->insert([
            'title' => $request->title,
            'content' => $request->content, // Pastikan hanya ada SATU baris ini
            'author_id' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.articles')->with('success', 'Artikel analisis berhasil diterbitkan!');
    }

    public function updateArticle(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        DB::table('articles')->where('id', $id)->update([
            'title' => $request->title,
            'content' => $request->content, // Pastikan hanya ada SATU baris ini
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.articles')->with('success', 'Artikel berhasil diperbarui!');
    }

    public function deleteArticle($id)
    {
        DB::table('articles')->where('id', $id)->delete();
        return redirect()->route('admin.articles')->with('success', 'Artikel berhasil dihapus!');
    }
}