<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Lacuna — Responder</title>
</head>
<body>
    <p><a href="{{ route('queue.index') }}">&larr; Voltar à fila</a></p>

    <h2>{{ $pending->text }}</h2>

    @if ($pending->origin === App\Enums\PendingOrigin::RealFailure)
        <p><strong>Alguém perguntou isto e o sistema não soube responder.</strong></p>
    @else
        <p><small>Pergunta gerada a partir de: {{ $pending->document?->title ?? 'sem documento' }}</small></p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('curate.update', $pending) }}">
        @csrf
        @method('PUT')

        <p>
            <label>Tema<br>
                <input type="text" name="topic_name" value="{{ old('topic_name', $pending->topic->name) }}" size="50" required>
            </label>
        </p>

        <p>
            <label>Resposta<br>
                <textarea name="answer" rows="8" cols="70" required autofocus>{{ old('answer') }}</textarea>
            </label>
        </p>

        <button type="submit">Guardar resposta</button>
    </form>

    <form method="POST" action="{{ route('curate.dismiss', $pending) }}">
        @csrf
        @method('DELETE')
        <button type="submit">Não vale a pena responder</button>
    </form>
</body>
</html>