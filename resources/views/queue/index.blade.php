<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Lacuna — Fila</title>
</head>
<body>
    <h2>Por responder</h2>

    @forelse ($topics as $topic)
        <hr>
        <h3>
            {{ $topic->name }}
            <small>({{ $topic->pending_questions_count }} em aberto)</small>
        </h3>

        @if (! $topic->has_material)
            <p><em>Sem material. É preciso dar input sobre este tema.</em>
               <a href="{{ route('documents.create') }}">Adicionar material</a></p>
        @elseif ($topic->owner)
            <p><small>Responsável: {{ $topic->owner->name }}</small></p>
        @endif

        <ul>
            @foreach ($topic->pendingQuestions as $pending)
                <li>
                    {{ $pending->text }}
                    @if ($pending->origin === App\Enums\PendingOrigin::RealFailure)
                        <strong>(alguém perguntou isto)</strong>
                    @endif
                </li>
            @endforeach
        </ul>
    @empty
        <p>Nada por responder.</p>
    @endforelse
</body>
</html>