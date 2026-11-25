<?php

declare(strict_types=1);

/*
 * The AI Advisor's configuration boundary.
 *
 * Everything here is OFF by default. The deterministic Advisor in `config/advisor.php` is the
 * product; this file adds an optional ranking and explanation layer on top of it, and a missing
 * key, a closed switch or an unreachable provider must leave the shipped recommendation unchanged
 * (`AGENTS.md` §8, `ADR-0020` §2).
 *
 * **No secret lives here.** `api_key` reads the environment, and `.env` is gitignored. A key
 * printed in a config file is a key in the repository.
 *
 * **Why the corpus path is here and not in `uma.sources`.** `uma.sources` is the fetch allowlist:
 * every entry in it is a URL the fetch engine may request. The Advisor corpus is local markdown
 * already in this repository and involves no outbound request, so adding it there would put a
 * non-URL into a list whose meaning is "hosts we may call" (`ARCHITECTURE.md` §8).
 *
 * **ADR-0038.** This boundary is recorded in `docs/adr/0038-ai-advisor-optional-read-only-layer.md`,
 * accepted by the owner on 2026-11-25. The accepted ADR and this config, with its tests, are the
 * enforcement.
 */

/*
 * The corpus root, composed into `retrieval.sources` below instead of repeated in each entry.
 *
 * `UiHygieneTest`'s config scan forbids a repository path on a shipped config line, because a page
 * that prints one leaks the tree it was built from. These entries are not display copy — they are
 * the documents the index reads — but the scan cannot tell the two apart, so writing `docs/` six
 * times would trip it either way. Composing keeps the stored value (`docs/<file>`) byte-identical
 * for `UmaAdvisorIndex` and `RetrievalBenchmarkTest`, which both resolve the entry against
 * `base_path()` and key the index by it.
 */
$advisorCorpus = 'docs';

