@props(['data', 'filters', 'statistik'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembayaran {{ ucfirst($filters['jenis_laporan']) }}</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
        }

        /* Print Specific Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 1.5cm 1cm;
            }
            
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .no-break {
                page-break-inside: avoid;
            }
        }

        /* Header Styles */
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #000;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-address {
            font-size: 11px;
            margin-bottom: 15px;
            color: #333;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-period {
            font-size: 12px;
            font-weight: normal;
            margin-bottom: 10px;
        }

        /* Summary Section */
        .summary-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            border: 2px solid #000;
            padding: 15px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .summary-item {
            text-align: center;
            border: 1px solid #ccc;
            padding: 8px 5px;
            background: white;
        }

        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 3px;
            color: #666;
        }

        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #000;
        }

        .payment-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .payment-item {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            background: white;
        }

        .payment-type {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .payment-count {
            font-size: 11px;
            margin-bottom: 2px;
        }

        .payment-amount {
            font-size: 11px;
            font-weight: bold;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background-color: #e9ecef;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: center;
        }

        .data-table td {
            font-size: 10px;
        }

        .number-cell {
            text-align: center;
            width: 40px;
        }

        .date-cell {
            width: 100px;
            text-align: center;
        }

        .customer-cell {
            width: 120px;
        }

        .transaction-cell {
            width: 100px;
            text-align: center;
        }

        .payment-type-cell {
            width: 80px;
            text-align: center;
        }

        .amount-cell {
            width: 100px;
            text-align: right;
        }

        .user-cell {
            width: 80px;
            text-align: center;
        }

        .notes-cell {
            width: 100px;
        }

        /* Payment Type Badges */
        .payment-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .payment-cash { background: #d4edda; color: #155724; }
        .payment-transfer { background: #cce7ff; color: #004085; }
        .payment-kredit { background: #fff3cd; color: #856404; }
        .payment-check { background: #e2e3e5; color: #383d41; }
        .payment-other { background: #f8d7da; color: #721c24; }

        /* Footer */
        .report-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #000;
            display: flex;
            justify-content: space-between;
            align-items: end;
            font-size: 10px;
        }

        .signature-section {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 50px;
            margin-bottom: 5px;
        }

        .print-info {
            text-align: right;
            font-size: 9px;
            color: #666;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            border: 2px dashed #ccc;
            background: #f8f9fa;
            margin: 20px 0;
        }

        .empty-icon {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }

        .empty-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .empty-subtext {
            font-size: 11px;
            color: #999;
        }
    </style>
</head>
<body>
    <!-- Report Header -->
    <div class="report-header no-break">
        <div class="company-name">{{ config('app.name', 'SIWUR') }}</div>
        <div class="company-address">
            Sistem Informasi Warung<br>
            Jl. Contoh No. 123, Kota, Provinsi 12345<br>
            Telp: (021) 1234-5678 | Email: info@siwur.com
        </div>
        
        <div class="report-title">
            Laporan Pembayaran {{ ucfirst($filters['jenis_laporan']) }}
        </div>
        
        <div class="report-period">
            Periode: {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d F Y') }} 
            s.d {{ \Carbon\Carbon::parse($filters['tanggal_selesai'])->format('d F Y') }}
            @if(!empty($filters['jenis_pembayaran']))
                | Jenis Pembayaran: {{ ucfirst($filters['jenis_pembayaran']) }}
            @endif
            @if(!empty($filters['search']))
                | Pencarian: "{{ $filters['search'] }}"
            @endif
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section no-break">
        <div class="summary-title">Ringkasan Laporan</div>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Transaksi</div>
                <div class="summary-value">{{ number_format($statistik['total_transaksi'], 0, ',', '.') }}</div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">
                    {{ $filters['jenis_laporan'] === 'penjualan' ? 'Total Pembayaran Bersih' : 'Total Pembayaran' }}
                </div>
                <div class="summary-value">Rp {{ number_format($statistik['total_pembayaran'], 0, ',', '.') }}</div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">Periode Laporan</div>
                <div class="summary-value">{{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->diffInDays(\Carbon\Carbon::parse($filters['tanggal_selesai'])) + 1 }} Hari</div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">
                    @if($filters['jenis_laporan'] === 'penjualan')
                        Total Kembalian
                    @else
                        Rata-rata per Transaksi
                    @endif
                </div>
                <div class="summary-value">
                    @if($filters['jenis_laporan'] === 'penjualan')
                        Rp {{ number_format($statistik['total_kembalian'], 0, ',', '.') }}
                    @else
                        Rp {{ number_format($statistik['total_transaksi'] > 0 ? $statistik['total_pembayaran'] / $statistik['total_transaksi'] : 0, 0, ',', '.') }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="payment-breakdown">
            <div class="payment-item">
                <div class="payment-type">Cash</div>
                <div class="payment-count">{{ $statistik['total_cash'] }} transaksi</div>
                <div class="payment-amount">Rp {{ number_format($statistik['nilai_cash'], 0, ',', '.') }}</div>
            </div>
            
            <div class="payment-item">
                <div class="payment-type">Transfer</div>
                <div class="payment-count">{{ $statistik['total_transfer'] }} transaksi</div>
                <div class="payment-amount">Rp {{ number_format($statistik['nilai_transfer'], 0, ',', '.') }}</div>
            </div>
            
            @if($filters['jenis_laporan'] === 'penjualan')
                <div class="payment-item">
                    <div class="payment-type">Kredit</div>
                    <div class="payment-count">{{ $statistik['total_kredit'] }} transaksi</div>
                    <div class="payment-amount">Rp {{ number_format($statistik['nilai_kredit'], 0, ',', '.') }}</div>
                </div>
            @else
                <div class="payment-item">
                    <div class="payment-type">Check</div>
                    <div class="payment-count">{{ $statistik['total_check'] }} transaksi</div>
                    <div class="payment-amount">Rp {{ number_format($statistik['nilai_check'], 0, ',', '.') }}</div>
                </div>
                
                <div class="payment-item">
                    <div class="payment-type">Other</div>
                    <div class="payment-count">{{ $statistik['total_other'] }} transaksi</div>
                    <div class="payment-amount">Rp {{ number_format($statistik['nilai_other'], 0, ',', '.') }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Data Table -->
    @if($data->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th class="number-cell">No</th>
                    <th class="date-cell">Tanggal</th>
                    <th class="customer-cell">{{ $filters['jenis_laporan'] === 'penjualan' ? 'Customer' : 'Supplier' }}</th>
                    <th class="transaction-cell">Kode Transaksi</th>
                    <th class="payment-type-cell">Jenis Bayar</th>
                    <th class="amount-cell">
                        {{ $filters['jenis_laporan'] === 'penjualan' ? 'Jumlah Bersih' : 'Jumlah' }}
                    </th>
                    <th class="user-cell">User</th>
                    <th class="notes-cell">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    <tr>
                        <td class="number-cell">{{ $loop->iteration }}</td>
                        <td class="date-cell">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="customer-cell">
                            @if($filters['jenis_laporan'] === 'penjualan')
                                {{ $item->penjualan->customer->nama_customer ?? '-' }}
                            @else
                                {{ $item->pembelian->supplier->nama_supplier ?? '-' }}
                            @endif
                        </td>
                        <td class="transaction-cell">
                            @if($filters['jenis_laporan'] === 'penjualan')
                                {{ $item->penjualan->nomor_penjualan ?? '-' }}
                            @else
                                {{ $item->pembelian->nomor_pembelian ?? '-' }}
                            @endif
                        </td>
                        <td class="payment-type-cell">
                            <span class="payment-badge payment-{{ $item->jenis_pembayaran }}">
                                {{ ucfirst($item->jenis_pembayaran) }}
                            </span>
                        </td>
                        <td class="amount-cell">
                            @if($filters['jenis_laporan'] === 'penjualan')
                                <div class="font-bold">Rp {{ number_format($item->jumlah - ($item->kembalian ?? 0), 0, ',', '.') }}</div>
                                @if($item->kembalian > 0)
                                    <div style="font-size: 8px; color: #666;">
                                        Bayar: Rp {{ number_format($item->jumlah, 0, ',', '.') }}<br>
                                        Kembali: Rp {{ number_format($item->kembalian, 0, ',', '.') }}
                                    </div>
                                @endif
                            @else
                                <div class="font-bold">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="user-cell">{{ $item->user->name ?? '-' }}</td>
                        <td class="notes-cell">{{ $item->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
                
                <!-- Summary Row -->
                <tr style="background: #f0f0f0; font-weight: bold; border-top: 2px solid #000;">
                    <td colspan="5" class="text-center text-uppercase">Total</td>
                    <td class="amount-cell font-bold">
                        Rp {{ number_format($statistik['total_pembayaran'], 0, ',', '.') }}
                    </td>
                    <td colspan="2" class="text-center">{{ $statistik['total_transaksi'] }} transaksi</td>
                </tr>
            </tbody>
        </table>
    @else
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">📄</div>
            <div class="empty-text">Tidak ada data pembayaran ditemukan</div>
            <div class="empty-subtext">
                Untuk periode {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d F Y') }} 
                s.d {{ \Carbon\Carbon::parse($filters['tanggal_selesai'])->format('d F Y') }}
                @if(!empty($filters['jenis_pembayaran']))
                    dengan jenis pembayaran {{ ucfirst($filters['jenis_pembayaran']) }}
                @endif
            </div>
        </div>
    @endif

    <!-- Report Footer -->
    <div class="report-footer">
        <div class="print-info">
            <div><strong>Dicetak pada:</strong> {{ now()->format('d F Y, H:i:s') }}</div>
            <div><strong>Dicetak oleh:</strong> {{ Auth::user()->name ?? 'System' }}</div>
            <div><strong>Sistem:</strong> {{ config('app.name', 'SIWUR') }} v1.0</div>
        </div>
        
        <div class="signature-section">
            <div>Mengetahui,</div>
            <div class="signature-line"></div>
            <div><strong>Pimpinan</strong></div>
            <div style="font-size: 9px; margin-top: 5px;">{{ now()->format('d F Y') }}</div>
        </div>
    </div>
</body>
</html>