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
        .condition-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .condition-column { min-width: 0; }
        .condition-title { margin: 0; padding: 6px; border: 1px solid #444; border-bottom: 0; background: #ddd; font-size: 12px; text-align: center; }
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        th, td { border: 1px solid #444; padding: 4px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
        th { background: #eee; }
        .number { text-align: right; }
        .empty { text-align: center; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    @php($formatMeasurement = static fn ($value): string => $value === null || $value === '' ? '-' : number_format((float) $value, 2, '.', ''))
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
            <div class="condition-grid">
                @foreach ([
                    'sebelum_cuci' => 'Sebelum Cuci',
                    'sesudah_cuci' => 'Sesudah Cuci',
                    'setelah_bakar' => 'Setelah Bakar',
                ] as $conditionCode => $conditionLabel)
                    @php($conditionActuals = $item->actuals->where('condition', $conditionCode))
                    <div class="condition-column">
                        <h3 class="condition-title">{{ $conditionLabel }}</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    @if ($conditionCode === 'setelah_bakar')
                                        <th>Spec</th>
                                    @endif
                                    <th>Actual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($conditionActuals as $actual)
                                    <tr>
                                        <td>{{ $actual->parameter_label }}</td>
                                        @if ($conditionCode === 'setelah_bakar')
                                            <td class="number">{{ $formatMeasurement($actual->specification) }}</td>
                                        @endif
                                        <td class="number">{{ $formatMeasurement($actual->actual) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="empty" colspan="{{ $conditionCode === 'setelah_bakar' ? 3 : 2 }}">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</body>
</html>
