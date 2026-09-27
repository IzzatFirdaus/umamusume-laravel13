<x-layout title="Review queue">
    <h1 class="text-2xl font-semibold">Match review queue</h1>
    <p class="mt-1 text-sm text-zinc-600">Fuzzy and unmatched fetch results land here. The engine never merges them without your decision.</p>

    @if ($candidates->count() === 0)
        <p class="mt-8 rounded border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">
            Nothing pending. Run `php artisan uma:fetch` to populate.
        </p>
    @else
        <ul class="mt-6 space-y-4">
            @foreach ($candidates as $candidate)
                <li class="rounded border border-zinc-200 bg-white p-4 text-sm">
                    <div class="flex items-baseline justify-between">
                        <span class="font-medium">{{ $candidate->proposed_name }}</span>
                        <span class="text-xs text-zinc-500">{{ $candidate->match_tier->label() }} · {{ $candidate->source_key }} · {{ $candidate->created_by_fetch_at->toDateString() }}</span>
                    </div>
                    @if ($candidate->proposed_name_ja)
                        <p class="text-zinc-600">{{ $candidate->proposed_name_ja }}</p>
                    @endif
                    @if ($candidate->suggestedUmamusume)
                        <p class="text-zinc-600">Suggestion: {{ $candidate->suggestedUmamusume->name }}</p>
                    @endif

                    <form method="POST" action="{{ route('review.resolve', $candidate) }}" class="mt-3 flex flex-wrap items-center gap-2">
                        @csrf
                        <select name="status" class="rounded border border-zinc-300 px-2 py-1">
                            <option value="Confirmed">Confirm (merge into suggestion, or create)</option>
                            <option value="Aliased">Add as alias of…</option>
                            <option value="Rejected">Reject</option>
                        </select>
                        <input type="number" name="umamusume_id" placeholder="Umamusume id (optional)" class="w-44 rounded border border-zinc-300 px-2 py-1" value="{{ $candidate->suggested_umamusume_id }}">
                        <select name="alias_language" class="rounded border border-zinc-300 px-2 py-1">
                            <option value="">Alias language</option>
                            @foreach (\App\Enums\AliasLanguage::cases() as $language)
                                <option value="{{ $language->value }}">{{ $language->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded bg-zinc-900 px-3 py-1.5 text-white">Resolve</button>
                    </form>
                    @error('status')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">{{ $candidates->links() }}</div>
    @endif
</x-layout>
