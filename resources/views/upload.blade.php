@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05); font-family: Arial, sans-serif;">
    <h2 style="margin-top: 0; color: #111827; font-size: 22px; font-weight: 700;">Form Upload File & Dokumen</h2>
    <p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">Unggah dokumen atau file pendukung transaksi dan produk ke sistem.</p>

    {{-- Menampilkan pesan sukses jika upload berhasil --}}
    @if(session('success'))
        <div style="color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Menampilkan error validasi jika ada --}}
    @if($errors->any())
        <div style="color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Upload (Wajib menggunakan enctype="multipart/form-data") --}}
    <form method="POST" action="{{ route('file.upload') }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 20px;">
            <label for="document" style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px;">
                Pilih File (JPG, PNG, PDF, DOCX):
            </label>
            <input type="file" name="document" id="document" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb; font-size: 14px; box-sizing: border-box;">
        </div>
        
        <button type="submit" style="padding: 12px 20px; background: #13825d; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 700; width: 100%; transition: background 0.2s;">
            Upload File
        </button>
    </form>
</div>
@endsection