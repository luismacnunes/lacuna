<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Lacuna — Cobertura</title>
</head>
<body>
    <h2>Cobertura ao longo do tempo</h2>

    @if ($weeks->isEmpty())
        <p>Ainda não há perguntas suficientes para mostrar uma linha.</p>
    @else
        @php
            $w = 640; $h = 220; $pad = 40;
            $plotW = $w - $pad * 2;
            $plotH = $h - $pad * 2;
            $n = $weeks->count();

            $points = $weeks->values()->map(function ($week, $i) use ($n, $pad, $plotW, $plotH) {
                $x = $n > 1 ? $pad + ($i / ($n - 1)) * $plotW : $pad + $plotW / 2;
                $y = $pad + $plotH - ($week['rate'] / 100) * $plotH;
                return round($x, 1) . ',' . round($y, 1);
            })->implode(' ');
        @endphp

        <svg viewBox="0 0 {{ $w }} {{ $h }}" width="{{ $w }}" height="{{ $h }}" role="img">
            <line x1="{{ $pad }}" y1="{{ $pad }}" x2="{{ $w - $pad }}" y2="{{ $pad }}" stroke="#ddd" />
            <line x1="{{ $pad }}" y1="{{ $pad + $plotH / 2 }}" x2="{{ $w - $pad }}" y2="{{ $pad + $plotH / 2 }}" stroke="#ddd" />
            <line x1="{{ $pad }}" y1="{{ $pad + $plotH }}" x2="{{ $w - $pad }}" y2="{{ $pad + $plotH }}" stroke="#999" />

            <text x="{{ $pad - 8 }}" y="{{ $pad + 4 }}" text-anchor="end" font-size="11">100%</text>
            <text x="{{ $pad - 8 }}" y="{{ $pad + $plotH / 2 + 4 }}" text-anchor="end" font-size="11">50%</text>
            <text x="{{ $pad - 8 }}" y="{{ $pad + $plotH + 4 }}" text-anchor="end" font-size="11">0%</text>

            <polyline points="{{ $points }}" fill="none" stroke="#14141A" stroke-width="2" />

            @foreach ($weeks->values() as $i => $week)
                @php
                    [$cx, $cy] = explode(',', explode(' ', $points)[$i]);
                @endphp
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="3" fill="#14141A" />
            @endforeach
        </svg>

        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <th>Semana</th>
                <th>Perguntas</th>
                <th>Respondidas</th>
                <th>Cobertura</th>
            </tr>
            @foreach ($weeks as $week)
                <tr>
                    <td>{{ $week['week']->format('d/m/Y') }}</td>
                    <td>{{ $week['total'] }}</td>
                    <td>{{ $week['answered'] }}</td>
                    <td>{{ $week['rate'] }}%</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>