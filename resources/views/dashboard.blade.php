<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight text-center relative">
            {{ __('Beranda') }}
        </h2>
    </x-slot>
    <div class="py-12 mt-20">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-10">

            <!-- Welcome Message -->
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg p-8 border border-indigo-300 dark:border-indigo-700">
                <h3 class="text-3xl font-extrabold mb-4 text-indigo-600 dark:text-indigo-400 tracking-wide">Laboratrack
                </h3>
                <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
                    Gunakan Beranda ini untuk mengelola data karyawan, melihat laporan, dan memonitor data gaji.
                </p>
            </div>

            <!-- Info Cards -->

            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-gradient-to-r from-indigo-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 p-6 rounded-lg shadow-xl">
                <div
                    class="bg-blue-600 text-white rounded-lg p-8 shadow-xl flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-300 cursor-pointer relative">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M12 12a5 5 0 100-10 5 5 0 000 10z" />
                    </svg>

                    <div class="text-lg font-semibold tracking-wide">Total Karyawan</div>
                    <div class="text-5xl mt-4 font-extrabold drop-shadow-lg">{{ $jumlahKaryawan }}</div>
                    <p class="mt-3 text-sm text-blue-200">Data karyawan yang terdaftar.</p>
                </div>

                <div
                    class="bg-green-600 text-white rounded-lg p-8 shadow-xl flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-300 cursor-pointer relative group">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-3.866 0-7 1.79-7 4v4h14v-4c0-2.21-3.134-4-7-4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8V4m0 0l3 3m-3-3L9 7" />
                    </svg>

                    <div class="text-lg font-semibold tracking-wide">Total Gaji</div>

                    <div class="text-5xl mt-4 font-extrabold drop-shadow-lg max-w-xs truncate font-mono">
                        Rp {{ number_format($totalGaji, 0, ',', '.') }}
                    </div>

                    <p class="mt-3 text-sm text-green-200 text-center">Total gaji yang harus dibayarkan kepada karyawan.
                    </p>

                    <div
                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-3 px-3 py-1 bg-black bg-opacity-75 text-white text-sm rounded whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-50">
                        Rp {{ number_format($totalGaji, 0, ',', '.') }}
                    </div>
                </div>

            </div>
        </div>

        <!-- Navigasi -->
        <div
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg p-8 text-center border border-indigo-300 dark:border-indigo-700">
            <a href="{{ route('karyawans.index') }}"
                class="inline-block px-8 py-4 bg-indigo-600 text-white rounded-lg font-semibold text-lg hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg">
                Kelola Data Karyawan
            </a>
            <a href="{{ route('absensi.importForm') }}"
                class="inline-block px-8 py-4 bg-yellow-600 text-white rounded-lg font-semibold text-lg hover:bg-yellow-500 transition duration-300 shadow-md hover:shadow-lg">
                Import Data Absensi
            </a>

        </div>


    </div>
    </div>
</x-app-layout>
