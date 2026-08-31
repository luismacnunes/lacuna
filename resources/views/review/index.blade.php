<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Lacuna — Revisão</title>
</head>
<body>
    <h2>A precisar de revisão</h2>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @forelse ($answers as $answer)
        <hr>

        <p>
            <strong>{{ $answer->topic->name }}</strong>
            <br>
            <small>
                Marcada em {{ $answer->flagged_at->format('d/m/Y') }} porque
                <em>{{ $answer->flaggedByDocument?->title ?? 'um documento' }}</em> mudou.
            </small>
        </p>

        <p><em>{{ $answer->question }}</em></p>

        <form method="POST" action="{{ route('review.update', $answer) }}">
            @csrf
            @method('PUT')
            <textarea name="answer" rows="5" cols="70">{{ $answer->answer }}</textarea>
            <br>
            <button type="submit">Guardar correcção</button>
        </form>

        <form method="POST" action="{{ route('review.confirm', $answer) }}" style="display:inline">
            @csrf
            <button type="submit">Continua válido</button>
        </form>

        <form method="POST" action="{{ route('review.destroy', $answer) }}" style="display:inline">
            @csrf
            @method('DELETE')
            <button type="submit">Remover</button>
        </form>
    @empty
        <p>Nada a rever.</p>
    @endforelse
</body>
</html>