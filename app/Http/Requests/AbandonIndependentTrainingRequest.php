<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The abandon door for an in-progress Independent Training session (§1.1.7).
 *
 * The payload carries nothing: the run is named by the route and the route's model binding is the
 * only input the write reads. The class exists anyway so this controller's every write takes a Form
 * Request rather than a bare `Request` or no parameter at all (AGENTS.md §7). A boundary that is
 * consistent is one a future rule can be added to without changing the method's signature.
 */
class AbandonIndependentTrainingRequest extends FormRequest
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
        return [];
    }
}
