<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight text-center">
            Tambah Absensi Harian
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg p-6">

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

                <form method="POST" action="{{ route('absensi_harian.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Pilih Karyawan --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Nama Karyawan
                            </label>
                            <select name="id_karyawan" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($karyawan as $k)
                                    <option value="{{ $k->id_karyawan }}" {{ old('id_karyawan') == $k->id_karyawan ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->nik ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tanggal --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Tanggal
                            </label>
                            <input type="date" name="tanggal"
                                value="{{ old('tanggal', date('Y-m-d')) }}"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Status Kehadiran --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Status Kehadiran
                            </label>
                            <select name="status_kehadiran" id="status_kehadiran" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="Hadir" {{ old('status_kehadiran') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Sakit" {{ old('status_kehadiran') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Izin" {{ old('status_kehadiran') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Tidak Hadir" {{ old('status_kehadiran') == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                            </select>
                        </div>

                        {{-- Jam Masuk --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Jam Masuk
                            </label>
                            <input type="time" name="jam_masuk"
                                value="{{ old('jam_masuk') }}"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Jam Pulang --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Jam Pulang
                            </label>
                            <input type="time" name="jam_pulang"
                                value="{{ old('jam_pulang') }}"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                    </div>

                    <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-700 rounded text-sm text-gray-600 dark:text-gray-300">
                        <p><strong>Catatan:</strong></p>
                        <ul class="list-disc list-inside">
                            <li>Jika memilih <strong>Sakit/Izin</strong>, Jam Masuk & Pulang boleh dikosongkan.</li>
                            <li>Perhitungan <strong>Terlambat</strong> dan <strong>Pulang Cepat</strong> akan otomatis dilakukan sistem.</li>
                        </ul>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('absensi_harian.index') }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Kembali
                        </a>

                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Simpan Data
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>