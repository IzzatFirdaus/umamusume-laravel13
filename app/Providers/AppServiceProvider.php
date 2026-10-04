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
         * Both root templates are composed: `components.layout` (the Blade shell) and
         * `app` (the Inertia root, ADR-0020 §1), so each first paint is already the
         * chosen theme. Composers run on every render, so the lookup stays to the two
         * views whose output depends on it.
         */
        View::composer(['components.layout', 'app'], function ($view): void {
            $theme = Preference::get('theme');

            $view->with('theme', in_array($theme, ['light', 'dark'], true) ? $theme : null);
        });
    }
}
