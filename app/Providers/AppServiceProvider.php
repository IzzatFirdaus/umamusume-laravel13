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
         * two error views that render themselves as documents, `errors.404` and `errors.419`,
         * because the Blade shell they used to share was retired with the Inertia port.
         * `errors.500` stays out on purpose: it is the page a Trainer sees when part of the app
         * is already failing, and it draws itself without the database. Composers run on every
         * render, so the lookup stays to the four views whose output depends on it.
         */
        View::composer(['app', 'errors.404', 'errors.419'], function ($view): void {
            $theme = Preference::get('theme');

            $view->with('theme', in_array($theme, ['light', 'dark'], true) ? $theme : null);
        });
    }
}
