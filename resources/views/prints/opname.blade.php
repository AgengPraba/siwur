<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Print Stock Opname - {{ $opname->nomor_opname }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .info {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            width: 150px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            font-size: 12px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .summary {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }


        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN STOCK OPNAME</h2>
        <h3>{{ $opname->toko->nama_toko ?? 'SIWUR POS' }}</h3>
    </div>

    <div class="info">
        <div class="info-row">
            <div class="info-label">Nomor Opname:</div>
            <div>{{ $opname->nomor_opname }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal:</div>
            <div>{{ date('d M Y H:i', strtotime($opname->tanggal_opname)) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Gudang:</div>
            <div>{{ $opname->gudang->nama_gudang ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">User:</div>
            <div>{{ $opname->user->name ?? '-' }}</div>
        </div>
        @if ($opname->keterangan)
            <div class="info-row">
                <div class="info-label">Keterangan:</div>
                <div>{{ $opname->keterangan }}</div>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 35%">Barang</th>
                <th style="width: 15%">Satuan</th>
                <th style="width: 15%">Stok Sistem</th>
                <th style="width: 15%">Stok Fisik</th>
                <th style="width: 15%">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($opname->details as $d)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $d->gudangStock->barang->nama_barang ?? '-' }}</td>
                    <td class="center">{{ $d->gudangStock->satuanTerkecil->nama_satuan ?? '-' }}</td>
                    <td class="right">{{ number_format($d->stok_sistem) }}</td>
                    <td class="right">{{ number_format($d->stok_fisik) }}</td>
                    <td class="right {{ $d->selisih > 0 ? 'text-green' : ($d->selisih < 0 ? 'text-red' : '') }}">
                        {{ $d->selisih > 0 ? '+' : '' }}{{ number_format($d->selisih) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Tidak ada detail item</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h4>RINGKASAN</h4>
        <div class="info-row">
            <div class="info-label">Total Item Dicek:</div>
            <div>{{ $opname->details->count() }} item</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Selisih Plus:</div>
            <div style="color: green;">+{{ number_format($opname->details->where('selisih', '>', 0)->sum('selisih')) }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Selisih Minus:</div>
            <div style="color: red;">{{ number_format($opname->details->where('selisih', '<', 0)->sum('selisih')) }}
            </div>
        </div>
    </div>

    <div style="margin-top: 40px;">
        <p style="font-size: 11px; color: #666;">
            Dicetak pada: {{ date('d M Y H:i:s') }}
        </p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>

</html>
