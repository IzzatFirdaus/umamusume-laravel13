<x-layout title="Training runs">
    <div class="flex items-baseline justify-between">
        <h1 class="text-2xl font-semibold">Training runs</h1>
        <a href="{{ route('runs.create') }}" class="rounded bg-zinc-900 px-3 py-1.5 text-sm text-white">New run</a>
    </div>

    @if ($runs->count() === 0)
        <p class="mt-8 rounded border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">
            No runs yet. Create one to start logging turns.
        </p>
    @else
        <ul class="mt-6 divide-y divide-zinc-200 rounded border border-zinc-200 bg-white">
            @foreach ($runs as $run)
                <li class="flex items-baseline justify-between px-4 py-3 text-sm">
                    <a href="{{ route('runs.show', $run) }}" class="font-medium hover:underline">
                        {{ $run->umamusume->name }}
                        @if ($run->scenario)
                            <span class="text-zinc-500">· {{ $run->scenario }}</span>
                        @endif
                    </a>
                    <span class="text-zinc-500">{{ $run->status->label() }} · {{ $run->created_at->toDateString() }}</span>
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">{{ $runs->links() }}</div>
    @endif
</x-layout>
