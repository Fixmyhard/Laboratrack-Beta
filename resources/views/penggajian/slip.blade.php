<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $gaji->karyawan->nama }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #1a202c;
            line-height: 1.5;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            border: 1px solid #e2e8f0;
            padding: 30px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2d3748;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .details-table th,
        .details-table td {
            padding: 10px;
            border: 1px solid #cbd5e0;
            text-align: left;
        }

        .bg-gray {
            background-color: #edf2f7;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            font-size: 16px;
            background-color: #f7fafc;
        }

        .signature-container {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            text-align: center;
        }

        .signature-box {
            width: 200px;
        }

        @media print {
            .btn-print {
                display: none;
            }

            body {
                padding: 0;
            }

            .container {
                border: none;
            }
        }
    </style>
</head>

<body>
    <div class="btn-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #4a5568; color: white; border: none; border-radius: 5px; cursor: pointer;">Cetak
            Slip</button>
    </div>

    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 24px;">SLIP GAJI KARYAWAN</h1>
            <p style="margin: 5px 0;">Periode: {{ $namaBulan }} {{ $gaji->tahun }}</p>
        </div>

        <table class="info-table">
            <tr>
                <td width="15%"><strong>Nama</strong></td>
                <td width="35%">: {{ $gaji->karyawan->nama }}</td>
                <td width="15%"><strong>Jabatan</strong></td>
                <td width="35%">: {{ $gaji->karyawan->jabatan }}</td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>: Aktif</td>
                <td><strong>Tgl Cetak</strong></td>
                <td>: {{ date('d/m/Y') }}</td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr class="bg-gray">
                    <th>KETERANGAN</th>
                    <th class="text-right">JUMLAH (RP)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="text-right">{{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="bg-gray" style="font-size: 12px; padding: 5px 10px;"><strong>POTONGAN /
                            DENDA</strong></td>
                </tr>
                <tr>
                    <td>Denda Terlambat ({{ $gaji->jumlah_terlambat }} jam)</td>
                    <td class="text-right">- {{ number_format($gaji->jumlah_terlambat * 12000, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Denda Pulang Cepat ({{ $gaji->jumlah_pulang_cepat }} jam)</td>
                    <td class="text-right">- {{ number_format($gaji->jumlah_pulang_cepat * 12000, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Denda Alpa ({{ $gaji->jumlah_alpa }} hari x 9 jam)</td>
                    <td class="text-right">- {{ number_format($gaji->jumlah_alpa * 9 * 12000, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL GAJI BERSIH (THP)</td>
                    <td class="text-right" style="color: #2f855a;">{{ number_format($gaji->total_gaji, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="signature-container">
            <div class="signature-box">
                <p>Penerima,</p>
                <br><br><br>
                <p><strong>( {{ $gaji->karyawan->nama }} )</strong></p>
            </div>
            <div class="signature-box">
                <p>Pemilik,</p>
                <br><br><br>
                <p><strong>(............... )</strong></p>
            </div>
        </div>
    </div>
</body>

</html>
