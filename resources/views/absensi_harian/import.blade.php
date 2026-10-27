<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight text-center">
            {{ __('Import Absensi Harian') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg p-6">

                {{-- Alert Error --}}
                @if (session('error'))
                    <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 bg-green-100 text-green-700 p-3 rounded">
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

                <form action="{{ route('absensi_harian.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Form Input File - Desain disamakan dengan Absensi Bulanan --}}
                    <div class="mb-4">
                        <label for="file" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                            Pilih File Excel (.xlsx) (Sheet: Catatan)
                        </label>
                        <input type="file" name="file" id="file" required accept=".xlsx, .xls"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white" />
                         <p class="text-xs text-gray-500 mt-2">Pastikan file excel memiliki sheet bernama "Catatan".</p>
                    </div>

                    <div class="flex justify-between mt-6">
                        <a href="{{ route('absensi_harian.index') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Kembali
                        </a>
                        
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                            Import Data Harian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>