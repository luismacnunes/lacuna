<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Lacuna</title>
</head>
<body>
    <form method="POST" action="{{ route('ask.store') }}">
        @csrf
        <input type="text" name="text" value="{{ $question?->text }}" placeholder="Pergunta alguma coisa" size="70" autofocus>
        <button type="submit">Perguntar</button>
    </form>

    @if ($question)
        <hr>

        @if ($question->answer)
            <p>{{ $question->answer->content }}</p>

            <h4>Fontes</h4>
            <ol>
                @foreach ($question->answer->curatedAnswers as $curated)
                    <li>
                        <strong>{{ $curated->topic->name }}</strong>
                        <em>(resposta curada)</em>
                        (semelhança {{ number_format($curated->pivot->similarity, 3) }})
                        <br>
                        <small>{{ Str::limit($curated->answer, 200) }}</small>
                    </li>
                @endforeach

                @foreach ($question->answer->chunks as $chunk)
                    <li>
                        <strong>{{ $chunk->document->title }}</strong>
                        (semelhança {{ number_format($chunk->pivot->similarity, 3) }})
                        <br>
                        <small>{{ Str::limit($chunk->content, 200) }}</small>
                    </li>
                @endforeach
            </ol>
        @else
            <p>O material disponível não cobre esta pergunta.</p>
            <p><small>Motivo: {{ $question->failure_reason->value }}</small></p>
        @endif
    @endif

    <hr>
    <p><a href="{{ route('documents.create') }}">Adicionar material</a></p>
</body>
</html>