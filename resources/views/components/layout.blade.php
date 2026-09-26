<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Trainer Companion' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900">
    <nav class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-5xl gap-6 px-4 py-3 text-sm font-medium">
            <a href="{{ route('catalog.index') }}" class="hover:underline">Catalog</a>
            <a href="{{ route('runs.index') }}" class="hover:underline">Training runs</a>
            <a href="{{ route('review.index') }}" class="hover:underline">Review</a>
        </div>
    </nav>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('status'))
            <p class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('status') }}</p>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
