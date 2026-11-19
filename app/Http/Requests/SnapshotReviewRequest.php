<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The snapshot review screen's read boundary.
 *
 * The review route accepts a POSTed payload, validates it, and shows the review; confirming later
 * re-posts the draft `store()` writes. Validation used to happen in `SnapshotController::reviewData()`
 * as `Validator::make($submitted, StoreSnapshotRequest::fieldRules())->validate()`, which is the
 * Floor's "no inline `$request->validate()`" defect expressed one layer down: the rules lived in a
 * Form Request but the BOUNDARY was a controller method, so this screen was the one read surface whose
 * refusals came from somewhere the next reader would not look.
 *
 * The rules are `StoreSnapshotRequest::fieldRules()` rather than a copy, because the review is a
 * preview of exactly the write `store()` will make. A rule added there must apply here on the same
 * request, or the screen would accept a payload the confirm then refuses.
 *
 * What this class does NOT own: whether a review has anything to show. A bare review URL with no
 * posted body and no parked draft is a real state (every field reads "Not provided"), not a validation
 * failure, so the controller still decides that before it asks this boundary anything.
 */
class SnapshotReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // local-only tool, no auth surface (ARCHITECTURE §8)
    }

    /**
     * The store's rules, but only for a request that carries a payload.
     *
     * A bare visit to the review URL is a real state, not a refused one: the HFW draws every field as
     * "Not provided" when no snapshot has been entered, and `SnapshotReviewTest` pins that. A
     * FormRequest's rules run on every method, so an unconditional set would have turned that state
     * into a validation redirect the moment the boundary moved here: the guarded set is what preserves
     * the screen's documented behaviour while keeping the boundary in one class.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->hasSnapshotPayload() ? StoreSnapshotRequest::fieldRules() : [];
    }

    /**
     * Whether this review was handed a payload to read, or is a bare visit.
     *
     * The body bag only, deliberately: reading `input()` would merge the query string, so a payload
     * that reached this route in a URL would be honoured and the URL is the thing being retired. The
     * two keys are the ones `StoreSnapshotRequest` requires, so a body carrying either is a submission
     * rather than a stray field.
     */
    public function hasSnapshotPayload(): bool
    {
        return $this->request->has('career_year') || $this->request->has('umamusume_id');
    }
}
