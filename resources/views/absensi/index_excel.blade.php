<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight text-center">
            {{ __('Data Absensi Karyawan') }}
        </h2>
    </x-slot>

    <div class="container mx-auto px-4">

        {{-- ALERT SUCCESS --}}
        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-100 text-green-800 px-4 py-3 dark:bg-green-900 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- FILTER + TOMBOL IMPORT --}}
        <div class="flex flex-wrap items-end gap-3 mb-4">
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Hari</label>
                <select name="hari" form="filterForm"
                    class="px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <option value="">Semua Hari</option>

                    @for ($d = 1; $d <= 31; $d++)
                        @php
                            $val = str_pad($d, 2, '0', STR_PAD_LEFT);
                        @endphp
                        <option value="{{ $val }}" {{ request('hari') == $val ? 'selected' : '' }}>
                            {{ $val }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Dropdown Bulan --}}
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                <select name="bulan" form="filterForm"
                    class="px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <option value="">Semua Bulan</option>
                    @foreach ([
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ] as $key => $value)
                        <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Dropdown Tahun --}}
            <div>
                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                <select name="tahun" form="filterForm"
                    class="px-3 py-2 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <option value="">Semua Tahun</option>
                    @php
                        $currentYear = date('Y');
                    @endphp
                    @for ($y = $currentYear; $y >= $currentYear - 5; $y--)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Tombol FILTER --}}
            <form method="GET" action="{{ route('absensi.index') }}" id="filterForm">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow-sm transition">
                    Filter
                </button>
            </form>

            {{-- TOMBOL IMPORT ABSENSI --}}
            <a href="{{ route('absensi.importForm') }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow-sm transition">
                Import Absensi
            </a>

        </div>

        {{-- TABEL DATA --}}
        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-center">No</th>
                        <th class="px-6 py-3 text-center">Nama Karyawan</th>
                        <th class="px-6 py-3 text-center">Keterlambatan (x)</th>
                        <th class="px-6 py-3 text-center">Keterlambatan</th>
                        <th class="px-6 py-3 text-center">Keluar Awal (x)</th>
                        <th class="px-6 py-3 text-center">Keluar Awal</th>
                        <th class="px-6 py-3 text-center">Kehadiran</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($absensis as $absensi)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 text-center">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-center">{{ ucwords(strtolower($absensi->nama)) }}</td>

                            {{-- Keterlambatan Jumlah --}}
                            <td class="px-6 py-4 text-center">{{ $absensi->keterlambatan_jumlah ?? 0 }}</td>

                            {{-- Keterlambatan Waktu (Pakai Accessor) --}}
                            <td class="px-6 py-4 text-center text-red-600 font-medium">
                                {{ $absensi->keterlambatan_teks }}
                            </td>

                            {{-- Keluar Awal Jumlah --}}
                            <td class="px-6 py-4 text-center">{{ $absensi->keluar_awal_jumlah ?? 0 }}</td>

                            {{-- Keluar Awal Waktu (Pakai Accessor) --}}
                            <td class="px-6 py-4 text-center text-orange-600 font-medium">
                                {{ $absensi->keluar_awal_teks }}
                            </td>

                            <td class="px-6 py-4 text-center font-semibold">
                                {{ $absensi->realisasi_kehadiran }} / {{ $absensi->target_kehadiran }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap space-x-2">
                                <a href="{{ route('absensi.edit', ['id' => $absensi->id_absensi, 'bulan' => request('bulan'), 'tahun' => request('tahun')]) }}"
                                    class="inline-block px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded text-sm transition">
                                    Edit
                                </a>

                                <form action="{{ route('absensi.destroy', $absensi->id_absensi) }}" method="POST"
                                    class="inline-block" onsubmit="return confirm('Yakin hapus absensi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-block px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                Data absensi tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>