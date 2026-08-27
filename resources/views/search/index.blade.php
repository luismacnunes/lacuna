<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Lacuna</title>
</head>
<body>
    <form method="GET" action="{{ route('search.index') }}">
        <input type="text" name="q" value="{{ $query }}" placeholder="Pergunta alguma coisa" size="60" autofocus>
        <button type="submit">Procurar</button>
    </form>

    @if ($query !== '' && count($results) === 0)
        <p>Sem resultados.</p>
    @endif

    @foreach ($results as $result)
        <hr>
        <p>
            <strong>{{ $result->title }}</strong>
            (semelhança {{ number_format($result->similarity, 3) }})
        </p>
        <p>{{ $result->content }}</p>
    @endforeach
</body>
</html>