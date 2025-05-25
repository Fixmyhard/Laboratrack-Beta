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
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2" for="file">
                            Pilih File Excel (.xlsx)
                        </label>
                        <input type="file" name="file" id="file" accept=".xlsx" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Import
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-sm text-gray-600 dark:text-gray-400">
                    <strong>Catatan:</strong> File Excel harus memiliki format kolom berikut:
                    <span
                        class="font-mono bg-gray-200 px-2 py-1 rounded text-gray-800 dark:bg-gray-700 dark:text-white">
                        nama_karyawan, tanggal, jam_masuk, jam_keluar
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
