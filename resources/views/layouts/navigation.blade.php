<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

            <!-- KIRI -->
            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">

                    <a class="store-brand" href="{{ auth()->user()
                        ? (auth()->user()->role === 'admin'
                            ? route('admin.dashboard')
                            : route('kasir.dashboard'))
                        : url('/') }}">
                        <span class="store-brand-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M4 9.5h16l-1 11H5L4 9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m3 9.5 2-6h14l2 6M9 9.5a3 3 0 0 0 6 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="store-brand-name">Toko<span>Mini</span><small>Point of sale</small></span>
                    </a>

                </div>


                <!-- MENU DESKTOP -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    @auth

                        {{-- ========================= --}}
                        {{-- MENU ADMIN --}}
                        {{-- ========================= --}}

                        @if(auth()->user()->role === 'admin')

                            <x-nav-link
                                :href="route('admin.dashboard')"
                                :active="request()->routeIs('admin.dashboard')"
                            >
                                {{ __('Dashboard Admin') }}
                            </x-nav-link>


                            <x-nav-link
                                :href="route('products.index')"
                                :active="request()->routeIs('products.*')"
                            >
                                {{ __('Produk') }}
                            </x-nav-link>

                            <x-nav-link
                                :href="route('categories.index')"
                                :active="request()->routeIs('categories.*')"
                            >
                                {{ __('Kategori') }}
                            </x-nav-link>


                            <x-nav-link
                                :href="route('transactions.index')"
                                :active="request()->routeIs('transactions.index') || request()->routeIs('transactions.show')"
                            >
                                {{ __('Riwayat Transaksi') }}
                            </x-nav-link>

                        @endif


                        {{-- ========================= --}}
                        {{-- MENU KASIR --}}
                        {{-- ========================= --}}

                        @if(auth()->user()->role === 'kasir')

                            <x-nav-link
                                :href="route('kasir.dashboard')"
                                :active="request()->routeIs('kasir.dashboard')"
                            >
                                {{ __('Dashboard Kasir') }}
                            </x-nav-link>


                            <x-nav-link
                                :href="route('transactions.create')"
                                :active="request()->routeIs('transactions.create')"
                            >
                                {{ __('Transaksi Baru') }}
                            </x-nav-link>


                            <x-nav-link
                                :href="route('transactions.index')"
                                :active="request()->routeIs('transactions.index') || request()->routeIs('transactions.show')"
                            >
                                {{ __('Riwayat Transaksi') }}
                            </x-nav-link>

                        @endif

                    @endauth

                </div>

            </div>


            <!-- ========================= -->
            <!-- USER DROPDOWN -->
            <!-- ========================= -->

            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                        >

                            <div>
                                {{ Auth::user()->name }}
                            </div>

                            <div class="ms-1">

                                <svg
                                    class="fill-current h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                >

                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />

                                </svg>

                            </div>

                        </button>

                    </x-slot>


                    <x-slot name="content">

                        <!-- ROLE -->

                        <div class="px-4 py-2 border-b border-gray-100">

                            <div class="text-xs text-gray-500">
                                Login sebagai
                            </div>

                            <div class="font-semibold text-gray-800">

                                {{ ucfirst(Auth::user()->role) }}

                            </div>

                        </div>


                        <!-- LOGOUT -->

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >

                            @csrf

                            <x-dropdown-link
                                :href="route('logout')"
                                onclick="
                                    event.preventDefault();
                                    this.closest('form').submit();
                                "
                            >

                                {{ __('Log Out') }}

                            </x-dropdown-link>

                        </form>

                    </x-slot>

                </x-dropdown>

            </div>


            <!-- ========================= -->
            <!-- HAMBURGER MOBILE -->
            <!-- ========================= -->

            <div class="-me-2 flex items-center sm:hidden">

                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                >

                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >

                        <path
                            :class="{
                                'hidden': open,
                                'inline-flex': ! open
                            }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <path
                            :class="{
                                'hidden': ! open,
                                'inline-flex': open
                            }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />

                    </svg>

                </button>

            </div>

        </div>

    </div>


    <!-- ========================= -->
    <!-- RESPONSIVE MOBILE MENU -->
    <!-- ========================= -->

    <div
        :class="{
            'block': open,
            'hidden': ! open
        }"
        class="hidden sm:hidden"
    >

        <div class="pt-2 pb-3 space-y-1">

            @auth

                {{-- ADMIN --}}

                @if(auth()->user()->role === 'admin')

                    <x-responsive-nav-link
                        :href="route('admin.dashboard')"
                        :active="request()->routeIs('admin.dashboard')"
                    >
                        {{ __('Dashboard Admin') }}
                    </x-responsive-nav-link>


                    <x-responsive-nav-link
                        :href="route('products.index')"
                        :active="request()->routeIs('products.*')"
                    >
                        {{ __('Produk') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link
                        :href="route('categories.index')"
                        :active="request()->routeIs('categories.*')"
                    >
                        {{ __('Kategori') }}
                    </x-responsive-nav-link>


                    <x-responsive-nav-link
                        :href="route('transactions.index')"
                        :active="request()->routeIs('transactions.index') || request()->routeIs('transactions.show')"
                    >
                        {{ __('Riwayat Transaksi') }}
                    </x-responsive-nav-link>

                @endif


                {{-- KASIR --}}

                @if(auth()->user()->role === 'kasir')

                    <x-responsive-nav-link
                        :href="route('kasir.dashboard')"
                        :active="request()->routeIs('kasir.dashboard')"
                    >
                        {{ __('Dashboard Kasir') }}
                    </x-responsive-nav-link>


                    <x-responsive-nav-link
                        :href="route('transactions.create')"
                        :active="request()->routeIs('transactions.create')"
                    >
                        {{ __('Transaksi Baru') }}
                    </x-responsive-nav-link>


                    <x-responsive-nav-link
                        :href="route('transactions.index')"
                        :active="request()->routeIs('transactions.index') || request()->routeIs('transactions.show')"
                    >
                        {{ __('Riwayat Transaksi') }}
                    </x-responsive-nav-link>

                @endif

            @endauth

        </div>


        <!-- RESPONSIVE USER -->

        <div class="pt-4 pb-1 border-t border-gray-200">

            <div class="px-4">

                <div class="font-medium text-base text-gray-800">
                    {{ Auth::user()->name }}
                </div>

                <div class="font-medium text-sm text-gray-500">
                    {{ Auth::user()->email }}
                </div>

                <div class="text-xs text-gray-500 mt-1">
                    Role: {{ ucfirst(Auth::user()->role) }}
                </div>

            </div>


            <div class="mt-3 space-y-1">

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="
                            event.preventDefault();
                            this.closest('form').submit();
                        "
                    >

                        {{ __('Log Out') }}

                    </x-responsive-nav-link>

                </form>

            </div>

        </div>

    </div>

</nav>