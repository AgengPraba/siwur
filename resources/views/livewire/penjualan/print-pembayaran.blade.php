<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran #{{ $pembayaran->id }}</title>
    <style>
        @page {
            margin: 0;
        }

        * {
            font-family: 'Courier New', Courier, monospace;
            box-sizing: border-box;
        }

        .text-normal {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        .text-mono {
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }

        .receipt-container {
            max-width: 350px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .items-section {
            margin-bottom: 15px;
        }

        .items-header {
            font-size: 12px;
            font-weight: 600;
            color: #000;
            margin-bottom: 8px;
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .item-row {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dotted #ccc;
        }

        .item-name {
            font-size: 10px;
            font-weight: 600;
            color: #000;
            margin-bottom: 2px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #333;
            margin-bottom: 1px;
            font-family: 'Courier New', Courier, monospace;
        }

        .item-price {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: 500;
            color: #000;
            font-family: 'Courier New', Courier, monospace;
        }

        .summary-section {
            background-color: #f9fafb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 3px;
            font-family: 'Courier New', Courier, monospace;
        }

        .summary-total {
            font-size: 11px;
            font-weight: 700;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 3px;
            font-family: 'Courier New', Courier, monospace;
        }

        .print-only {
            display: none;
        }

        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 16px;
            font-weight: 700;
            color: #000;
            margin: 0 0 5px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .receipt-title {
            font-size: 14px;
            font-weight: 600;
            color: #000;
            margin: 10px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .company-details {
            color: #000;
            font-size: 10px;
            line-height: 1.3;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .receipt-info {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .info-label {
            font-weight: 500;
            color: #000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .info-value {
            color: #000;
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }

        .payment-details {
            margin-bottom: 15px;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 4px;
            font-family: 'Courier New', Courier, monospace;
        }

        .payment-label {
            font-weight: 500;
            color: #000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .payment-value {
            color: #000;
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }

        .payment-total {
            font-size: 11px;
            font-weight: 700;
            color: #000;
            margin-top: 3px;
            font-family: 'Courier New', Courier, monospace;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #000;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .thank-you {
            font-weight: 600;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .barcode {
            text-align: center;
            margin: 20px 0;
        }

        .barcode-img {
            width: 90%;
            height: 50px;
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            margin: 0 auto;
        }

        .qr-code {
            width: 80px;
            height: 80px;
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
            @page {
                margin: 5mm;
                size: 80mm auto;
            }

            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }

            .receipt-container {
                max-width: 100%;
                box-shadow: none;
                padding: 5px;
                margin: 0;
            }

            .print-controls {
                display: none;
            }

            .print-only {
                display: block;
            }

            .header {
                margin-bottom: 10px;
                padding-bottom: 8px;
            }

            .items-section,
            .summary-section,
            .payment-details,
            .receipt-info {
                margin-bottom: 10px;
            }

            .footer {
                margin-top: 15px;
                padding-top: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="print-controls">
            <button class="print-button" onclick="window.print()">Cetak Struk</button>
            <button class="back-button" onclick="window.history.back()">Kembali</button>
        </div>

        <div class="header">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Company Logo"
                style="width: 80px; height: auto; margin-bottom: 1px;">
            <h1 class="company-name">{{ config('app.name') }}</h1>
            {{-- <div class="company-details">
                <div>Jl. Contoh No. 123, Jakarta Selatan</div>
                <div>Telp: (021) 123-4567</div>
            </div> --}}
            <h2 class="receipt-title">STRUK PEMBAYARAN</h2>
        </div>

        <div class="receipt-info">

            <div class="info-row">
                <div class="info-label">Tanggal:</div>
                <div class="info-value">{{ $pembayaran->created_at->format('d M Y, H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">No. Invoice:</div>
                <div class="info-value">{{ $pembayaran->penjualan->nomor_penjualan }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Customer:</div>
                <div class="info-value">{{ $pembayaran->penjualan->customer->nama_customer }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Metode:</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $pembayaran->jenis_pembayaran)) }}</div>
            </div>
        </div>

        <!-- Detail Barang -->
        <div class="items-section">
            <div class="items-header">No Penjualan {{ $pembayaran->penjualan->nomor_penjualan }}</div>
            @foreach ($pembayaran->penjualan->penjualanDetails as $detail)
                <div class="item-row">
                    <div class="item-name">{{ $detail->barang->nama_barang }}</div>
                    <div class="item-details">
                        <span>{{ number_format($detail->jumlah, 0) }}
                            {{ $detail->satuan->nama_satuan ?? 'pcs' }}</span>
                        <span>@ Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</span>
                    </div>
                    <div class="item-price">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if ($detail->diskon > 0)
                        <div class="item-details">
                            <span>Diskon:</span>
                            <span style="color: #dc2626;">-Rp {{ number_format($detail->diskon, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($detail->biaya_lain > 0)
                        <div class="item-details">
                            <span>Biaya Lain:</span>
                            <span style="color: #059669;">+Rp
                                {{ number_format($detail->biaya_lain, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Ringkasan Penjualan -->
        <div class="summary-section">
            <div class="summary-row">
                <span>Total Item:</span>
                <span>{{ $pembayaran->penjualan->total_items }} item</span>
            </div>
            <div class="summary-row">
                <span>Total Qty:</span>
                <span>{{ number_format($pembayaran->penjualan->total_quantity, 0) }}</span>
            </div>
            @if ($pembayaran->penjualan->total_diskon > 0)
                <div class="summary-row">
                    <span>Total Diskon:</span>
                    <span style="color: #dc2626;">-Rp
                        {{ number_format($pembayaran->penjualan->total_diskon, 0, ',', '.') }}</span>
                </div>
            @endif
            @if ($pembayaran->penjualan->total_biaya_lain > 0)
                <div class="summary-row">
                    <span>Total Biaya Lain:</span>
                    <span style="color: #059669;">+Rp
                        {{ number_format($pembayaran->penjualan->total_biaya_lain, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="summary-row summary-total">
                <span>TOTAL PENJUALAN:</span>
                <span>Rp {{ number_format($pembayaran->penjualan->total_harga, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="payment-details">
            <div class="payment-row">
                <div class="payment-label">Total Invoice:</div>
                <div class="payment-value">Rp {{ number_format($pembayaran->penjualan->total_harga, 0, ',', '.') }}
                </div>
            </div>
            <div class="payment-row">
                <div class="payment-label">Jumlah Pembayaran:</div>
                <div class="payment-value payment-total">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</div>
            </div>

            @php
                $totalPembayaran = $pembayaran->penjualan->pembayaranPenjualan->sum('jumlah');
                $sisaPembayaran = $pembayaran->penjualan->total_harga - $totalPembayaran;
                $kembalian = $pembayaran->jumlah - $pembayaran->penjualan->total_harga;
            @endphp

            @if ($kembalian > 0)
                <div class="payment-row">
                    <div class="payment-label">Kembalian:</div>
                    <div class="payment-value" style="color: #059669; font-weight: 600;">Rp
                        {{ number_format($kembalian, 0, ',', '.') }}</div>
                </div>
            @endif

            <div class="payment-row">
                <div class="payment-label">Sisa Tagihan:</div>
                <div class="payment-value">Rp {{ number_format(max(0, $sisaPembayaran), 0, ',', '.') }}</div>
            </div>

            <div class="payment-row">
                <div class="payment-label">Status:</div>
                <div class="payment-value">
                    {{ $sisaPembayaran <= 0 ? 'LUNAS' : 'BELUM LUNAS' }}
                </div>
            </div>
        </div>

        @if ($pembayaran->keterangan)
            <div class="divider"></div>
            <div
                style="font-size: 10px; margin-bottom: 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                <div style="font-weight: 500; margin-bottom: 3px; color: #000;">Keterangan:</div>
                <div style="color: #000; font-family: 'Courier New', Courier, monospace;">{{ $pembayaran->keterangan }}
                </div>
            </div>
        @endif



        <div class="footer">
            <div class="thank-you">TERIMA KASIH</div>
            <div>Struk ini sah tanpa tanda tangan</div>
            <div style="margin-top: 5px; font-size: 9px;">{{ now()->format('d/m/Y H:i:s') }}</div>
            <div class="print-only" style="margin-top: 5px; font-size: 9px;">Dicetak:
                {{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <script>
        const redirectUrl = @json($redirectUrl);

        // Auto print when page loads after 500ms
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };

        // Handle after print event
        window.addEventListener('afterprint', function() {
            if (redirectUrl) {
                window.location.href = redirectUrl;
                return;
            }

            // Optional: Auto go back after printing
            setTimeout(function() {
                window.history.back();
            }, 1000);
        });
    </script>
</body>

</html>
