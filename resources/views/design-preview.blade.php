{{--
  ============================================================================
  TEMPORARY REVIEW SURFACE. DELETE THIS FILE WITH THE /design-preview ROUTE.
  ============================================================================

  Rendered only so the scenario components could be opened in a browser and their
  contrast measured in both themes. Sample data is assembled in routes/web.php by
  closure; there is no controller, no model and no query behind this page.

  Not a spec for the real run screen. Do not extend it. See KNOWN-ISSUES.md.
--}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scenario components · review surface</title>
    <script>
        /* Resolve the theme before first paint so there is no flash of the wrong one. */
        (function () {
            try {
                var t = localStorage.getItem('uma-theme');
                document.documentElement.dataset.theme = (t === 'light') ? 'light' : 'dark';
            } catch (e) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="min-h-screen bg-page font-sans text-sm text-ink">
    <div class="mx-auto max-w-6xl px-4 py-8">
        <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-ink-strong">Scenario composition review</h1>
                <p class="mt-1 text-ink-muted">
                    Every panel below is driven by <code class="font-mono">config/scenarios.php</code>.
                    No component branches on a scenario name.
                </p>
            </div>
            <button id="theme-toggle" type="button"
                    class="rounded-md border-2 border-rule bg-raised px-4 py-2 text-sm font-bold text-ink-strong">
                Toggle theme
            </button>
        </header>

        @foreach ($samples as $key => $sample)
            <section class="mt-8 border-t border-rule pt-6">
                <div class="mb-3 flex flex-wrap items-baseline gap-3">
                    <h2 class="text-lg font-semibold text-ink-strong">{{ $sample['label'] }}</h2>
                    <span class="rounded-md border border-rule px-2 py-0.5 font-mono text-xs tabular-nums text-ink-muted">
                        {{ $key }}
                    </span>
                    <span class="text-xs text-ink-muted">
                        Live on Global {{ $sample['live'] }} · {{ count($sample['links']) }} scenario link(s)
                    </span>
                </div>

                <x-resource-strip :scenario="$key" :run="$sample['run']" class="mb-3" />

                <x-stat-band :scenario="$key" :values="$sample['values']" :skill-points="$sample['sp']" class="mb-3" />

                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12">
                        <x-race-calendar :scenario="$key" :cells="$sample['calendar']" />
                    </div>
                    <div class="col-span-12">
                        <x-grade-point-meter :scenario="$key" :objectives="$sample['objectives']"
                                             :current="2" :earned="240" />
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($sample['steps'] as $step)
                        <a href="{{ route('design.preview', ['step' => $step]) }}"
                           class="rounded-full border px-3 py-1 text-xs font-bold
                                  {{ $sample['step'] === $step ? 'border-pick-line bg-raised text-ink-strong' : 'border-rule bg-panel text-ink-muted hover:border-green-line' }}">
                            {{ $step }}
                        </a>
                    @endforeach
                </div>

                <div class="mt-3 grid grid-cols-12 gap-3">
                    <div class="col-span-12 lg:col-span-7">
                        <x-guided-step :scenario="$key"
                                       :current="$sample['step']"
                                       :selected="$sample['selected']"
                                       :choices="$sample['choices']"
                                       :preview="$sample['preview']"
                                       :energy="$sample['run']['energy']" />
                    </div>
                    <div class="col-span-12 lg:col-span-5">
                        <div class="rounded-md border border-rule bg-panel p-3">
                            <h3 class="mb-2 text-xs font-bold uppercase tracking-widest text-ink-muted">
                                Scenario note
                            </h3>
                            <p class="text-sm text-ink">{{ $sample['note'] }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>

    <script>
        document.getElementById('theme-toggle').addEventListener('click', function () {
            var next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = next;
            try { localStorage.setItem('uma-theme', next); } catch (e) {}
        });
    </script>
</body>
</html>
