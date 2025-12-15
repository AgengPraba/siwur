<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran Pembelian #{{ $pembelian->nomor_pembelian }}</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #fff;
            font-size: 14px;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #10b981;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            margin: 0;
        }
        .company-details {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .document-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 15px 0;
            color: #10b981;
            text-transform: uppercase;
        }
        .purchase-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 5px;
        }
        .purchase-info-block {
            flex: 1;
            padding-right: 15px;
        }
        .info-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #10b981;
        }
        .info-value {
            margin: 0 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #10b981;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tr:hover {
            background-color: #f1f5f9;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #e5e7eb;
            padding-top: 10px;
            margin-top: 10px;
            color: #10b981;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 50px;
            font-weight: bold;
        }
        .payment-badge {
            display: inline-block;
            padding: 5px 10px;
            background-color: #a5f3fc;
            color: #0e7490;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .print-date {
            text-align: right;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 20px;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <h1 class="company-logo">POS & SCM SYSTEM</h1>
            <p class="company-details">
                Jl. Raya Utama No. 123, Jakarta Selatan<br>
                Telp: (021) 123-4567 | Email: info@posscm.com
            </p>
        </div>
        
        <div class="print-date">
            Dicetak pada: {{ $printDate }}
        </div>

        <h2 class="document-title">Bukti Pembayaran Pembelian</h2>
        
        <div class="purchase-info">
            <div class="purchase-info-block">
                <p class="info-title">Nomor Pembelian</p>
                <p class="info-value">{{ $pembelian->nomor_pembelian }}</p>
                
                <p class="info-title">Tanggal Pembelian</p>
                <p class="info-value">{{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d/m/Y') }}</p>
                
                <p class="info-title">Status</p>
                <p class="info-value">{{ strtoupper($pembelian->status) }}</p>
            </div>
            
            <div class="purchase-info-block">
                <p class="info-title">Supplier</p>
                <p class="info-value">{{ $pembelian->supplier->nama_supplier }}</p>
                
                <p class="info-title">Total Harga</p>
                <p class="info-value">Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}</p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jenis Pembayaran</th>
                    <th>Jumlah</th>
                    <th>User</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @if($payments->count() > 0)
                    @foreach($payments as $index => $payment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}</td>
                        <td><span class="payment-badge">{{ strtoupper($payment->jenis_pembayaran) }}</span></td>
                        <td>Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</td>
                        <td>{{ $payment->user->name }}</td>
                        <td>{{ $payment->keterangan ?? '-' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" style="text-align: center;">Belum ada pembayaran</td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div class="summary">
            <div class="summary-row">
                <span>Total Harga</span>
                <span>Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Terbayar</span>
                <span>Rp {{ number_format($total_paid, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row total" style="color: {{ $remaining > 0 ? '#ef4444' : '#10b981' }};">
                <span>Sisa</span>
                <span>Rp {{ number_format($remaining, 0, ',', '.') }}</span>
            </div>
        </div>
        
        <div class="signature-area">
            <div class="signature-box">
                <div class="signature-line">Penerima</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Pembayar</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Mengetahui</div>
            </div>
        </div>
        
        <div class="footer">
            <p>Terima kasih atas kerjasamanya. Dokumen ini dicetak oleh sistem dan sah tanpa tanda tangan.</p>
            <p>© {{ date('Y') }} POS & SCM System</p>
        </div>
        
        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Cetak Dokumen
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; background-color: #64748b; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                Tutup
            </button>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            // Small delay to ensure everything is rendered
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
