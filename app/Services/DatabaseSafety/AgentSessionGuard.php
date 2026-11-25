<?php

declare(strict_types=1);

namespace App\Services\DatabaseSafety;

use App\Exceptions\DatabaseSafetyViolation;
use Illuminate\Database\Events\ConnectionEstablished;

/**
 * The fail-safe execution boundary: it lives where the application hands out a
 * database connection, not where a caller remembers to ask for it.
 *
 * Every previous guard layer is opt-in — it protects only when a role is
 * declared (`assertIsolatedContext`, `assertWriteSafe`) or only when a
 * destructive command is named (`assertCommandSafe`). A Tinker session, a
 * custom script, or a factory sweep that declares nothing at all sails past
 * all of them and writes the canonical file. That is how the synthetic
 * trainees entered `database/database.sqlite` on 2026-11-24.
 *
 * This listener inverts the default for *agent-looking* processes: any process
 * that carries an agent session marker (`CLAUDE_SESSION_ID` / `CODEBUDDY_SESSION_ID`)
 * and targets the canonical file is refused **unless** it has explicitly
 * isolated itself (an isolated role with a non-canonical, absolute DB path) or
 * the operator has acknowledged the write (`UMA_OPERATOR_ACKNOWLEDGED=yes`).
 * The Trainer's own interactive use — no session marker in the environment —
 * is never touched, so the application keeps working exactly as before.
 *
 * It listens to `ConnectionEstablished`, which the `DatabaseManager` fires on
 * every resolved connection (`connection()`, `connectUsing()`, `build()`),
 * so Query Builder writes, Eloquent mass operations, seeders, and Tinker all
 * pass through the same boundary before the first statement runs.
 */
class AgentSessionGuard
{
    /**
     * The environment variables that identify a process launched by a coding
     * agent. Their presence is not an authorization — it is the trigger for
     * the fail-safe refusal.
     */
    private const AGENT_MARKERS = [
        'CLAUDE_SESSION_ID',
        'CODEBUDDY_SESSION_ID',
    ];

    public function __construct(private readonly DatabaseGuard $guard) {}

    /**
     * Whether this process looks like an agent session.
     */
    public function isAgentSession(): bool
    {
        foreach (self::AGENT_MARKERS as $marker) {
            $value = $_SERVER[$marker] ?? $_ENV[$marker] ?? getenv($marker);

            if (is_string($value) && $value !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the operator explicitly authorised a canonical write for this
     * session. Requires the same acknowledgement pair the destructive-command
     * override uses, so an agent reading the error message cannot self-authorise.
     */
    public function isOperatorAuthorised(): bool
    {
        $ack = $_SERVER['UMA_OPERATOR_ACKNOWLEDGED'] ?? $_ENV['UMA_OPERATOR_ACKNOWLEDGED'] ?? getenv('UMA_OPERATOR_ACKNOWLEDGED');

        return is_string($ack) && strtolower($ack) === 'yes';
    }

    /**
     * The registered entry point: refuse an agent process that established a
     * connection to the canonical database without having isolated itself.
     */
    public function onConnectionEstablished(ConnectionEstablished $event): void
    {
        if (! $this->isAgentSession()) {
            return;
        }

        if (! $this->guard->isSqlite() || ! $this->guard->isFileBacked()) {
            return;
        }

        if (! $this->guard->targetsCanonical()) {
            return;
        }

        // An isolated role with a canonical target was already refused at boot by
        // `assertIsolatedContext`; reaching here means no isolated role was
        // declared (or a misleading one was) and the process still points at the
        // real file. The operator acknowledgement is the only way through.
        if ($this->isOperatorAuthorised()) {
            return;
        }

        $session = (string) ($_SERVER['CODEBUDDY_SESSION_ID'] ?? $_SERVER['CLAUDE_SESSION_ID']
            ?? $_ENV['CODEBUDDY_SESSION_ID'] ?? $_ENV['CLAUDE_SESSION_ID'] ?? 'unknown');

        throw new DatabaseSafetyViolation(
            'Agent session ['.substr($session, 0, 8).'] established a connection to the canonical development '
            ."database {$this->guard->canonicalPath()}. An agent process must isolate itself before touching the "
            .'real data: set UMA_DATABASE_ROLE to an isolated role and DB_DATABASE to an absolute scratch path '
            .'(see AGENTS.md section on database safety), or re-run with UMA_OPERATOR_ACKNOWLEDGED=yes to accept '
            .'responsibility for this session.'
        );
    }
}
