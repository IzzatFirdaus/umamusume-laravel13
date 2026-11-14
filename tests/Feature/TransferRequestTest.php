<?php

declare(strict_types=1);

use App\Actions\CompleteTransferRequest;
use App\Actions\OfferTransferRequest;
use App\Actions\WithdrawTransferRequest;
use App\Models\TransferRequest;
use App\Models\Umamusume;
use App\Models\Veteran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Transfer Requests for Veteran Umamusume (Global notice 1157, 2026-11-05).
 *
 * Tests the lifecycle: offer -> complete | withdraw.
 * - offer: creates an offered TransferRequest, blocks duplicate offers
 * - complete: marks completed, deletes Veteran, records reward
 * - withdraw: marks withdrawn, Veteran stays
 *
 * The reward is recorded for audit (no wallet/ledger exists yet).
 */
it('creates the transfer_requests table with veteran foreign key and unique offered constraint', function (): void {
    expect(Schema::hasColumns('transfer_requests', [
        'id',
        'veteran_id',
        'status',
        'reward',
        'completed_at',
        'withdrawn_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    $fk = collect(Schema::getForeignKeys('transfer_requests'))
        ->map(fn (array $k): string => ($k['columns'][0] ?? '').'->'.$k['foreign_table'])
        ->first();

    expect($fk)->toBe('veteran_id->veterans');

    // Unique index on veteran_id + status
    $indexes = Schema::getIndexes('transfer_requests');
    $uniquePerStatus = collect($indexes)->firstWhere('name', 'one_per_veteran_per_status');
    expect($uniquePerStatus)->not->toBeNull();
});

it('offers a Veteran for transfer, creating an offered request', function (): void {
    $veteran = Veteran::factory()->create();

    $request = (new OfferTransferRequest)->handle($veteran, [
        'star_pieces' => 10,
        'monies' => 50000,
        'support_points' => 5,
    ]);

    expect($request->status)->toBe('offered')
        ->and($request->veteran_id)->toBe($veteran->id)
        ->and($request->reward)->toBe([
            'star_pieces' => 10,
            'monies' => 50000,
            'support_points' => 5,
        ])
        ->and($request->completed_at)->toBeNull()
        ->and($request->withdrawn_at)->toBeNull();
});

it('blocks a duplicate offer for the same Veteran', function (): void {
    $veteran = Veteran::factory()->create();

    (new OfferTransferRequest)->handle($veteran);

    expect(fn () => (new OfferTransferRequest)->handle($veteran))
        ->toThrow(RuntimeException::class, 'This Veteran already has an active transfer offer.');

    expect(TransferRequest::where('veteran_id', $veteran->id)->where('status', 'offered')->count())->toBe(1);
});

it('allows a new offer for a different veteran after the previous one was completed', function (): void {
    $veteran1 = Veteran::factory()->create();
    $veteran2 = Veteran::factory()->create();

    $first = (new OfferTransferRequest)->handle($veteran1);
    (new CompleteTransferRequest)->handle($first);

    // First offer is completed, second veteran should be offerable
    $second = (new OfferTransferRequest)->handle($veteran2);

    expect($second->id)->not->toBe($first->id)
        ->and($second->status)->toBe('offered')
        ->and($second->veteran_id)->toBe($veteran2->id);
});

it('allows a new offer after the previous one was withdrawn', function (): void {
    $veteran = Veteran::factory()->create();

    $first = (new OfferTransferRequest)->handle($veteran);
    (new WithdrawTransferRequest)->handle($first);

    // First offer is withdrawn, second should be allowed
    $second = (new OfferTransferRequest)->handle($veteran);

    expect($second->id)->not->toBe($first->id)
        ->and($second->status)->toBe('offered');
});

it('completes a transfer: marks completed, deletes Veteran, records reward', function (): void {
    $veteran = Veteran::factory()->create();
    $transfer = (new OfferTransferRequest)->handle($veteran, ['star_pieces' => 5]);

    $completed = (new CompleteTransferRequest)->handle($transfer, ['monies' => 10000]);

    expect($completed->status)->toBe('completed')
        ->and($completed->reward['monies'])->toBe(10000)
        ->and($completed->reward['star_pieces'])->toBe(5)
        ->and($completed->completed_at)->not->toBeNull()
        ->and($completed->withdrawn_at)->toBeNull()
        ->and(Veteran::find($veteran->id))->toBeNull() // Veteran deleted
        ->and(TransferRequest::find($completed->id))->not->toBeNull(); // Request kept for audit
});

it('refuses to complete a non-offered request', function (): void {
    $veteran = Veteran::factory()->create();
    $transfer = (new OfferTransferRequest)->handle($veteran);
    (new WithdrawTransferRequest)->handle($transfer);

    expect(fn () => (new CompleteTransferRequest)->handle($transfer))
        ->toThrow(RuntimeException::class, 'Only offered transfer requests can be completed.');
});

it('withdraws an offer: marks withdrawn, Veteran stays', function (): void {
    $veteran = Veteran::factory()->create();
    $transfer = (new OfferTransferRequest)->handle($veteran);

    $withdrawn = (new WithdrawTransferRequest)->handle($transfer);

    expect($withdrawn->status)->toBe('withdrawn')
        ->and($withdrawn->withdrawn_at)->not->toBeNull()
        ->and($withdrawn->completed_at)->toBeNull()
        ->and(Veteran::find($veteran->id))->not->toBeNull(); // Veteran remains
});

it('refuses to withdraw a non-offered request', function (): void {
    $veteran = Veteran::factory()->create();
    $transfer = (new OfferTransferRequest)->handle($veteran);
    (new CompleteTransferRequest)->handle($transfer);

    expect(fn () => (new WithdrawTransferRequest)->handle($transfer))
        ->toThrow(RuntimeException::class, 'Only offered transfer requests can be withdrawn.');
});

it('records completed_at and withdrawn_at automatically on status change', function (): void {
    $veteran = Veteran::factory()->create();
    $transfer = (new OfferTransferRequest)->handle($veteran);

    $completed = (new CompleteTransferRequest)->handle($transfer);
    expect($completed->completed_at)->not->toBeNull();

    $veteran2 = Veteran::factory()->create();
    $transfer2 = (new OfferTransferRequest)->handle($veteran2);

    $withdrawn = (new WithdrawTransferRequest)->handle($transfer2);
    expect($withdrawn->withdrawn_at)->not->toBeNull();
});

it('Veteran scopeTransferable excludes Veterans with active offers', function (): void {
    $v1 = Veteran::factory()->create();
    $v2 = Veteran::factory()->create();
    $v3 = Veteran::factory()->create();

    (new OfferTransferRequest)->handle($v1); // has active offer
    $offeredV2 = (new OfferTransferRequest)->handle($v2);
    (new WithdrawTransferRequest)->handle($offeredV2); // withdrawn offer -> transferable

    $transferable = Veteran::transferable()->pluck('id')->all();

    expect($transferable)->toContain($v2->id) // withdrawn offer -> transferable
        ->and($transferable)->toContain($v3->id) // never offered -> transferable
        ->and($transferable)->not->toContain($v1->id); // active offer -> not transferable
});

it('TransferRequest model casts reward as array and dates as datetime', function (): void {
    $veteran = Veteran::factory()->create();
    $transfer = TransferRequest::create([
        'veteran_id' => $veteran->id,
        'status' => 'offered',
        'reward' => ['star_pieces' => 3],
    ]);

    expect($transfer->reward)->toBe(['star_pieces' => 3])
        ->and($transfer->created_at)->toBeInstanceOf(Carbon::class)
        ->and($transfer->isOffered())->toBeTrue()
        ->and($transfer->isCompleted())->toBeFalse()
        ->and($transfer->isWithdrawn())->toBeFalse();
});

it('TransferRequest status constants', function (): void {
    expect(TransferRequest::STATUS_OFFERED)->toBe('offered')
        ->and(TransferRequest::STATUS_COMPLETED)->toBe('completed')
        ->and(TransferRequest::STATUS_WITHDRAWN)->toBe('withdrawn');
});
