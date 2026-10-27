<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight text-center relative">
            {{ __('Bantuan Penggunaan Aplikasi Laboratrack') }}
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">
            <h3 class="text-lg font-semibold">📌 Tentang Aplikasi</h3>
            <p>
                Laboratrack adalah sistem informasi untuk memonitor absensi, data karyawan, dan manajemen gaji di
                bengkel atau usaha Anda.
            </p>

            <h3 class="text-lg font-semibold">👤 Pengelolaan Karyawan</h3>
            <ul class="list-disc ml-6">
                <li>Masuk ke menu <strong>Karyawan</strong>.</li>
                <li>Tekan tombol <strong>Tambah</strong> untuk menambahkan data baru.</li>
                <li>Edit atau hapus karyawan dengan tombol <strong>Edit</strong> / <strong>Hapus</strong>.</li>
            </ul>

            <h3 class="text-lg font-semibold">🕒 Absensi</h3>
            <p>
                Fitur absensi menggunakan data fingerprint atau input manual. Anda bisa melihat rekap absensi harian
                atau bulanan di dashboard.
            </p>

            <h3 class="text-lg font-semibold">💰 Gaji</h3>
            <p>
                Gaji akan dihitung otomatis berdasarkan data absensi. Anda bisa menambahkan tunjangan atau potongan
                sebelum menyimpan slip gaji.
            </p>

            <h3 class="text-lg font-semibold">📄 Laporan</h3>
            <p>
                Anda dapat mencetak atau mengunduh laporan dalam format PDF dari halaman laporan bulanan.
            </p>

            <h3 class="text-lg font-semibold">❓ Butuh Bantuan Lebih Lanjut?</h3>
            <p>
                Hubungi developer sistem dengan kirim email ke <a href="mailto:dariusdarren86@gmail.com"
                    class="text-indigo-500 hover:underline">dariusdarren86@gmail.com</a>.
            </p>
        </div>
    </div>
</x-app-layout>
