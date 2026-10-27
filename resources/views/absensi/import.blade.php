<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight text-center">
            {{ __('Import Data Absensi') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 text-green-600 font-semibold">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 text-red-600 font-semibold">
                        Ada kesalahan pada file yang diunggah:
                        <ul class="mt-2 list-disc list-inside text-sm text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('absensi.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Tambahan: Dropdown Bulan dan Tahun -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <!-- Dropdown Bulan -->
                        <div>
                            <label for="bulan" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Pilih Bulan
                            </label>
                            <select name="bulan" id="bulan" required
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                                <option value="">-- Pilih Bulan --</option>
                                @php
                                    $bulanIndo = [
                                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                    ];
                                    $currentMonth = date('m');
                                @endphp
                                @foreach($bulanIndo as $key => $val)
                                    <option value="{{ $key }}" {{ $currentMonth == $key ? 'selected' : '' }}>
                                        {{ $val }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dropdown Tahun -->
                        <div>
                            <label for="tahun" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                Pilih Tahun
                            </label>
                            <select name="tahun" id="tahun" required
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                                <option value="">-- Pilih Tahun --</option>
                                @php
                                    $currentYear = date('Y');
                                @endphp
                                @for($i = $currentYear; $i >= $currentYear - 5; $i--)
                                    <option value="{{ $i }}" {{ $currentYear == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="file" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                            Pilih File Excel (.xlsx)
                        </label>
                        <input type="file" name="file" id="file" accept=".xlsx" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white" />
                    </div>

                    <div class="flex justify-between mt-6">
                        <a href="{{ route('absensi.index', ['bulan' => request('bulan'), 'tahun' => request('tahun')]) }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Kembali
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Import
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-sm text-gray-600 dark:text-gray-400">
                    <strong>Catatan:</strong> File Excel harus memiliki format kolom berikut yang sesuai migrasi:<br>
                    <span
                        class="font-mono bg-gray-200 px-2 py-1 rounded text-gray-800 dark:bg-gray-700 dark:text-white">
                        no, nama, departemen, dibutuhkan, kenyataan, keterlambatan_jumlah, keterlambatan_menit
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    @if(session('error'))
    <script>
        alert("{{ session('error') }}");
    </script>
@endif

@if(session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
@endif

</x-app-layout>