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
                <a>

                </a>
                <div class="mt-6">
                    <li class="flex items-start gap-2">
                        <span class="text-gray-600 dark:text-gray-300"></span>
                    </li>

                    <a href="{{ route('login') }}"
                        class="inline-block px-5 py-2 bg-[#1b1b18] text-white dark:bg-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-gray-100 transition">
                        Start Now!
                    </a>
                </div>
            </div>

            <div
                class="bg-[#f1f5f9] dark:bg-[#131313] w-full lg:w-[420px] p-4 rounded-t-lg lg:rounded-r-lg flex items-center justify-center">
                <img src="{{ asset('images/laboratrack.png') }}" alt="Logo"
                    class="h-12 sm:h-16 md:h-20 lg:h-24 w-auto" />
            </div>
        </main>
    </div>
</body>

</html>
