<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 9pt;
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f3f3;
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            font-size: 9pt;
        }
        td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 8pt;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            padding: 5px 0;
            border-top: 1px solid #ddd;
        }
        .page-break {
            page-break-after: always;
        }
        .summary {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>{{ $toko }}</p>
        <p>Tanggal Cetak: {{ $date }}</p>
        <p>Dicetak oleh: {{ $user }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Gudang</th>
                <th>Satuan</th>
                <th class="text-right">Harga Beli</th>
                <th class="text-right">Harga Jual</th>
                <th class="text-right">Stok Awal</th>
                <th class="text-right">Stok Masuk</th>
                <th class="text-right">Stok Keluar</th>
                {{-- <th class="text-right">Penyesuaian</th> --}}
                <th class="text-right">Stok Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->nama_gudang }}</td>
                <td>{{ $item->satuan_terkecil }}</td>
                <td class="text-right">{{ number_format($item->harga_beli, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->harga_jual, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->stok_awal, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->stok_masuk, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->stok_keluar, 2, ',', '.') }}</td>
                {{-- <td class="text-right">{{ number_format($item->penyesuaian, 2, ',', '.') }}</td> --}}
                <td class="text-right">{{ number_format($item->stok_akhir, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="summary">
        <p><strong>Total Item:</strong> {{ $data->count() }}</p>
        <p><strong>Total Stok Awal:</strong> {{ number_format($data->sum('stok_awal'), 2, ',', '.') }}</p>
        <p><strong>Total Stok Masuk:</strong> {{ number_format($data->sum('stok_masuk'), 2, ',', '.') }}</p>
        <p><strong>Total Stok Keluar:</strong> {{ number_format($data->sum('stok_keluar'), 2, ',', '.') }}</p>
        {{-- <p><strong>Total Penyesuaian:</strong> {{ number_format($data->sum('penyesuaian'), 2, ',', '.') }}</p> --}}
        <p><strong>Total Stok Akhir:</strong> {{ number_format($data->sum('stok_akhir'), 2, ',', '.') }}</p>
    </div>
    
    <div class="footer">
        Laporan ini dihasilkan pada {{ $date }} | {{ $toko }}
    </div>
</body>
</html>