<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laboratrack') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="min-h-screen flex flex-col">

        {{-- Navbar --}}
        <nav class="bg-white dark:bg-gray-800 shadow-md w-full flex items-center h-16">

            <a href="{{ route('dashboard') }}" class="flex items-center select-none space-x-3 ml-4">
                <img src="{{ asset('images/laboratrack.png') }}" alt="Logo" class="h-10 w-auto" />
                <span class="text-xl font-semibold text-gray-900 dark:text-gray-100">Laboratrack</span>
            </a>

            <!-- Spacer flexible -->
            <div class="flex-1"></div>

            <!-- Menu Navigasi + Logout dalam container max-w-7xl agar tetap rapi -->
            <div class="max-w-7xl mx-auto flex items-center space-x-8 px-4 sm:px-6 lg:px-8">
                <div class="hidden md:flex space-x-8">
                    <a href="{{ route('dashboard') }}"
                        class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition">
                        Beranda
                    </a>
                    <a href="{{ route('karyawans.index') }}"
                        class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition">
                        Karyawan
                    </a>
                    
                    {{-- DROPDOWN ABSENSI --}}
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center text-gray-700 dark:text-gray-300 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                Absensi
                                <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link href="{{ route('absensi_harian.index') }}">
                                Absensi Harian
                            </x-dropdown-link>

                            <x-dropdown-link href="{{ route('absensi.index') }}">
                                Rekap Absensi Bulanan
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    {{-- MENU KALENDER (DIPINDAHKAN KELUAR DARI DROPDOWN) --}}
                    <a href="{{ route('kalender.index') }}"
                        class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition">
                        Kalender
                    </a>

                    <a href="{{ route('penggajian.index') }}"
                        class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition">
                        Penggajian
                    </a>
                    <a href="{{ route('bantuan') }}"
                        class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition">
                        Bantuan
                    </a>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="text-sm font-medium text-gray-700 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-500 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </nav>

        {{-- Page Heading --}}
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow-sm">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        {{-- Main Content --}}
        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer
            class="bg-white dark:bg-gray-800 shadow-inner text-center py-4 mt-auto text-sm text-gray-500 dark:text-gray-400 select-none">
            &copy; {{ date('Y') }} Laboratrack
        </footer>
    </div>
</body>

</html>