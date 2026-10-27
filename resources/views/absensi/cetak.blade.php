<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Absensi - {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-data th,
        .table-data td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        .table-data th {
            background-color: #f0f0f0;
        }

        .text-left {
            text-align: left !important;
        }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-box p {
            margin-bottom: 60px;
        }

        /* Print Settings */
        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.history.back()" style="padding: 5px 10px; cursor: pointer;">&larr; Kembali</button>
    </div>

    <div class="header">
        <h1>LAPORAN REKAPITULASI ABSENSI KARYAWAN</h1>
        <p><strong>LABORATRACK</strong></p>
        <p>Periode: {{ $namaBulan }} {{ $tahun }}</p>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th rowspan="2" width="3%">No</th>
                <th rowspan="2" width="20%">Nama Karyawan</th>
                <th rowspan="2" width="10%">Jabatan</th> {{-- HEADER DIGANTI JADI JABATAN --}}
                <th colspan="4">Status Kehadiran</th>
                <th colspan="2">Keterlambatan</th>
                <th colspan="2">Pulang Cepat</th>
            </tr>
            <tr>
                <th width="5%">Hadir</th>
                <th width="5%">Sakit</th>
                <th width="5%">Izin</th>
                <th width="5%">Alpha</th>
                <th width="5%">Freq</th>
                <th width="10%">Total Waktu</th>
                <th width="5%">Freq</th>
                <th width="10%">Total Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($absensis as $a)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-left">{{ $a->nama }}</td>
                    <td class="text-left"> {{ $a->jabatan_asli ?? $a->departemen }}</td>
                    <td>{{ $a->hadir }}</td>
                    <td>{{ $a->sakit }}</td>
                    <td>{{ $a->izin }}</td>
                    <td>{{ $a->alpha }}</td>

                    <td>{{ $a->keterlambatan_jumlah }}x</td>
                    <td>
                        @if ($a->keterlambatan_menit > 0)
                            {{ $a->keterlambatan_menit }} mnt
                        @else
                            -
                        @endif
                    </td>

                    <td>{{ $a->keluar_awal_jumlah }}x</td>
                    <td>
                        @if ($a->keluar_awal_menit > 0)
                            {{ $a->keluar_awal_menit }} mnt
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">Data tidak ditemukan untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
        </div>
        <div class="signature-box">
            <p>Dibuat Oleh,</p>
            <br>
            <strong>( ........................... )</strong><br>
            Pemilik Usaha
        </div>
    </div>

</body>

</html>
