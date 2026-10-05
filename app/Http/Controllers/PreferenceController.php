<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePreferenceRequest;
use App\Models\Preference;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The only writer for the `preferences` table (PRD US-11, SCREEN_SPEC.md §7-5).
 *
 * Two keys, one row each. The theme's third authorized value, follow the OS, is stored as the
 * absence of a row rather than as the word `system`, because that is what the composer in
 * `AppServiceProvider` already resolves: a row holding `system` would be a second claim about the
 * same preference that nothing reads.
 */
class PreferenceController extends Controller
{
    public function edit(): Response
    {
        // The same rule the layout composer applies: a stored value outside the two resolved
        // themes is not a theme, so it must not come back preselected as if it were.
        $theme = Preference::get('theme');

        return Inertia::render('Preferences/Edit', [
            'theme' => in_array($theme, ['light', 'dark'], true) ? $theme : null,
            'failureEstimate' => Preference::get('failure_estimate') ?? 'off',
        ]);
    }

    public function update(UpdatePreferenceRequest $request): RedirectResponse
    {
        $preferences = $request->validated();

        if ($preferences['theme'] === null) {
            Preference::query()->whereKey('theme')->delete();
        } else {
            Preference::put('theme', (string) $preferences['theme']);
        }

        Preference::put('failure_estimate', (string) $preferences['failure_estimate']);

        // Back to wherever the Trainer was: the control sits in the shared nav, so a save that
        // landed them on a preferences screen would move them off the page they were working on.
        return redirect()->back()->with('status', 'Preferences saved.');
    }
}
