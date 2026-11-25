<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Illuminate\Support\Facades\Http;

it('scratch-verify: emits the scenario label and deterministic decision for an empty analysis', function (): void {
    config(['advisor_ai.enabled' => true, 'advisor_ai.embeddings.enabled' => false]);

    Http::fake();

    $run = TrainingRun::factory()->create(['scenario' => 'unity_cup']);
    TurnEntry::factory()->create([
        'training_run_id' => $run->id,
        'turn' => 1,
        'energy' => 55,
        'mood' => 'NORMAL',
    ]);

    $response = $this->get(route('runs.advisor-analysis', $run));

    $payload = $response->json();
    file_put_contents('.scratch-uma/session-b4373e33/adv-payload.json', json_encode($payload, JSON_PRETTY_PRINT));
    expect($response->getStatusCode())->toBe(200);
});
