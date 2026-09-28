<?php

declare(strict_types=1);

use App\Models\TrainingRun;

/*
 * C2: the API error envelope, and the 422 branch that no endpoint can reach.
 *
 * `bootstrap/app.php:21-56` documents one shape for every non-2xx under `/api`:
 * `{ "error": { "code", "message" } }`. Three different places produce it, and only one
 * of them is a controller:
 *
 *   - UmamusumeController::show  returns it by hand for an unknown slug
 *   - bootstrap/app.php:38-43    renders it for a route that does not exist
 *   - bootstrap/app.php:38-43    renders it for a model that route-model binding cannot find
 *
 * The fourth branch, VALIDATION_ERROR at 422, is currently unreachable: routes/api.php
 * registers four GET routes, the write side stays in the web UI by design (PRD US-9, FR-E),
 * and no FormRequest guards a read. `ApiV1Test.php:54` is *titled* "returns a validation
 * error shape" but probes an unknown route, so the 422 branch has never been executed.
 *
 * The test states that unreachability rather than leaving it ambiguous, so the first write
 * endpoint added to this API has to come back here and prove its own envelope instead of
 * inheriting a branch nobody has run.
 */
it('uses the error envelope for an unknown route', function (): void {
    test()->getJson('/api/v1/nope')
        ->assertNotFound()
        ->assertJsonStructure(['error' => ['code', 'message']])
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('uses the error envelope for an unknown slug, from the controller branch', function (): void {
    $response = test()->getJson('/api/v1/umamusume/does-not-exist')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');

    // The controller's own message, as opposed to the central renderer's flat one: a
    // caller that prints `error.message` gets a sentence it can put in front of a human.
    expect($response->json('error.message'))->toContain('does-not-exist');
});

it('uses the error envelope for a run that does not exist, from route model binding', function (): void {
    // A different branch from the slug case: this one never reaches a controller, so it
    // exercises the central renderer rather than the hand-written response.
    test()->getJson('/api/v1/training-runs/999')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND')
        ->assertJsonPath('error.message', 'Resource not found.');
});

it('never answers an api request with html, whatever the failure', function (): void {
    $urls = ['/api/v1/nope', '/api/v1/umamusume/does-not-exist', '/api/v1/training-runs/999'];

    $types = [];

    foreach ($urls as $url) {
        $types[$url] = test()->getJson($url)->headers->get('content-type');
    }

    // The envelope is only worth having if a client can parse it without sniffing, so the
    // three branches are checked for the content type and not only for the body shape.
    foreach ($types as $url => $type) {
        expect($type)->toContain('application/json');
    }
});

it('leaves the 422 branch unreachable, because the api has no write', function (): void {
    $routes = collect(app('router')->getRoutes()->getRoutesByMethod())
        ->flatten()
        ->filter(fn ($route): bool => str_starts_with($route->uri(), 'api/'));

    // Every route is a read. The moment one is not, VALIDATION_ERROR becomes reachable
    // through a FormRequest and this test is wrong - which is the point of asserting it.
    expect($routes)->not->toBeEmpty()
        ->and($routes->filter(fn ($route): bool => ! in_array($route->methods()[0], ['GET', 'HEAD', 'OPTIONS'], true)))
        ->toBeEmpty();
});

it('reports the envelope of a malformed page size as a normal read, not a validation error', function (): void {
    TrainingRun::factory()->create();

    // `pageSize` is clamped rather than validated, so a nonsense value is a smaller page,
    // not a 422. Pinning that here stops a later "just validate it properly" change from
    // silently altering the response shape of every existing consumer.
    $response = test()->getJson('/api/v1/training-runs?pageSize=not-a-number');

    $response->assertOk()
        ->assertJsonPath('pagination.pageSize', 1);
});
