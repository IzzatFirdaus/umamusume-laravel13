<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CompleteTransferRequest;
use App\Actions\OfferTransferRequest;
use App\Actions\WithdrawTransferRequest;
use App\Http\Requests\TransferRequestRequest;
use App\Models\TransferRequest;
use App\Models\Veteran;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for Transfer Requests (Global notice 1157, 2026-11-05).
 *
 * Endpoints:
 * - POST   /veterans/{veteran}/transfers           -> store (offer)
 * - PUT    /transfers/{transfer}/complete          -> complete
 * - DELETE /transfers/{transfer}                   -> withdraw
 * - GET    /veterans/transfers                     -> index (Transfers page)
 *
 * All actions return Inertia redirects back to the Veteran detail or the
 * Transfers list.
 */
final class TransferRequestController extends Controller
{
    public function index(): Response
    {
        $offered = TransferRequest::query()
            ->where('status', TransferRequest::STATUS_OFFERED)
            ->with(['veteran' => fn ($q) => $q->with('trainingRun.umamusume')])
            ->latest('created_at')
            ->paginate(20);

        $completed = TransferRequest::query()
            ->where('status', TransferRequest::STATUS_COMPLETED)
            ->with(['veteran' => fn ($q) => $q->with('trainingRun.umamusume')])
            ->latest('completed_at')
            ->paginate(20);

        $withdrawn = TransferRequest::query()
            ->where('status', TransferRequest::STATUS_WITHDRAWN)
            ->with(['veteran' => fn ($q) => $q->with('trainingRun.umamusume')])
            ->latest('withdrawn_at')
            ->paginate(20);

        // The action door for every listed request, keyed by request id: complete and withdraw
        // reach the routes the table rows act on, composed here rather than in the page.
        $transferUrls = [];
        foreach ([$offered, $completed, $withdrawn] as $page) {
            foreach ($page->getCollection() as $transfer) {
                $transferUrls[$transfer->id] = [
                    'complete' => route('transfers.complete', $transfer),
                    'withdraw' => route('transfers.withdraw', $transfer),
                ];
            }
        }

        return Inertia::render('Veterans/TransferRequests', [
            'offered' => $offered,
            'completed' => $completed,
            'withdrawn' => $withdrawn,
            // URLs composed server-side so the page spells none (`AUD-20261102-028 F-01`): the
            // shell carries no Ziggy, so `route()` is not callable from page code.
            'veteransIndexUrl' => route('veterans.index'),
            'transferUrls' => $transferUrls,
        ]);
    }

    public function store(TransferRequestRequest $request, Veteran $veteran): RedirectResponse
    {
        $reward = $request->reward();

        $action = app(OfferTransferRequest::class);
        $action->handle($veteran, $reward);

        return redirect()->route('veterans.show', $veteran)
            ->with('success', 'Veteran offered for transfer.');
    }

    public function complete(TransferRequestRequest $request, TransferRequest $transfer): RedirectResponse
    {
        $reward = $request->reward();

        $action = app(CompleteTransferRequest::class);
        $action->handle($transfer, $reward);

        return redirect()->route('veterans.transfers')
            ->with('success', 'Transfer completed. Veteran removed from library.');
    }

    public function withdraw(TransferRequest $transfer): RedirectResponse
    {
        $action = app(WithdrawTransferRequest::class);
        $action->handle($transfer);

        return redirect()->route('veterans.transfers')
            ->with('success', 'Transfer offer withdrawn.');
    }
}
