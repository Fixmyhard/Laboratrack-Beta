<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight text-center relative">
            {{ __('Beranda') }}
        </h2>
    </x-slot>

    <div class="py-12 mt-20">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-10">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg p-8 border border-indigo-300 dark:border-indigo-700">
                <h3 class="text-3xl font-extrabold mb-4 text-indigo-600 dark:text-indigo-400 tracking-wide">
                    Laboratrack
                </h3>
                <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
                    Gunakan Beranda ini untuk mengelola data karyawan, melihat laporan, dan memonitor data gaji.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-gradient-to-r from-indigo-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 p-6 rounded-lg shadow-xl">

                <div class="bg-blue-600 text-white rounded-lg p-8 shadow-xl flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-300 cursor-pointer relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M12 12a5 5 0 100-10 5 5 0 000 10z" />
                    </svg>
                    <div class="text-lg font-semibold tracking-wide">Total Karyawan</div>
                    <div class="text-5xl mt-4 font-extrabold drop-shadow-lg">{{ $jumlahKaryawan }}</div>
                    <p class="mt-3 text-sm text-blue-200">Data karyawan yang terdaftar.</p>
                </div>

                <div class="bg-green-600 text-white rounded-lg p-8 shadow-xl flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-300 cursor-pointer relative group w-full">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-3.866 0-7 1.79-7 4v4h14v-4c0-2.21-3.134-4-7-4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8V4m0 0l3 3m-3-3L9 7" />
                    </svg>

                    <div class="text-lg font-semibold tracking-wide text-center">Total Gaji Bersih</div>

                    <div id="salary-container" class="w-full flex justify-center items-center mt-4 overflow-hidden" style="height: 60px;">
                        <div id="salary-text" 
                             class="font-extrabold drop-shadow-lg font-mono text-center whitespace-nowrap inline-block" 
                             style="font-size: 3rem; line-height: 1;">
                            Rp {{ number_format($totalGaji, 0, ',', '.') }}
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-green-200 text-center">
                        Total gaji bulan ini yang harus dibayar.
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById('salary-container');
            const text = document.getElementById('salary-text');

            function adjustFontSize() {
                if (!container || !text) return;

                // Reset ke ukuran awal dulu setiap kali fungsi dijalankan
                let fontSize = 48; // Setara 3rem (text-5xl)
                text.style.fontSize = fontSize + 'px';

                // Ambil lebar kontainer yang tersedia (dikurangi sedikit padding aman)
                const maxWidth = container.clientWidth - 10;

                // Kurangi ukuran font secara bertahap sampai teks muat di dalam kontainer
                while (text.scrollWidth > maxWidth && fontSize > 14) {
                    fontSize--;
                    text.style.fontSize = fontSize + 'px';
                }
            }

            // Jalankan saat pertama kali load
            adjustFontSize();

            // Jalankan ulang jika ukuran layar berubah (penting untuk tampilan responsif)
            window.addEventListener('resize', adjustFontSize);
        });
    </script>
</x-app-layout>