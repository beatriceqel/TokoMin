<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Upload File - TokoMini</title>
</head>
<body>
    <div style="max-width: 500px; margin: 50px auto; font-family: Arial, sans-serif;">
        <h2>Form Upload File & Dokumen</h2>

        {{-- Menampilkan pesan sukses jika upload berhasil --}}
        @if(session('success'))
            <p style="color: green; background: #e2fcd9; padding: 10px; border-radius: 5px;">
                {{ session('success') }}
            </p>
        @endif

        {{-- Menampilkan error validasi jika ada --}}
        @if($errors->any())
            <div style="color: red; background: #fcd9d9; padding: 10px; border-radius: 5px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Upload (Wajib menggunakan enctype="multipart/form-data") --}}
        <form method="POST" action="{{ route('file.upload') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 15px;">
                <label for="document">Pilih File (JPG, PNG, PDF, DOCX):</label><br>
                <input type="file" name="document" id="document" required style="margin-top: 5px;">
            </div>
            
            <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Upload File
            </button>
        </form>
    </div>
</body>
</html>