<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratrack</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="antialiased dark:bg-[#0F0F0F] bg-[#F8F8F8] text-gray-900 dark:text-gray-100">
    <header class="flex justify-end p-4">
        @if (Route::has('login'))
            <nav class="space-x-3">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="text-sm text-gray-700 dark:text-gray-300 hover:underline">Dashboard</a>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm text-gray-700 dark:text-gray-300 hover:underline">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="text-sm text-gray-700 dark:text-gray-300 hover:underline">Register</a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>

    <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-700">
        <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-5xl lg:flex-row">
            <div class="flex-1 p-6 pb-12 lg:p-16 bg-white dark:bg-[#161615] shadow-md rounded-lg lg:rounded-l-lg">
                <h1 class="mb-2 text-xl font-semibold">Welcome to Laboratrack</h1>
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    Streamline your team's productivity. Track attendance, calculate payroll, and manage employees
                    efficiently.
                </p>

                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-2">
                        <span class="mt-1 w-2 h-2 rounded-full bg-green-500"></span>
                        <span>Fingerprint-based attendance system</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>Automatic salary calculation</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 w-2 h-2 rounded-full bg-yellow-500"></span>
                        <span>Real-time reporting and employee monitoring</span>
                    </li>
                </ul>

                <div class="mt-6">
                    <a href="{{ route('login') }}"
                        class="inline-block px-5 py-2 bg-[#1b1b18] text-white dark:bg-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-gray-100 transition">
                        Start Now
                    </a>
                </div>
            </div>

            <div
                class="bg-[#f1f5f9] dark:bg-[#131313] w-full lg:w-[420px] p-4 rounded-t-lg lg:rounded-r-lg flex items-center justify-center">
                {{-- Placeholder Logo --}}
                <svg class="w-40 h-40 text-[#4A90E2] dark:text-[#00BFFF]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373
                        12-12S18.627 0 12 0zm0 22C6.486 22 2 17.514
                        2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10
                        10zm1-15h-2v6h6v-2h-4z" />
                </svg>
            </div>
        </main>
    </div>
</body>

</html>
