<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\TrainingRun;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The confirmation `runs.destroy` demands before it deletes a career (`C6b.3`, KI-144).
 *
 * Deletion is the one irreversible write in this tool, so the flag is a boundary rule rather than a
 * controller branch: the Form Request refuses a DELETE that carries no confirmation, which keeps the
 * validator's error bag and the redirect-back behaviour identical to every other write on this
 * controller. `accepted` rather than `boolean`, because the door posts `confirmed: true` and an
 * absent key must fail the same way a false one does, not read as "not provided, so default false".
 *
 * The 404 for a run id that does not exist stays with route-model binding; nothing here widens it.
 */
class ConfirmRunDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'confirmed' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmed.accepted' => 'Confirm that this career should be deleted.',
        ];
    }

    /**
     * Send a refused delete to the confirmation page, not to the referer.
     *
     * The hand-rolled branch this class replaced answered
     * `redirect()->route('runs.confirm-delete', $run)->withErrors(...)`. A FormRequest's default is
     * `url()->previous()`, which for a bare DELETE is the current URL - so the Trainer landed back on a
     * page that is not a page, with the error bag attached to nothing that renders it. The redirect has
     * to name the door the refusal is asking them to walk through.
     *
     * `getRedirectUrl()`, not `redirectTo()`: this Laravel line's `FormRequest::getRedirectUrl()` is the
     * only hook (`redirectTo` is a property set by the `RedirectTo` attribute, not a method), so an
     * override under that name is a method nothing calls and a redirect that stays wrong.
     */
    protected function getRedirectUrl(): string
    {
        $run = $this->route('run');

        return $run instanceof TrainingRun
            ? route('runs.confirm-delete', $run)
            : route('runs.index');
    }
}
