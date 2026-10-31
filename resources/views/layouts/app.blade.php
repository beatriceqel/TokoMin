<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'TokoMini') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="font-sans antialiased">

    <div class="min-h-screen">

        {{-- =============================== --}}
        {{-- NAVIGATION --}}
        {{-- =============================== --}}

        @include('layouts.navigation')


        {{-- =============================== --}}
        {{-- PAGE CONTENT --}}
        {{-- =============================== --}}

        <main>

            @yield('content')

        </main>

    </div>

    {{-- =============================== --}}
    {{-- REAL-TIME NOTIFICATION LISTENER --}}
    {{-- =============================== --}}
    @auth
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof window.Echo !== 'undefined') {
                window.Echo.private('user.{{ auth()->id() }}')
                    .listen('.order.placed', (e) => {
                        alert(`Order #${e.order_id} telah ditempatkan! Total: Rp ${e.total}`);
                        console.log('Order Placed:', e);
                    });
            } else {
                console.error('Laravel Echo belum terinisialisasi!');
            }
        });
    </script>
    @endauth

</body>

</html>