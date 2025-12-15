<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Profit Penjualan - {{ config('app.name', 'SIWUR') }}</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        /* Print Styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
        
        /* Print Button */
        .print-button-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #4F81BD, #3366a3);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            background: linear-gradient(135deg, #3366a3, #2a5490);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-close {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .btn-close:hover {
            background: linear-gradient(135deg, #c82333, #a71d2a);
            transform: translateY(-2px);
        }
        
        /* Container */
        .container {
            max-width: 100%;
            padding: 20px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .period {
            font-size: 12px;
            color: #666;
        }
        
        /* Info Section */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        
        .info-left, .info-right {
            font-size: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #4a5568;
        }
        
        /* Summary Cards */
        .summary-section {
            margin-bottom: 25px;
        }
        
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 2px solid #4F81BD;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
        }
        
        .summary-card {
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        
        .summary-card.highlight-green {
            background: linear-gradient(135deg, #c6f6d5, #9ae6b4);
            border-color: #68d391;
        }
        
        .summary-card.highlight-purple {
            background: linear-gradient(135deg, #e9d8fd, #d6bcfa);
            border-color: #b794f4;
        }
        
        .summary-card.highlight-orange {
            background: linear-gradient(135deg, #feebc8, #fbd38d);
            border-color: #f6ad55;
        }
        
        .summary-card.highlight-red {
            background: linear-gradient(135deg, #fed7d7, #feb2b2);
            border-color: #fc8181;
        }
        
        .summary-card-label {
            font-size: 9px;
            color: #4a5568;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .summary-card-value {
            font-size: 13px;
            font-weight: bold;
            color: #1a365d;
        }
        
        /* Financial Summary Table */
        .financial-summary {
            margin-bottom: 25px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .financial-summary-header {
            background: linear-gradient(135deg, #4F81BD, #3366a3);
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .financial-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .financial-table td {
            padding: 8px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .financial-table tr:last-child td {
            border-bottom: none;
        }
        
        .financial-table .label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .financial-table .label.indent {
            padding-left: 30px;
        }
        
        .financial-table .value {
            text-align: right;
            font-weight: 600;
        }
        
        .financial-table .positive {
            color: #2f855a;
        }
        
        .financial-table .negative {
            color: #c53030;
        }
        
        .financial-table .total-row {
            background: #f7fafc;
            font-weight: bold;
        }
        
        .financial-table .total-row td {
            border-top: 2px solid #4F81BD;
        }
        
        /* Data Table */
        .table-section {
            margin-bottom: 25px;
        }
        
        .table-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #4F81BD;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        
        table.data-table thead th {
            background: linear-gradient(135deg, #4F81BD, #3366a3);
            color: white;
            padding: 8px 6px;
            text-align: center;
            font-weight: 600;
            font-size: 9px;
            border: 1px solid #3366a3;
        }
        
        table.data-table tbody td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        table.data-table tbody tr:hover {
            background: #edf2f7;
        }
        
        table.data-table .text-center {
            text-align: center;
        }
        
        table.data-table .text-right {
            text-align: right;
        }
        
        table.data-table .text-left {
            text-align: left;
        }
        
        table.data-table .positive {
            color: #2f855a;
            font-weight: 600;
        }
        
        table.data-table .negative {
            color: #c53030;
            font-weight: 600;
        }
        
        table.data-table tfoot td {
            background: #2d3748;
            color: white;
            padding: 8px 6px;
            font-weight: bold;
            border: 1px solid #1a202c;
        }
        
        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .badge-warning {
            background: #fefcbf;
            color: #744210;
        }
        
        .badge-error {
            background: #fed7d7;
            color: #822727;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-notes {
            font-size: 9px;
            color: #718096;
            margin-bottom: 20px;
        }
        
        .footer-notes ul {
            list-style: none;
            padding-left: 0;
        }
        
        .footer-notes li {
            margin-bottom: 3px;
        }
        
        .footer-notes li:before {
            content: "•";
            margin-right: 8px;
            color: #4F81BD;
        }
        
        .signature-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .signature-box {
            text-align: center;
            min-width: 180px;
        }
        
        .signature-box .sign-title {
            font-size: 10px;
            color: #4a5568;
            margin-bottom: 50px;
        }
        
        .signature-box .sign-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .signature-box .sign-position {
            font-size: 9px;
            color: #718096;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #718096;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Print Buttons -->
    <div class="print-button-container no-print">
        <button class="btn-print" onclick="window.print()">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Cetak Laporan
        </button>
        <button class="btn-close" onclick="window.close()">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Tutup
        </button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ config('app.name', 'SIWUR') }}</div>
            <div class="report-title">LAPORAN LABA RUGI / PROFIT PENJUALAN</div>
            <div class="period">Periode: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}</div>
        </div>
        
        <!-- Info Section -->
        <div class="info-section">
            <div class="info-left">
                <div><span class="info-label">Dicetak pada:</span> {{ now()->format('d M Y H:i:s') }}</div>
                <div><span class="info-label">Dicetak oleh:</span> {{ Auth::user()->name ?? 'System' }}</div>
            </div>
            <div class="info-right">
                @if(!empty($filters['customer_name']))
                    <div><span class="info-label">Customer:</span> {{ $filters['customer_name'] }}</div>
                @endif
                @if(!empty($filters['status']))
                    <div><span class="info-label">Status:</span> {{ ucfirst(str_replace('_', ' ', $filters['status'])) }}</div>
                @endif
                @if(!empty($filters['search']))
                    <div><span class="info-label">Pencarian:</span> {{ $filters['search'] }}</div>
                @endif
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-section">
            <div class="summary-title">📊 Ringkasan Kinerja</div>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-card-label">Total Transaksi</div>
                    <div class="summary-card-value">{{ number_format($summary['total_transaksi']) }}</div>
                </div>
                <div class="summary-card highlight-green">
                    <div class="summary-card-label">Total Penjualan</div>
                    <div class="summary-card-value">Rp {{ number_format($summary['total_penjualan'], 0, ',', '.') }}</div>
                </div>
                <div class="summary-card highlight-purple">
                    <div class="summary-card-label">Total Profit</div>
                    <div class="summary-card-value">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</div>
                </div>
                <div class="summary-card highlight-orange">
                    <div class="summary-card-label">Profit Margin</div>
                    <div class="summary-card-value">{{ number_format($summary['profit_margin'], 1) }}%</div>
                </div>
                <div class="summary-card highlight-red">
                    <div class="summary-card-label">Total Diskon</div>
                    <div class="summary-card-value">Rp {{ number_format($summary['total_diskon'], 0, ',', '.') }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card-label">Biaya Lain</div>
                    <div class="summary-card-value">Rp {{ number_format($summary['total_biaya_lain'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        
        <!-- Financial Summary -->
        <div class="financial-summary">
            <div class="financial-summary-header">📋 Ikhtisar Laba Rugi</div>
            <table class="financial-table">
                <tr>
                    <td class="label" style="width: 50%;">PENDAPATAN</td>
                    <td class="value" style="width: 50%;"></td>
                </tr>
                <tr>
                    <td class="label indent">Penjualan Kotor (Subtotal)</td>
                    <td class="value">Rp {{ number_format($summary['subtotal_sebelum_adjustment'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label indent">Dikurangi: Diskon Penjualan</td>
                    <td class="value negative">(Rp {{ number_format($summary['total_diskon'], 0, ',', '.') }})</td>
                </tr>
                <tr>
                    <td class="label indent">Ditambah: Biaya Lain-lain</td>
                    <td class="value">Rp {{ number_format($summary['total_biaya_lain'], 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td class="label">Penjualan Bersih</td>
                    <td class="value">Rp {{ number_format($summary['total_penjualan'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label" style="padding-top: 15px;">LABA / RUGI</td>
                    <td class="value"></td>
                </tr>
                <tr>
                    <td class="label indent">Laba Kotor (Gross Profit)</td>
                    <td class="value {{ $summary['total_profit'] >= 0 ? 'positive' : 'negative' }}">
                        Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="total-row">
                    <td class="label">Margin Laba</td>
                    <td class="value {{ $summary['profit_margin'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($summary['profit_margin'], 2) }}%
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Detail Table -->
        <div class="table-section">
            <div class="table-title">📋 Detail Transaksi Penjualan</div>
            
            @if(count($penjualans) > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 3%;">No</th>
                            <th style="width: 10%;">Tanggal</th>
                            <th style="width: 10%;">No. Penjualan</th>
                            <th style="width: 12%;">Customer</th>
                            <th style="width: 5%;">Items</th>
                            <th style="width: 5%;">Qty</th>
                            <th style="width: 10%;">Subtotal</th>
                            <th style="width: 8%;">Diskon</th>
                            <th style="width: 8%;">Biaya Lain</th>
                            <th style="width: 10%;">Total</th>
                            <th style="width: 10%;">Profit</th>
                            <th style="width: 5%;">Margin</th>
                            <th style="width: 6%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualans as $index => $penjualan)
                            @php
                                $totalProfit = $penjualan->penjualanDetails->sum('profit');
                                $totalDiskon = $penjualan->penjualanDetails->sum('diskon');
                                $totalBiayaLain = $penjualan->penjualanDetails->sum('biaya_lain');
                                $totalItems = $penjualan->penjualanDetails->count();
                                $totalQuantity = $penjualan->penjualanDetails->sum('jumlah');
                                $subtotal = $penjualan->penjualanDetails->sum('subtotal');
                                $profitMargin = $penjualan->total_harga > 0 ? ($totalProfit / $penjualan->total_harga) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $penjualan->tanggal_penjualan->format('d/m/Y') }}</td>
                                <td class="text-left">{{ $penjualan->nomor_penjualan }}</td>
                                <td class="text-left">{{ $penjualan->customer->nama_customer ?? '-' }}</td>
                                <td class="text-center">{{ $totalItems }}</td>
                                <td class="text-center">{{ number_format($totalQuantity, 0) }}</td>
                                <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                <td class="text-right {{ $totalDiskon > 0 ? 'negative' : '' }}">
                                    {{ $totalDiskon > 0 ? 'Rp ' . number_format($totalDiskon, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-right">
                                    {{ $totalBiayaLain > 0 ? 'Rp ' . number_format($totalBiayaLain, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-right">Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</td>
                                <td class="text-right {{ $totalProfit >= 0 ? 'positive' : 'negative' }}">
                                    Rp {{ number_format($totalProfit, 0, ',', '.') }}
                                </td>
                                <td class="text-center {{ $profitMargin >= 0 ? 'positive' : 'negative' }}">
                                    {{ number_format($profitMargin, 1) }}%
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $penjualan->status === 'lunas' ? 'badge-success' : ($penjualan->status === 'belum_lunas' ? 'badge-warning' : 'badge-error') }}">
                                        {{ $penjualan->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-center">TOTAL</td>
                            <td class="text-center">{{ $summary['total_transaksi'] }}</td>
                            <td class="text-center">-</td>
                            <td class="text-right">Rp {{ number_format($summary['subtotal_sebelum_adjustment'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($summary['total_diskon'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($summary['total_biaya_lain'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($summary['total_penjualan'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($summary['profit_margin'], 1) }}%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div>Tidak ada data penjualan untuk periode ini</div>
                </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-notes">
                <strong>Catatan:</strong>
                <ul>
                    <li>Laporan ini dihasilkan secara otomatis oleh sistem {{ config('app.name', 'SIWUR') }}</li>
                    <li>Profit dihitung berdasarkan selisih harga jual dengan harga beli per item</li>
                    <li>Margin laba = (Total Profit / Total Penjualan) × 100%</li>
                    <li>Diskon mengurangi total penjualan, Biaya Lain menambah total penjualan</li>
                </ul>
            </div>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="sign-title">Mengetahui,</div>
                    <div class="sign-line">____________________</div>
                    <div class="sign-position">Pimpinan / Akuntan</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto focus for print when page loads
        window.onload = function() {
            // Optional: Auto print after page loads
            // Uncomment line below to auto-trigger print dialog
            // window.print();
        }
    </script>
</body>
</html>
