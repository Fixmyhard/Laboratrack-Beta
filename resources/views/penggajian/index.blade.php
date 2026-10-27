<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight text-center">
            Penggajian Karyawan
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-8">

        {{-- ALERT --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        {{-- INFO TOLERANSI --}}
        <div class="mb-6 bg-blue-50 dark:bg-gray-700 border-l-4 border-blue-500 p-4 rounded shadow-sm">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    <strong>Informasi Aturan:</strong> Toleransi keterlambatan adalah <strong>15 menit</strong>
                    (maksimal 07:45)
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
            {{-- FORM FILTER & ACTION --}}
            <div class="flex flex-col md:flex-row justify-between items-end gap-4">

                {{-- Kiri: Filter --}}
                <form method="GET" action="{{ route('penggajian.index') }}" class="flex items-end gap-3">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                        <select name="bulan" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}"
                                    {{ $bulan == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                        <select name="tahun" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                            @foreach (range(date('Y'), date('Y') - 2) as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded font-bold">
                        Lihat Data
                    </button>
                </form>

                {{-- Kanan: Tombol Hitung --}}
                <div class="flex items-end gap-2">
                    <a href="{{ route('penggajian.cetak', ['bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-bold shadow flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan (PDF)
                    </a>

                    <form action="{{ route('penggajian.hitung') }}" method="POST">
                        @csrf
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-bold shadow"
                            onclick="return confirm('Generate ulang gaji?')">
                            Hitung / Generate
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-center border-r dark:border-gray-600">No</th>
                        <th class="px-4 py-3 text-left border-r dark:border-gray-600">Nama Karyawan</th>
                        <th class="px-4 py-3 text-right border-r dark:border-gray-600">Gaji Pokok</th>
                        <th class="px-4 py-3 text-center border-r dark:border-gray-600">
                            Pinalti Jam<br><span class="text-xs text-gray-500 dark:text-gray-400">(Telat + Cepat +
                                Alpa)</span>
                        </th>
                        <th class="px-4 py-3 text-right border-r dark:border-gray-600 text-red-600">Total Potongan</th>
                        <th class="px-4 py-3 text-right font-bold text-green-600 border-r dark:border-gray-600">Total
                            Gaji</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($dataGaji as $gaji)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 text-center border-r dark:border-gray-700 dark:text-gray-300">
                                {{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium border-r dark:border-gray-700 dark:text-gray-200">
                                {{ $gaji->karyawan->nama ?? 'Unknown' }}
                                @if ($gaji->jumlah_alpa > 0)
                                    <span
                                        class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-full font-bold">Alpa:
                                        {{ $gaji->jumlah_alpa }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right border-r dark:border-gray-700 dark:text-gray-300">
                                Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center border-r dark:border-gray-700 dark:text-gray-300">
                                @php
                                    $jamAlpa = $gaji->jumlah_alpa * 9;
                                    $totalJam = $gaji->jumlah_terlambat + $gaji->jumlah_pulang_cepat + $jamAlpa;
                                @endphp
                                <span class="font-bold">{{ $totalJam }} Jam</span>
                            </td>
                            <td
                                class="px-4 py-3 text-right text-red-600 dark:text-red-400 border-r dark:border-gray-700">
                                Rp {{ number_format($gaji->total_potongan, 0, ',', '.') }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400 border-r dark:border-gray-700">
                                Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('penggajian.slip', $gaji->id_gaji) }}" target="_blank"
                                    class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded shadow-sm transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Slip Gaji
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada data gaji untuk periode ini. Silakan klik tombol "Hitung / Generate Gaji".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
