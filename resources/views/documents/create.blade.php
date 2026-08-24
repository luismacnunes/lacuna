<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Lacuna</title>
</head>
<body>
    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('documents.store') }}">
        @csrf

        <p><input type="text" name="title" value="{{ old('title') }}" placeholder="Título" required></p>
        <p><textarea name="content" rows="10" cols="60" placeholder="Cola aqui o conteúdo" required>{{ old('content') }}</textarea></p>
        <p><textarea name="description" rows="3" cols="60" placeholder="Em duas linhas, do que se trata">{{ old('description') }}</textarea></p>

        <button type="submit">Guardar</button>
    </form>
</body>
</html>