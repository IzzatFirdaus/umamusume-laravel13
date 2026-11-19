<!DOCTYPE html>
{{--
    Inertia root template (ADR-0020 §1). The first paint is already correct: a stored
    preference wins, then the OS, then light. `$theme` is composed server-side by
    AppServiceProvider for this view.
--}}
<html lang="en" @if (! empty($theme)) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-inertia>Trainer Desk</title>
    {{--
        Icon set (identity 2026-11-19). A single 1.32 MB 1254px PNG cannot serve a 32px tab
        favicon, an iOS home-screen tile and a search-result card at once, and it was untracked
        besides, so a clean clone served no icon at all. These four are the ladder that actually
        gets requested, and together they are 38 KB. `apple-touch-icon` is what iOS uses for
        "Add to Home Screen".

        public/favicon.svg stays unlinked, for two measured reasons rather than the size one this
        comment used to give: it is a VTracer autotrace of an older mark, so the one browser that
        would prefer it would show the previous logo; and until this change it carried no
        viewBox, so a 1024-unit drawing in a 32px viewport rendered as a blank colour block at
        every size (reproduced at 16, 32 and 64px). The viewBox is added, because a tracked asset
        that renders nothing is a defect whichever file links it, but the mark is still out of
        date and the PNGs carry the current one.
    --}}
    <link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
    <link rel="icon" href="/favicon-192.png" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <link rel="icon" href="/favicon-512.png" type="image/png" sizes="512x512">
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
<body class="app-backdrop min-h-screen bg-page text-ink">
    @inertia
</body>
</html>
