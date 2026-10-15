<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Preference;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * The theme reaches the document server-side, so the very first paint is
         * already the chosen theme and the inline head script never has to run
         * (D-104, PRD US-11). Reading it here rather than inside the layout keeps
         * the query out of the view; the layout already treats a null theme as
         * "no preference stored" and falls through to the system setting.
         *
         * Only the two resolved values are honoured. US-11 authorizes `light`,
         * `dark` and follow-the-OS, and the third is expressed as the *absence* of
         * a row — so an unrecognised stored value resolves to null too, and falls
         * back to the system script rather than writing an attribute no stylesheet
         * would match.
         *
         * All four root documents are composed: `app` (the Inertia root, ADR-0020 §1) and the
         * two error views that render themselves as documents, `errors::404` and `errors::419`,
         * because the Blade shell they used to share was retired with the Inertia port.
         * `errors.500` stays out on purpose: it is the page a Trainer sees when part of the app
         * is already failing, and it draws itself without the database. Composers run on every
         * render, so the lookup stays to the four views whose output depends on it.
         *
         * *Dated correction 2026-10-14:* these two views were listed only as `errors.404` and
         * `errors.419` from `0ea8d43` until now, and **that never matched a real error response**.
         * The exception renderer resolves the status page through the framework's `errors` view
         * hint, so the name the factory composes is `errors::404` with a double colon, and
         * `View::composer` matches names with `Str::is` — `errors.404` is not a pattern for
         * `errors::404`. Measured: a 404 request logged `composer:errors::404` and no `errors.404`,
         * and the document rendered `<html lang="en" >` with only the template's inline fallback,
         * while `/preferences` rendered `data-theme="dark"`. The dark-theme error page has therefore
         * been broken since the day this composition was added.
         *
         * Both spellings stay in the list, because both are live paths and neither implies the
         * other: the hint form is what the exception renderer composes, and the dotted form is what
         * application code and `ErrorPageViewsTest` render directly with `view('errors.419')`.
         * Dropping the dotted form fails that test's 500-canary, which counts the composer's
         * `preferences` read as the proof the canary is measuring something.
         */
        View::composer(['app', 'errors.404', 'errors.419', 'errors::404', 'errors::419'], function ($view): void {
            $view->with('theme', Preference::resolvedTheme());
        });
    }
}