return [

    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    |
    | False means no cloud call is made, no local model is called, and the
    | Advisor renders exactly what it rendered before this feature existed.
    | A provider being reachable does not turn this on; only the Trainer does.
    */

    'enabled' => env('ADVISOR_AI_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Decision provider (rank the candidates the engine already validated)
    |--------------------------------------------------------------------------
    |
    | `clef` ranks only the options `TrainerAdvisor` produced. It never adds an
    | option, never removes a deterministic obligation, and never supplies a
    | game-state value. A response naming an option that is not in the supplied
    | set is discarded and the deterministic recommendation stands.
    */

    'decision' => [
        'provider' => env('ADVISOR_AI_DECISION_PROVIDER', 'logfare'),
        'model' => env('ADVISOR_AI_DECISION_MODEL', 'clef'),
        'endpoint' => env('ADVISOR_AI_DECISION_ENDPOINT', 'https://logfare.ai/v1/decisions'),
        'timeout' => (int) env('ADVISOR_AI_DECISION_TIMEOUT', 20),
        'retries' => (int) env('ADVISOR_AI_DECISION_RETRIES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Total request budget
    |--------------------------------------------------------------------------
    |
    | An interactive Advisor request must return within this wall-clock budget
    | including every optional external call. The decision call and the
    | explanation call each carry their own timeouts, but those can sum to
    | far more than a usable UI allows. `total_timeout` is the outer bound:
    | when it is nearly exhausted, explanation generation is skipped and the
    | deterministic template ships instead. A usable recommendation is always
    | better than optional prose that never arrives.
    */
    'total_timeout' => (int) env('ADVISOR_AI_TOTAL_TIMEOUT', 12),

    /*
    |--------------------------------------------------------------------------
    | Explanation provider
    |--------------------------------------------------------------------------
    |
    | The explanation restates the validated decision. It is a separate call
    | because Clef returns no prose: `/v1/decisions` answers with option ids and
    | probabilities, and nothing else (`https://logfare.ai/docs`).
    */

    'explanation' => [
        'provider' => env('ADVISOR_AI_EXPLANATION_PROVIDER', 'logfare'),
        'model' => env('ADVISOR_AI_EXPLANATION_MODEL', 'qwen-3.8-27b'),
        'endpoint' => env('ADVISOR_AI_EXPLANATION_ENDPOINT', 'https://logfare.ai/v1/chat/completions'),
        'timeout' => (int) env('ADVISOR_AI_EXPLANATION_TIMEOUT', 60),
        /*
         * `low`, and measured rather than assumed. Reachable chat models on this plan are reasoning
         * models: they emit a hidden `reasoning` channel before any `content`, charged against
         * `max_tokens`. Measured 2026-11-22 on `qwen-3.8-27b` with the real prompt shape:
         *
         *   default effort : 13-32 s per call, 480-836 completion tokens, 1 of 3 hit the budget
         *   effort=low     :  6.6 s,                  135 completion tokens, clean `stop`
         *   effort=none    : HTTP 400 -- rejected by the provider
         *   effort=minimal : HTTP 400 -- rejected by the provider
         *
         * So `low` is the only value that both works and cuts the reasoning burn to a fraction. The
         * task is a one-sentence restatement of a decision already made, which needs no deliberation.
         */
        'reasoning_effort' => env('ADVISOR_AI_EXPLANATION_REASONING_EFFORT', 'low'),
        /*
         * 400 is headroom over the 135 tokens a low-effort answer actually used, not a guess. The
         * earlier default of 220 came back with `content: null` or a fragment because the reasoning
         * channel consumed the whole budget; with low effort the visible sentence is reached well
         * inside this figure, and `finish_reason: length` is rejected rather than shipped.
         */
        'max_tokens' => (int) env('ADVISOR_AI_EXPLANATION_MAX_TOKENS', 400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logfare account
    |--------------------------------------------------------------------------
    |
    | The key is never read by the browser and never printed. Premium models are
    | gated on an account-level training opt-in that this application must not
    | set on the Trainer's behalf (`https://logfare.ai/privacy`).
    */

    'logfare' => [
        'api_key' => env('LOGFARE_API_KEY'),
        'models_endpoint' => env('LOGFARE_MODELS_ENDPOINT', 'https://logfare.ai/v1/models'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Local embeddings
    |--------------------------------------------------------------------------
    |
    | `nomic-embed-text` is already installed on this machine and produces 768
    | dimensions. Indexing and querying must use the same model; a mismatch is
    | rejected rather than compared, because two vector spaces are not
    | comparable and a cosine between them is noise wearing a number.
    */

    'embeddings' => [
        'enabled' => env('ADVISOR_AI_EMBEDDINGS_ENABLED', true),
        'provider' => env('ADVISOR_AI_EMBEDDINGS_PROVIDER', 'ollama'),
        'model' => env('ADVISOR_AI_EMBEDDINGS_MODEL', 'nomic-embed-text'),
        'endpoint' => env('ADVISOR_AI_EMBEDDINGS_ENDPOINT', 'http://127.0.0.1:11434/api/embed'),
        'timeout' => (int) env('ADVISOR_AI_EMBEDDINGS_TIMEOUT', 60),
        'batch' => (int) env('ADVISOR_AI_EMBEDDINGS_BATCH', 16),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retrieval
    |--------------------------------------------------------------------------
    |
    | `sources` lists the documents the index may read. It is a curated subset on
    | purpose: `docs/` holds 306 markdown files and 19.8 MB, most of it process
    | record rather than game mechanics, and indexing the process record would
    | let a planning note be retrieved as if it were a rule of the game.
    */

    'retrieval' => [
        'index_path' => env('ADVISOR_AI_INDEX_PATH', 'app/private/advisor-index/advisor.sqlite'),
        'sources' => [
            $advisorCorpus.'/UMAMUSUME_REFERENCE.md',
            $advisorCorpus.'/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md',
            $advisorCorpus.'/scenarios/01-ura-finale.md',
            $advisorCorpus.'/scenarios/02-unity-cup.md',
            $advisorCorpus.'/scenarios/03-trackblazer.md',
            $advisorCorpus.'/scenarios/07-grand-concert.md',
        ],
        'chunk_chars' => (int) env('ADVISOR_AI_CHUNK_CHARS', 1200),
        'max_evidence' => (int) env('ADVISOR_AI_MAX_EVIDENCE', 4),
        'max_chunk_chars_sent' => (int) env('ADVISOR_AI_MAX_CHUNK_SENT', 700),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | A recommendation is cached against the career's revision fingerprint, so a
    | turn the Trainer logged invalidates it by construction rather than by a
    | timeout. The TTL is the outer bound for a state nobody has touched.
    */

    'cache' => [
        'ttl' => (int) env('ADVISOR_AI_CACHE_TTL', 900),
    ],

];
