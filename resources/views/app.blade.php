<!DOCTYPE html>
{{--
    Inertia root template (ADR-0020 §1). Theme resolution matches components/layout.blade.php
    so the first paint is already correct: a stored preference wins, then the OS, then light.
    `$theme` is composed server-side by AppServiceProvider for this view.
--}}
<html lang="en" @if (! empty($theme)) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title inertia>Trainer Desk</title>
    @empty($theme)
        <script>
            (function () {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.dataset.theme = 'dark';
                }
            })();
        </script>
    @endempty
    @vite(['resources/css/app.css', 'resources/js/spa.ts'])
    @inertiaHead
</head>
<body class="min-h-screen bg-page text-ink">
    @inertia
</body>
</html>
