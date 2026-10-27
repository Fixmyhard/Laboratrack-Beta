<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight text-center">
            Edit Absensi - {{ ucwords(strtolower($absensi->nama)) }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg p-6">

                {{-- SUCCESS MESSAGE --}}
                @if (session('success'))
                    <div class="mb-4 text-green-600 font-semibold">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- VALIDATION ERROR --}}
                @if ($errors->any())
                    <div class="mb-4 text-red-600 font-semibold">
                        Terjadi kesalahan:
                        <ul class="mt-2 list-disc list-inside text-sm text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('absensi.update', $absensi->id_absensi) }}">
                    @csrf
                    @method('PUT')

                    {{-- KEEP FILTER --}}
                    <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                    <input type="hidden" name="tahun" value="{{ request('tahun') }}">

                    {{-- GRID FORM --}}
                    <div class="grid grid-cols-2 gap-4">

                        {{-- Keterlambatan jumlah --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Keterlambatan (jumlah)
                            </label>
                            <input type="number" name="keterlambatan_jumlah"
                                value="{{ old('keterlambatan_jumlah', $absensi->keterlambatan_jumlah) }}"
                                min="0"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Keterlambatan menit --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Keterlambatan (menit)
                            </label>
                            <input type="number" name="keterlambatan_menit"
                                value="{{ old('keterlambatan_menit', $absensi->keterlambatan_menit) }}"
                                min="0"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Keluar awal jumlah --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Keluar Awal (jumlah)
                            </label>
                            <input type="number" name="keluar_awal_jumlah"
                                value="{{ old('keluar_awal_jumlah', $absensi->keluar_awal_jumlah) }}"
                                min="0"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Keluar awal menit --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Keluar Awal (menit)
                            </label>
                            <input type="number" name="keluar_awal_menit"
                                value="{{ old('keluar_awal_menit', $absensi->keluar_awal_menit) }}"
                                min="0"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Kehadiran --}}
                        <div class="col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Realisasi Kehadiran
                            </label>
                            <input type="number" name="realisasi_kehadiran"
                                value="{{ old('realisasi_kehadiran', $absensi->realisasi_kehadiran) }}"
                                min="0"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="flex justify-between mt-6">
                        <a href="{{ route('absensi.index', ['bulan' => request('bulan'), 'tahun' => request('tahun')]) }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Kembali
                        </a>

                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Update
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
