<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $penjualan->nomor_penjualan }}</title>
    <style>
        @page {
            margin: 0;
        }
        * {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .print-only {
            display: none;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 30px;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 24px;
            font-weight: 700;
            color: #1e40af;
            margin: 0 0 5px 0;
        }
        .company-details {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-id {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 5px 0;
        }
        .invoice-date {
            color: #6b7280;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        .status-lunas {
            background-color: #d1fae5;
            color: #047857;
        }
        .status-belum-lunas {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .status-partial {
            background-color: #fef3c7;
            color: #92400e;
        }
        .customer-info {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 10px 0;
        }
        .customer-name {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 5px 0;
        }
        .customer-details {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        td {
            padding: 12px 15px;
            font-size: 14px;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .item-name {
            font-weight: 500;
            color: #1f2937;
        }
        .item-description {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        .summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .summary-card {
            width: 350px;
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-row.total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 16px;
            color: #1e40af;
        }
        .notes {
            margin-bottom: 30px;
        }
        .notes-content {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            font-size: 14px;
            color: #4b5563;
            line-height: 1.5;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .payment-info {
            margin-bottom: 30px;
        }
        .payment-summary {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        .payment-card {
            flex: 1;
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .payment-value {
            font-size: 18px;
            font-weight: 600;
            margin: 5px 0;
        }
        .payment-label {
            font-size: 12px;
            color: #6b7280;
        }
        .qr-section {
            text-align: center;
            margin-top: 30px;
        }
        .qr-code {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
        }
        .print-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .print-button {
            background-color: #1e40af;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .print-button:hover {
            background-color: #1e3a8a;
        }
        .back-button {
            background-color: #6b7280;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .back-button:hover {
            background-color: #4b5563;
        }
        @media print {
            body {
                background-color: white;
            }
            .invoice-container {
                max-width: 100%;
                box-shadow: none;
                padding: 20px;
            }
            .print-controls {
                display: none;
            }
            .print-only {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="print-controls">
            <button class="print-button" onclick="window.print()">Cetak Invoice</button>
            <button class="back-button" onclick="window.history.back()">Kembali</button>
        </div>
        
        <div class="header">
            <div class="company-info">
                <h1 class="company-name">POS SCM System</h1>
                {{-- <div class="company-details">
                    <div>Jl. Contoh No. 123, Jakarta Selatan</div>
                    <div>Telp: (021) 123-4567</div>
                    <div>Email: info@posscm.com</div>
                </div> --}}
            </div>
            <div class="invoice-details">
                <div class="invoice-id">Invoice #{{ $penjualan->nomor_penjualan }}</div>
                <div class="invoice-date">Tanggal: {{ $penjualan->formatted_tanggal }}</div>
                <div class="status-badge {{ $sisaPembayaran <= 0 ? 'status-lunas' : ($totalPembayaran > 0 ? 'status-partial' : 'status-belum-lunas') }}">
                    {{ $sisaPembayaran <= 0 ? 'LUNAS' : ($totalPembayaran > 0 ? 'SEBAGIAN' : 'BELUM LUNAS') }}
                </div>
            </div>
        </div>
        
        <div class="customer-info">
            <h2 class="section-title">Informasi Customer</h2>
            <div class="customer-name">{{ $penjualan->customer->nama_customer }}</div>
            <div class="customer-details">
                @if($penjualan->customer->alamat)
                <div>{{ $penjualan->customer->alamat }}</div>
                @endif
                @if($penjualan->customer->telepon)
                <div>Telp: {{ $penjualan->customer->telepon }}</div>
                @endif
                @if($penjualan->customer->email)
                <div>Email: {{ $penjualan->customer->email }}</div>
                @endif
            </div>
        </div>
        
        <h2 class="section-title">Detail Barang</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Gudang</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualanDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="item-name">{{ $detail->barang->nama_barang }}</div>
                        @if($detail->barang->keterangan)
                        <div class="item-description">{{ $detail->barang->keterangan }}</div>
                        @endif
                    </td>
                    <td>{{ $detail->satuan->nama_satuan }}</td>
                    <td>{{ $detail->gudang->nama_gudang }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->jumlah, 2) }}</td>
                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="summary">
            <div class="summary-card">
                <div class="summary-row">
                    <div>Subtotal</div>
                    <div>Rp {{ number_format($penjualanDetails->sum('subtotal'), 0, ',', '.') }}</div>
                </div>
                @if($penjualan->diskon > 0)
                <div class="summary-row">
                    <div>Diskon</div>
                    <div>-Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</div>
                </div>
                @endif
                @if($penjualan->biaya_lain > 0)
                <div class="summary-row">
                    <div>Biaya Lain</div>
                    <div>Rp {{ number_format($penjualan->biaya_lain, 0, ',', '.') }}</div>
                </div>
                @endif
                <div class="summary-row total">
                    <div>Total</div>
                    <div>Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        
        <div class="payment-info">
            <h2 class="section-title">Informasi Pembayaran</h2>
            <div class="payment-summary">
                <div class="payment-card">
                    <div class="payment-value">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</div>
                    <div class="payment-label">Total Dibayar</div>
                </div>
                <div class="payment-card">
                    <div class="payment-value">Rp {{ number_format(abs($sisaPembayaran), 0, ',', '.') }}</div>
                    <div class="payment-label">{{ $sisaPembayaran > 0 ? 'Sisa Tagihan' : ($sisaPembayaran < 0 ? 'Kelebihan Bayar' : 'Lunas') }}</div>
                </div>
                <div class="payment-card">
                    <div class="payment-value">Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</div>
                    <div class="payment-label">Total Tagihan</div>
                </div>
            </div>
        </div>
        
        @if($penjualan->keterangan)
        <div class="notes">
            <h2 class="section-title">Catatan</h2>
            <div class="notes-content">
                {{ $penjualan->keterangan }}
            </div>
        </div>
        @endif
        
        {{-- <div class="qr-section">
            <div class="qr-code"></div>
            <div style="margin-top: 5px; font-size: 12px; color: #6b7280;">Scan untuk verifikasi</div>
        </div> --}}
        
        <div class="footer">
            <div>Terima kasih atas kepercayaan Anda berbelanja dengan kami!</div>
            <div style="margin-top: 5px;">Invoice ini dihasilkan secara otomatis dan sah tanpa tanda tangan.</div>
            <div class="print-only" style="margin-top: 10px;">Dicetak pada: {{ now()->format('d M Y H:i:s') }}</div>
        </div>
    </div>
    
    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
