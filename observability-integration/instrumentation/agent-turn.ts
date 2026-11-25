/**
 * Backend-agnostic observability wrapper for MCode agent turns.
 *
 * Selects Latitude, Langfuse, or both via MCODE_OBSERVABILITY_BACKEND:
 *   - latitude   → agent self-healing (signals → monitors → MCP fix dispatch)
 *   - langfuse   → prompt/skill lifecycle + rigorous evaluation
 *   - both       → NOT recommended (duplicate ingestion, double cost)
 *
 * Install the chosen backend's SDKs in MCode's actual project before importing.
 */
import { initLatitude, shutdownLatitude } from "../latitude/init";
import {
  initLangfuse,
  startTurnSpan,
  shutdownLangfuse,
} from "../langfuse/init";

type Backend = "latitude" | "langfuse" | "both";

let backend: Backend | null = null;
let ready = false;

export interface TurnContext {
  userId?: string;
  sessionId?: string;
  tags?: string[];
  metadata?: Record<string, unknown>;
}

export interface TurnResult<T> {
  result: T;
  backend: Backend;
}

export function initObservability(
  preferredBackend?: Backend,
): { backend: Backend; initialized: boolean } {
  const configured =
    (process.env.MCODE_OBSERVABILITY_BACKEND as Backend) ?? "latitude";
  backend = preferredBackend ?? configured;

  switch (backend) {
    case "latitude":
      initLatitude();
      break;
    case "langfuse":
      initLangfuse();
      break;
    case "both":
      initLatitude();
      initLangfuse();
      break;
    default:
      throw new Error(
        `Unknown observability backend: ${backend}. Use "latitude", "langfuse", or "both".`,
      );
  }

  ready = true;
  return { backend, initialized: true };
}

export function getBackend(): Backend {
  if (!backend) {
    throw new Error(
      "Observability not initialised. Call initObservability() at app entrypoint first.",
    );
  }
  return backend;
}

/**
 * Capture an agent turn / skill invocation / tool call.
 *
 * Usage:
 *   const result = await captureTurn("minimax-completion", async () => {
 *     return await minimax.chat.completions.create({ ... });
 *   }, { userId, sessionId, tags: ["skill:summarize"] });
 */
export async function captureTurn<T>(
  name: string,
  fn: () => Promise<T>,
  ctx?: TurnContext,
): Promise<T> {
  if (!ready) {
    // Observability is opt-in; if not initialised, run the turn untraced.
    return await fn();
  }

  const b = backend!;

  if (b === "latitude" || b === "both") {
    return await (await import("../latitude/init")).captureTurn(
      name,
      fn,
      ctx,
    );
  }

  // Langfuse path: start a span, run the turn, end the span.
  const span = startTurnSpan(name, {
    input: ctx?.metadata,
    userId: ctx?.userId,
    sessionId: ctx?.sessionId,
    tags: ctx?.tags,
  });

  try {
    const result = await fn();
    span.update({ output: result }).end();
    return result;
  } catch (err) {
    span.update({ level: "error", statusMessage: String(err) }).end();
    throw err;
  }
}

/**
 * Flush and shut down the active backend(s). Call on app teardown / process exit.
 */
export async function shutdownObservability(): Promise<void> {
  if (!ready) return;

  const errors: unknown[] = [];

  if (backend === "latitude" || backend === "both") {
    try {
      await shutdownLatitude();
    } catch (err) {
      errors.push(err);
    }
  }

  if (backend === "langfuse" || backend === "both") {
    try {
      await shutdownLangfuse();
    } catch (err) {
      errors.push(err);
    }
  }

  ready = false;
  backend = null;

  if (errors.length > 0) {
    // Never fail the request — log only.
    // eslint-disable-next-line no-console
    console.error("Observability shutdown errors (non-fatal):", errors);
  }
}
