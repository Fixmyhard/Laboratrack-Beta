<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Gaji {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0; /* Warna abu muda untuk header tabel */
            text-align: center;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        /* Area Tanda Tangan */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            margin-top: 60px;
            border-bottom: 1px solid #000;
        }

        /* Tombol Cetak (Disembunyikan saat diprint) */
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn-print {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
            th { background-color: #ddd !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan / Simpan PDF</button>
    </div>

    <div class="header">
        <h1>LAPORAN PENGGAJIAN KARYAWAN</h1>
        <p>Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Karyawan</th>
                <th>Gaji Pokok</th>
                <th>Potongan (Jam)</th>
                <th>Total Potongan (Rp)</th>
                <th>Total Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse ($dataGaji as $gaji)
                @php 
                    $jamAlpa = $gaji->jumlah_alpa * 9; 
                    $totalJam = $gaji->jumlah_terlambat + $gaji->jumlah_pulang_cepat + $jamAlpa;
                    $grandTotal += $gaji->total_gaji;
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        {{ $gaji->karyawan->nama ?? 'Unknown' }}
                        @if($gaji->jumlah_alpa > 0)
                            <br><small>(Alpa: {{ $gaji->jumlah_alpa }} hari)</small>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $totalJam }} Jam</td>
                    <td class="text-right">Rp {{ number_format($gaji->total_potongan, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Data gaji belum digenerate untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">GRAND TOTAL PENGELUARAN GAJI</th>
                <th class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="signature-section">
        
        <div class="signature-box">

        </div>
        <div class="signature-box">
            <p>Dibuat Oleh,</p>
            <div class="signature-line"></div>
            <p>Pemilik Usaha</p>
        </div>
    </div>

</body>
</html>