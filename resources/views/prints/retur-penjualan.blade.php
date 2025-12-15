<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Retur Penjualan - {{ $returPenjualan->nomor_retur }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
            color: #666;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .info-box {
            width: 48%;
        }

        .info-box h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #444;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        .info-item {
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 11px;
        }

        .table td {
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #333;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #333;
            padding-top: 8px;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 30%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }

        .print-info {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px dashed #999;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .container {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .print-info {
                display: none;
            }
        }

        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }

        .print-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }

        .print-btn:hover {
            background-color: #0056b3;
        }

        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background-color: #545b62;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print</button>
        <a href="{{ route('retur-penjualan.index') }}" class="back-btn">← Kembali</a>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>RETUR PENJUALAN</h1>
            <h2>{{ $returPenjualan->nomor_retur }}</h2>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <!-- Informasi Retur -->
            <div class="info-box">
                <h3>Informasi Retur</h3>
                <div class="info-item">
                    <span class="info-label">Tanggal Retur:</span>
                    {{ \Carbon\Carbon::parse($returPenjualan->tanggal_retur)->format('d/m/Y') }}
                </div>
                <div class="info-item">
                    <span class="info-label">No. Penjualan:</span>
                    {{ $returPenjualan->penjualan->nomor_penjualan }}
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Penjualan:</span>
                    {{ \Carbon\Carbon::parse($returPenjualan->penjualan->tanggal_penjualan)->format('d/m/Y') }}
                </div>
                <div class="info-item">
                    <span class="info-label">Gudang:</span>
                    {{ $returPenjualan->gudang->nama_gudang }}
                </div>
                <div class="info-item">
                    <span class="info-label">Dibuat Oleh:</span>
                    {{ $returPenjualan->dibuatOleh->name }}
                </div>
            </div>

            <!-- Informasi Customer -->
            <div class="info-box">
                <h3>Informasi Customer</h3>
                <div class="info-item">
                    <span class="info-label">Nama Customer:</span>
                    {{ $returPenjualan->penjualan->customer->nama_customer }}
                </div>
                <div class="info-item">
                    <span class="info-label">Telepon:</span>
                    {{ $returPenjualan->penjualan->customer->telepon ?? '-' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Alamat:</span>
                    {{ $returPenjualan->penjualan->customer->alamat ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Detail Items Table -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Nama Barang</th>
                    <th style="width: 10%;">Satuan</th>
                    <th style="width: 10%;">Qty Jual</th>
                    <th style="width: 10%;">Qty Retur</th>
                    <th style="width: 12%;">Harga</th>
                    <th style="width: 12%;">Total</th>
                    <th style="width: 16%;">Alasan Retur</th>
                </tr>
            </thead>
            <tbody>
                @php $totalRetur = 0; @endphp
                @foreach ($returPenjualan->details as $index => $detail)
                    @php $totalRetur += $detail->total; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $detail->barang->nama_barang }}</td>
                        <td class="text-center">{{ $detail->satuan->nama_satuan }}</td>
                        <td class="text-center">{{ number_format($detail->qty_jual, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($detail->qty, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($detail->total, 0, ',', '.') }}</td>
                        <td>{{ $alasanReturLabels[$detail->alasan_retur] ?? $detail->alasan_retur }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row total">
                <span>Total Retur:</span>
                <span>Rp {{ number_format($totalRetur, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer dengan tanda tangan -->
        <div class="footer">
            <div class="signature-box">
                <div>Dibuat Oleh:</div>
                <div class="signature-line">{{ $returPenjualan->dibuatOleh->name }}</div>
            </div>
            <div class="signature-box">
                <div>Customer:</div>
                <div class="signature-line">{{ $returPenjualan->penjualan->customer->nama_customer }}</div>
            </div>
            <div class="signature-box">
                <div>Manajer:</div>
                <div class="signature-line">
                    (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                </div>
            </div>
        </div>

        <!-- Print Info -->
        <div class="print-info">
            Dicetak pada: {{ $printDate }} | Sistem Inventory Toko
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }

        // Print function
        function printDocument() {
            window.print();
        }
    </script>
</body>

</html>
