{{-- Tokens only: no zinc utility and no `dark:` fork. Same rule as create.blade.php (D-101, G-18, G-19). --}}
<x-layout title="Import a historical run">
    <h1 class="text-2xl font-semibold text-ink-strong">Import a historical run</h1>

    <p class="mt-2 max-w-2xl text-sm text-ink-muted">
        For a run you already finished. Paste or choose the CSV this app exports, and the turns come in as
        the record of a past career. The preview shows every row before anything is written, and the file
        has to be correct before the run exists: nothing is created until you confirm.
    </p>

    @if (isset($preview))
        {{-- Preview and commit are two POSTs to two routes, and the commit runs the same Form Request as
             this one did. The preview is therefore not a trust boundary: editing the flashed CSV into
             something invalid fails on the second POST rather than reaching the action, which is why the
             raw text is carried in a field at all instead of a signed token standing in for it. --}}
        <form method="POST" action="{{ route('runs.import.store') }}" class="mt-6">
            @csrf

            @foreach ($preview['run'] as $field => $value)
                <input type="hidden" name="{{ $field }}" value="{{ $value }}">
            @endforeach
            <textarea name="csv" class="hidden">{{ $preview['csv'] }}</textarea>

            <h2 class="text-lg font-semibold text-ink-strong">Confirm the import</h2>

            @php
                $trainee = $umamusumes->firstWhere('id', (int) $preview['run']['umamusume_id']);
            @endphp
            <p class="mt-2 text-sm text-ink-muted">
                <span class="font-semibold text-ink">{{ $trainee?->name ?? 'Unknown trainee' }}</span>
                · {{ $preview['run']['scenario'] ? ($scenarios[$preview['run']['scenario']] ?? $preview['run']['scenario']) : 'No scenario (baseline strip)' }}
                · {{ $preview['run']['status'] }}
                · {{ count($preview['turns']) }} turn{{ count($preview['turns']) === 1 ? '' : 's' }}
            </p>

            <p class="mt-3 max-w-2xl rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted" role="note">
                This imports turns only. Skills, the support deck and race entries are not part of the file
                format, so they stay empty and you add them per-turn on the run page afterwards. A sheet
                that recorded them is not lost, it just has no column here yet.
            </p>

            <div class="mt-4 overflow-x-auto" role="region" tabindex="0" aria-label="Rows to import">
                <table class="w-full border-collapse text-sm">
                    <caption class="sr-only">Every turn row that will be written</caption>
                    <thead>
                        <tr class="border-b border-rule text-left text-ink-muted">
                            <th class="py-1 pr-3">Turn</th><th class="pr-3">Speed</th><th class="pr-3">Stamina</th>
                            <th class="pr-3">Power</th><th class="pr-3">Guts</th><th class="pr-3">Wit</th>
                            <th class="pr-3">SP</th><th class="pr-3">Condition</th><th class="pr-3">Energy</th>
                            <th class="pr-3">Mood</th><th class="pr-3">Fans</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['turns'] as $row)
                            <tr class="border-b border-rule font-mono text-xs tabular-nums">
                                <td class="py-1 pr-3">{{ $row['turn'] }}</td>
                                <td class="pr-3">{{ $row['speed'] }}</td>
                                <td class="pr-3">{{ $row['stamina'] }}</td>
                                <td class="pr-3">{{ $row['power'] }}</td>
                                <td class="pr-3">{{ $row['guts'] }}</td>
                                <td class="pr-3">{{ $row['wit'] }}</td>
                                <td class="pr-3">{{ $row['sp'] ?? '' }}</td>
                                <td class="pr-3 font-sans">{{ $row['condition'] ?? '' }}</td>
                                <td class="pr-3">{{ $row['energy'] ?? '' }}</td>
                                <td class="pr-3">{{ $row['mood'] ?? '' }}</td>
                                <td class="pr-3">{{ $row['fans'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Import {{ count($preview['turns']) }} turns</button>
                <a href="{{ route('runs.import') }}" class="rounded-full border-2 border-rule px-3 py-1.5 text-sm font-semibold text-ink-strong">Start over</a>
            </div>
        </form>
    @else
        <form method="POST" action="{{ route('runs.import.preview') }}" enctype="multipart/form-data"
            class="mt-6 max-w-2xl space-y-4 rounded-md border border-rule bg-raised p-4 text-sm">
            @csrf

            <label class="block">
                <span class="font-medium text-ink">Trainee *</span>
                {{-- A plain select rather than create's combobox: an import names a trainee you already
                     ran, and the card gate the combobox exists to enforce has no field here. --}}
                <select name="umamusume_id" required class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">Choose a trainee</option>
                    @foreach ($umamusumes as $umamusume)
                        <option value="{{ $umamusume->id }}" @selected((int) old('umamusume_id') === $umamusume->id)>
                            {{ $umamusume->name }}@if ($umamusume->name_ja) · {{ $umamusume->name_ja }} @endif
                        </option>
                    @endforeach
                </select>
                @error('umamusume_id')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
            </label>

            <label class="block">
                <span class="font-medium text-ink">Scenario (optional)</span>
                {{-- Validated against the composition matrix's keys, inherited from the create form's
                     request. An empty choice is the baseline strip, and it is also the honest answer when
                     a paper sheet never recorded which scenario the run was. --}}
                <select name="scenario" class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    <option value="">Not set (baseline strip)</option>
                    @foreach ($scenarios as $key => $label)
                        <option value="{{ $key }}" @selected(old('scenario') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('scenario')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
            </label>

            <label class="block">
                <span class="font-medium text-ink">Status</span>
                <select name="status" required class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                    @foreach (\App\Enums\RunStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', 'Completed') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="font-medium text-ink">Notes (optional)</span>
                <textarea name="notes" rows="2" class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink">{{ old('notes') }}</textarea>
            </label>

            <div class="flex flex-col gap-1">
                <label for="import-file" class="font-medium text-ink">CSV file</label>
                {{-- When both are present the file wins: the request overwrites the pasted text with the
                     upload's contents, so choosing a file cannot silently mix with a stale paste. --}}
                <input id="import-file" type="file" name="file" accept=".csv,text/csv"
                    class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                @error('file')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
            </div>

            <label class="flex flex-col gap-1">
                <span class="font-medium text-ink">or paste the CSV</span>
                <span class="font-mono text-xs text-ink-muted">
                    {{ implode(', ', \App\Http\Requests\ImportHistoricalRunRequest::HEADERS) }}
                </span>
                <textarea name="csv" rows="8" required class="rounded-md border border-rule bg-raised px-2 py-1 font-mono text-xs text-ink"
                    placeholder="{{ implode(',', \App\Http\Requests\ImportHistoricalRunRequest::HEADERS) }}">{{ old('csv') }}</textarea>
                @error('csv')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
                @error('turns')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
                @error('turns.*.turn')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
                @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                    @error("turns.*.{$stat}")<p class="mt-1 text-risk">A {{ $stat }} value is outside what this scenario allows: {{ $message }}</p>@enderror
                @endforeach
                @error('turns.*.mood')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
                @error('turns.*.energy')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
            </label>

            <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Preview import</button>
        </form>
    @endif
</x-layout>
