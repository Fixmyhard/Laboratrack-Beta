<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight text-center">
            Edit Absensi Harian - {{ ucwords(strtolower($absensi->karyawan->nama ?? 'Karyawan')) }}
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

                <form method="POST" action="{{ route('absensi_harian.update', $absensi->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Tanggal --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Tanggal
                            </label>
                            <input type="date" name="tanggal"
                                value="{{ old('tanggal', $absensi->tanggal) }}"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Status Kehadiran
                            </label>
                            <select name="status_kehadiran" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                @php
                                    $status = old('status_kehadiran', $absensi->status_kehadiran);
                                @endphp
                                <option value="Hadir" {{ $status == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Sakit" {{ $status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Izin" {{ $status == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Tidak Hadir" {{ $status == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                            </select>
                        </div>

                        {{-- Jam Masuk --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Jam Masuk
                            </label>
                            {{-- PERBAIKAN: Format H:i untuk menghilangkan detik --}}
                            <input type="time" name="jam_masuk"
                                value="{{ old('jam_masuk', \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i')) }}"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Jam Pulang --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Jam Pulang
                            </label>
                            {{-- PERBAIKAN: Format H:i untuk menghilangkan detik --}}
                            <input type="time" name="jam_pulang"
                                value="{{ old('jam_pulang', $absensi->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') : '') }}"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                    </div>

                    {{-- Informasi Default (Opsional, hanya untuk info user) --}}
                    <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-700 rounded text-sm text-gray-600 dark:text-gray-300">
                        <p><strong>Info Perhitungan:</strong></p>
                        <ul class="list-disc list-inside">
                            <li>Batas Masuk: {{ $absensi->default_masuk ?? '07:30' }} (Lewat dari ini dihitung terlambat)</li>
                            <li>Batas Pulang: {{ $absensi->default_pulang ?? '16:00' }} (Pulang sebelum ini dihitung pulang cepat)</li>
                        </ul>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('absensi_harian.index') }}"
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