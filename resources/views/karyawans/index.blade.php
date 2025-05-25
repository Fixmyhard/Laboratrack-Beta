<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight text-center relative">
            {{ __('Daftar Karyawan') }}
        </h2>
    </x-slot>

    <div class="container mx-auto px-4">
        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-100 text-green-800 px-4 py-3 dark:bg-green-900 dark:text-green-300"
                role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex gap-3 mb-4">
            <a href="{{ url('/dashboard') }}"
                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded shadow-sm transition">Beranda</a>

            <a href="{{ route('karyawans.create') }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow-sm transition">Tambah
                Karyawan</a>
        </div>

        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            No
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Nama
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            NIP
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Jabatan
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Gaji Pokok
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($karyawans as $karyawan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $karyawan->nama }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $karyawan->nip }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $karyawan->jabatan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                Rp {{ number_format($karyawan->gaji_pokok, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <a href="{{ route('karyawans.edit', $karyawan->id) }}"
                                    class="inline-block px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-black rounded text-sm transition">
                                    Edit
                                </a>
                                <form action="{{ route('karyawans.destroy', $karyawan->id) }}" method="POST"
                                    class="inline-block" onsubmit="return confirm('Yakin hapus karyawan ini?');">
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
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                Data karyawan kosong.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
