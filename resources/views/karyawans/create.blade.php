<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight text-center relative">
            {{ __('Tambah Karyawan') }}
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-6">
        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-4 text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('karyawans.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
                <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>

            {{-- <div>
                <label for="jabatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" required value="{{ old('jabatan') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div> --}}

            <div>
                <label for="jabatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Jabatan
                </label>

                <input type="hidden" name="jabatan" id="jabatan" value="{{ old('jabatan') }}">

                <x-dropdown width="48">
                    <x-slot name="trigger">
                        <button type="button"
                            class="w-full flex justify-between items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 
                                rounded-md border dark:border-gray-600 text-left">
                            <span id="selectedJabatan">
                                {{ old('jabatan') ?: 'Pilih Jabatan' }}
                            </span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @foreach ($daftarJabatan as $j)
                            <x-dropdown-link
                                onclick="document.getElementById('jabatan').value='{{ $j }}'; 
                                        document.getElementById('selectedJabatan').innerText='{{ $j }}';">

                                {{ $j }}
                            </x-dropdown-link>
                        @endforeach
                    </x-slot>
                </x-dropdown>
            </div>

            <div>
                <label for="gaji_pokok" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gaji
                    Pokok</label>
                <input type="text" name="gaji_pokok" id="gaji_pokok" required value="{{ old('gaji_pokok') }}"
                    class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 w-full max-w-xl"
                    style="width: 900px;" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="\d+"
                    title="Hanya boleh memasukkan angka" />
            </div>

            <div class="flex space-x-4">
                <button type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Simpan
                </button>
                <a href="{{ route('karyawans.index') }}"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
