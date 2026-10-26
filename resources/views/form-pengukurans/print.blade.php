<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Pengukuran #{{ $formPengukuran->id }}</title>
    <style>
        @page { size: landscape; margin: 10mm; }
        body { color: #111; font-family: Arial, sans-serif; font-size: 11px; }
        h1, h2 { margin: 0 0 10px; }
        .actions { margin-bottom: 16px; }
        .meta { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px 16px; margin-bottom: 18px; }
        .item { break-inside: avoid; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #444; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #eee; }
        .number { text-align: right; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Print / Save as PDF</button></div>
    <h1>Form Pengukuran #{{ $formPengukuran->id }}</h1>
    <div class="meta">
        <div><strong>Area:</strong> {{ $formPengukuran->area->name }}</div>
        <div><strong>Customer:</strong> {{ $formPengukuran->customer->name }}</div>
        <div><strong>Master Mould:</strong> {{ $formPengukuran->masterMould->code }}</div>
        <div><strong>Tanggal Pengukuran:</strong> {{ $formPengukuran->tanggal_pengukuran?->format('d-m-Y') }}</div>
        <div><strong>Hari Ke:</strong> {{ $formPengukuran->hari_ke }}</div>
        <div><strong>Mesin:</strong> {{ $formPengukuran->mesin }}</div>
        <div><strong>Dibuat Oleh:</strong> {{ $formPengukuran->dibuat_oleh }}</div>
        <div><strong>Diperiksa Oleh:</strong> {{ $formPengukuran->diperiksa_oleh }}</div>
    </div>

    @foreach ($formPengukuran->items as $item)
        <section class="item">
            <h2>{{ $item->model_size_name }}</h2>
            @if ($item->remarks)
                <p><strong>Catatan:</strong> {{ $item->remarks }}</p>
            @endif
            <table>
                <thead>
                    <tr>
                        <th>Kondisi</th>
                        <th>Parameter</th>
                        <th>Deskripsi</th>
                        <th>Spesifikasi</th>
                        <th>Aktual</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($item->actuals as $actual)
                        <tr>
                            <td>{{ str($actual->condition)->replace('_', ' ')->title() }}</td>
                            <td>{{ $actual->parameter_label }}</td>
                            <td>{{ $actual->parameter_description }}</td>
                            <td class="number">{{ $actual->specification }}</td>
                            <td class="number">{{ $actual->actual }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endforeach
</body>
</html>
