<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Absensi Harian
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert --}}
            @if (session('error'))
                <div class="bg-red-500 text-white px-4 py-2 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-500 text-white px-4 py-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- SECTION FILTER & TOOLBAR --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
                
                {{-- Form Filter --}}
                <form method="GET" action="{{ route('absensi_harian.index') }}" class="flex flex-wrap items-end gap-4 mb-4">
                    
                    {{-- Dropdown Nama Karyawan (PENAMBAHAN BARU) --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Karyawan</label>
                        <select name="id_karyawan" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawans_all as $k)
                                <option value="{{ $k->id_karyawan }}" {{ request('id_karyawan') == $k->id_karyawan ? 'selected' : '' }}>
                                    {{ $k->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Bulan --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                        <select name="bulan" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">Semua Bulan</option>
                            @foreach(range(1, 12) as $m)
                                @php $val = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                <option value="{{ $val }}" {{ request('bulan') == $val ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Tahun --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                        <select name="tahun" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">Semua Tahun</option>
                            @foreach(range(date('Y'), date('Y')-5) as $y)
                                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Filter --}}
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-bold text-sm">
                        Filter
                    </button>
                    
                    {{-- Tombol Reset (UPDATE: Mendeteksi id_karyawan juga) --}}
                    @if(request('bulan') || request('tahun') || request('id_karyawan'))
                        <a href="{{ route('absensi_harian.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded font-bold text-sm">
                            Reset
                        </a>
                    @endif
                </form>

                <hr class="my-4 border-gray-300 dark:border-gray-600">

                {{-- Action Buttons (Import & Create) --}}
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-bold">Info Default:</span>
                        Masuk <span class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">07:30</span> -
                        Pulang <span class="font-mono bg-gray-100 dark:bg-gray-700 px-1 rounded">16:00</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('absensi_harian.create') }}"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow-sm transition flex items-center gap-2 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah
                        </a>

                        <a href="{{ route('absensi_harian.importForm') }}"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded shadow-sm transition flex items-center gap-2 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Import
                        </a>
                    </div>
                </div>
            </div>

            {{-- TABEL DATA --}}
            <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">No</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">Tanggal</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">Nama Karyawan</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">Status</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">Jam Masuk</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">Terlambat</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">Jam Pulang</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">Pulang Cepat</th>
                            <th class="p-3 text-center text-gray-700 dark:text-gray-200">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($data as $i => $a)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="p-3 text-center">{{ $loop->iteration }}</td>
                                <td class="p-3 text-center">{{ \Carbon\Carbon::parse($a->tanggal)->format('d-m-Y') }}</td>
                                <td class="p-3 text-center font-medium">{{ $a->karyawan->nama ?? '-' }}</td>
                                
                                {{-- Status Badge --}}
                                <td class="p-3 text-center">
                                    @php
                                        $color = 'bg-gray-100 text-gray-800'; 
                                        if($a->status_kehadiran == 'Hadir') $color = 'bg-green-100 text-green-800';
                                        elseif($a->status_kehadiran == 'Sakit') $color = 'bg-yellow-100 text-yellow-800';
                                        elseif($a->status_kehadiran == 'Izin') $color = 'bg-blue-100 text-blue-800';
                                        elseif($a->status_kehadiran == 'Tidak Hadir') $color = 'bg-red-100 text-red-800';
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                        {{ $a->status_kehadiran ?? '-' }}
                                    </span>
                                </td>

                                <td class="p-3 text-center font-mono text-green-600">
                                    {{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}
                                </td>

                                <td class="p-3 text-center font-semibold {{ $a->terlambat_menit > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                    {{ $a->terlambat_teks }}
                                </td>

                                <td class="p-3 text-center font-mono text-blue-600">
                                    {{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '-' }}
                                </td>

                                <td class="p-3 text-center font-semibold {{ $a->pulang_cepat_menit > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                    {{ $a->pulang_cepat_teks }}
                                </td>

                                <td class="p-3 text-center whitespace-nowrap space-x-2">
                                    <a href="{{ route('absensi_harian.edit', $a->id) }}"
                                        class="inline-block px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded text-sm transition">
                                        Edit
                                    </a>

                                    <form action="{{ route('absensi_harian.destroy', $a->id) }}" method="POST"
                                        class="inline-block" onsubmit="return confirm('Yakin hapus data ini?');">
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
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    Data tidak ditemukan. Coba ubah filter atau lakukan Import/Tambah Manual.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>