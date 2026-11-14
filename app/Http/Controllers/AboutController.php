<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('About', [
            'version' => config('app.version'),
            'buildSha' => config('app.build_sha'),
            'feedUrl' => config('app.update_feed_url'),
        ]);
    }
}
