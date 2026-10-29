<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    // Menampilkan form upload file
    public function showForm()
    {
        return view('upload');
    }

    // Memproses upload file dan validasi sesuai modul praktikum
    public function upload(Request $request)
    {
        // 1. Validasi file (Sesuai modul Acara File Storage)
        $request->validate([
            'document' => 'required|file|mimes:jpg,png,pdf,docx|max:2048',
        ]);

        $file = $request->file('document');

        // 2. Mengubah nama file agar unik sebelum disimpan
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

        // 3. Menyimpan file ke storage disk public (folder storage/app/public/documents)
        $path = $file->storeAs('documents', $filename, 'public');

        return back()->with('success', "File berhasil diunggah! Disimpan pada: storage/{$path}");
    }
}