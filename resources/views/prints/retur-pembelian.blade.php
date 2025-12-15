<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Retur Pembelian - {{ $returPembelian->nomor_retur }}</title>
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
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-label {
            width: 120px;
            font-weight: bold;
        }

        .info-value {
            flex: 1;
        }

        .table-container {
            margin: 20px 0;
        }

        .table-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }

        td {
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 150px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }

        .notes {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }

        .notes h4 {
            margin-bottom: 5px;
            font-size: 12px;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .container {
                margin: 0;
                padding: 0;
            }

            @page {
                margin: 15mm;
                size: A4;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>RETUR PEMBELIAN</h1>
            <h2>{{ $returPembelian->nomor_retur }}</h2>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <h3>Informasi Retur</h3>
                <div class="info-row">
                    <div class="info-label">Nomor Retur:</div>
                    <div class="info-value">{{ $returPembelian->nomor_retur }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Retur:</div>
                    <div class="info-value">{{ $returPembelian->tanggal_retur_formatted }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Gudang:</div>
                    <div class="info-value">{{ $returPembelian->gudang->nama_gudang ?? '-' }}</div>
                </div>
                @if ($returPembelian->keterangan)
                    <div class="info-row">
                        <div class="info-label">Keterangan:</div>
                        <div class="info-value">{{ $returPembelian->keterangan }}</div>
                    </div>
                @endif
            </div>

            <div class="info-box">
                <h3>Informasi Pembelian</h3>
                <div class="info-row">
                    <div class="info-label">No. Pembelian:</div>
                    <div class="info-value">{{ $returPembelian->pembelian->nomor_pembelian }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Supplier:</div>
                    <div class="info-value">{{ $returPembelian->pembelian->supplier->nama_supplier ?? '-' }}</div>
                </div>
                @if ($returPembelian->pembelian->supplier->no_hp ?? null)
                    <div class="info-row">
                        <div class="info-label">No. HP:</div>
                        <div class="info-value">{{ $returPembelian->pembelian->supplier->no_hp }}</div>
                    </div>
                @endif
                <div class="info-row">
                    <div class="info-label">Dibuat oleh:</div>
                    <div class="info-value">{{ $returPembelian->dibuatOleh->name ?? '-' }}</div>
                </div>
                @if ($returPembelian->disetujui_pada)
                    <div class="info-row">
                        <div class="info-label">Disetujui oleh:</div>
                        <div class="info-value">{{ $returPembelian->disetujuiOleh->name ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tgl Disetujui:</div>
                        <div class="info-value">{{ $returPembelian->disetujui_pada_formatted }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Detail Items -->
        <div class="table-container">
            <div class="table-title">Detail Item yang Diretur</div>
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="30%">Nama Barang</th>
                        <th width="10%">Satuan</th>
                        <th width="10%">Qty Retur</th>
                        <th width="15%">Harga Satuan</th>
                        <th width="15%">Total</th>
                        <th width="15%">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returPembelian->details as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                            <td class="text-center">{{ $detail->satuan->nama_satuan ?? '-' }}</td>
                            <td class="text-right">{{ number_format($detail->qty_retur, 2) }}</td>
                            <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                            <td class="text-center">
                                {{ $alasanReturLabels[$detail->alasan_retur] ?? $detail->alasan_retur }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" class="text-right"><strong>TOTAL RETUR:</strong></td>
                        <td class="text-right"><strong>Rp
                                {{ number_format($returPembelian->total_nilai_retur, 0, ',', '.') }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($returPembelian->keterangan)
            <!-- Notes -->
            <div class="notes">
                <h4>Catatan:</h4>
                <p>{{ $returPembelian->keterangan }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="signature-box">
                <div>Dibuat oleh:</div>
                <div class="signature-line">
                    {{ $returPembelian->dibuatOleh->name ?? '-' }}
                </div>
            </div>

            <div style="text-align: right; font-size: 10px; color: #666;">
                <div>Dicetak pada:</div>
                <div>{{ $printDate }}</div>
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
