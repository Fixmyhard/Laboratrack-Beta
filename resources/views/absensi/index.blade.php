<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight text-center">
            Rekap Absensi Bulanan
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-8">

        {{-- ALERT --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        {{-- FILTER & ACTION --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
            <form method="GET" action="{{ route('absensi.index') }}" class="flex flex-wrap items-end gap-4">
                
                {{-- Dropdown Bulan --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                    <select name="bulan" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">-- Pilih --</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ request('bulan') == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown Tahun --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                    <select name="tahun" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">-- Pilih --</option>
                        @foreach(range(date('Y'), date('Y')-5) as $y)
                            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter (Lihat Data) --}}
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-bold">
                    Lihat Data
                </button>
            </form>

            @if(request('bulan') && request('tahun') && $absensis->count() > 0)
            <a href="{{ route('absensi.cetak', ['bulan' => request('bulan'), 'tahun' => request('tahun')]) }}" 
               target="_blank"
               class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded font-bold flex items-center gap-2 shadow">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Cetak PDF
            </a>
        @endif

            <hr class="my-4 border-gray-300 dark:border-gray-600">

            {{-- Tombol HITUNG ULANG (SYNC) --}}
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    *Klik tombol di kanan untuk menarik data dari Absensi Harian ke Rekap Bulanan.
                </p>
                @if(request('bulan') && request('tahun'))
                    <form action="{{ route('absensi.rekapitulasi') }}" method="POST">
                        @csrf
                        <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                        <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-bold shadow flex items-center gap-2" onclick="return confirm('Proses ini akan menghitung ulang rekap berdasarkan data harian. Lanjutkan?')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Hitung Rekap dari Harian
                        </button>
                    </form>
                @else
                    <button disabled class="px-4 py-2 bg-gray-400 text-white rounded font-bold cursor-not-allowed">
                        Pilih Bulan & Tahun Dulu
                    </button>
                @endif
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-center border-r dark:border-gray-600" rowspan="2">No</th>
                        <th class="px-4 py-3 text-left border-r dark:border-gray-600" rowspan="2">Nama Karyawan</th>
                        
                        {{-- Group Status Kehadiran --}}
                        <th class="px-4 py-2 text-center border-b dark:border-gray-600 border-r" colspan="4">Status Kehadiran (Hari)</th>
                        
                        {{-- Group Keterlambatan --}}
                        <th class="px-4 py-2 text-center border-b dark:border-gray-600 border-r" colspan="2">Keterlambatan</th>
                        
                        {{-- Group Pulang Cepat --}}
                        <th class="px-4 py-2 text-center border-b dark:border-gray-600" colspan="2">Pulang Cepat</th>
                    </tr>
                    <tr>
                        {{-- Subkolom Status --}}
                        {{-- PERBAIKAN: Tambahkan warna background gelap yang sesuai untuk header --}}
                        <th class="px-2 py-2 text-center bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 border-r border-gray-200 dark:border-gray-600">H</th>
                        <th class="px-2 py-2 text-center bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 border-r border-gray-200 dark:border-gray-600">S</th>
                        <th class="px-2 py-2 text-center bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 border-r border-gray-200 dark:border-gray-600">I</th>
                        <th class="px-2 py-2 text-center bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border-r border-gray-200 dark:border-gray-600">A</th>

                        {{-- Subkolom Terlambat --}}
                        <th class="px-4 py-2 text-center border-r dark:border-gray-600">Freq</th>
                        <th class="px-4 py-2 text-center border-r dark:border-gray-600">Durasi</th>

                        {{-- Subkolom Pulang Cepat --}}
                        <th class="px-4 py-2 text-center border-r dark:border-gray-600">Freq</th>
                        <th class="px-4 py-2 text-center">Durasi</th>
                    </tr>
                </thead>

                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($absensis as $a)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 text-center border-r dark:border-gray-700 dark:text-gray-300">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium border-r dark:border-gray-700 dark:text-gray-200">
                                {{ $a->nama }}
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $a->departemen }}</div>
                            </td>

                            {{-- STATUS DENGAN WARNA KONTRAS DARK MODE --}}
                            {{-- Hadir --}}
                            <td class="px-2 py-3 text-center font-bold text-green-700 bg-green-100 dark:text-green-300 dark:bg-green-900 border-r dark:border-gray-700">
                                {{ $a->hadir }}
                            </td>
                            {{-- Sakit --}}
                            <td class="px-2 py-3 text-center font-bold text-yellow-700 bg-yellow-100 dark:text-yellow-300 dark:bg-yellow-900 border-r dark:border-gray-700">
                                {{ $a->sakit }}
                            </td>
                            {{-- Izin --}}
                            <td class="px-2 py-3 text-center font-bold text-blue-700 bg-blue-100 dark:text-blue-300 dark:bg-blue-900 border-r dark:border-gray-700">
                                {{ $a->izin }}
                            </td>
                            {{-- Alpha --}}
                            <td class="px-2 py-3 text-center font-bold text-red-700 bg-red-100 dark:text-red-300 dark:bg-red-900 border-r dark:border-gray-700">
                                {{ $a->alpha }}
                            </td>

                            {{-- TERLAMBAT --}}
                            <td class="px-4 py-3 text-center border-r dark:border-gray-700 dark:text-gray-300">
                                {{ $a->keterlambatan_jumlah }} x
                            </td>
                            <td class="px-4 py-3 text-center text-red-600 dark:text-red-400 border-r dark:border-gray-700 font-medium">
                                {{ $a->terlambat_teks }}
                            </td>

                            {{-- PULANG CEPAT --}}
                            <td class="px-4 py-3 text-center border-r dark:border-gray-700 dark:text-gray-300">
                                {{ $a->keluar_awal_jumlah }} x
                            </td>
                            <td class="px-4 py-3 text-center text-orange-600 dark:text-orange-400 font-medium">
                                {{ $a->pulang_cepat_teks }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Data belum ada. Silakan pilih Bulan & Tahun lalu klik "Hitung Rekap dari Harian".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>