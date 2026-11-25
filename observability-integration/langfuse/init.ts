/**
 * Langfuse v5 observability init for MCode (MiniMax Code).
 * Cloud-first: no Docker required. Sends traces to Langfuse Cloud via OpenTelemetry.
 *
 * Install first (in MCode's actual project, not this scaffolding dir):
 *   npm install @langfuse/tracing @langfuse/otel @langfuse/client \
 *     "@opentelemetry/sdk-node@<0.300" @opentelemetry/api
 *
 * Env vars (see .env.example):
 *   LANGFUSE_SECRET_KEY, LANGFUSE_PUBLIC_KEY, LANGFUSE_HOST
 */
import { NodeSDK } from "@opentelemetry/sdk-node";
import { LangfuseSpanProcessor } from "@langfuse/otel";
import { startObservation } from "@langfuse/tracing";
import { LangfuseClient } from "@langfuse/client";

let sdk: NodeSDK | null = null;
let langfuseClient: LangfuseClient | null = null;
let initialized = false;

export interface LangfuseInitOptions {
  secretKey?: string;
  publicKey?: string;
  host?: string;
}

export function initLangfuse(opts?: LangfuseInitOptions): {
  sdk: NodeSDK;
  client: LangfuseClient;
} {
  if (initialized && sdk && langfuseClient) {
    return { sdk, client: langfuseClient };
  }

  const secretKey = opts?.secretKey ?? process.env.LANGFUSE_SECRET_KEY;
  const publicKey = opts?.publicKey ?? process.env.LANGFUSE_PUBLIC_KEY;
  const host = opts?.host ?? process.env.LANGFUSE_HOST ?? "https://cloud.langfuse.com";

  if (!secretKey || !publicKey) {
    throw new Error(
      "Langfuse: LANGFUSE_SECRET_KEY and LANGFUSE_PUBLIC_KEY must be set",
    );
  }

  sdk = new NodeSDK({
    spanProcessors: [
      new LangfuseSpanProcessor({
        publicKey,
        secretKey,
        baseUrl: host,
        // Never let a failed trace export fail the user request:
        shouldExportSpan: () => true,
      }),
    ],
  });

  sdk.start();

  langfuseClient = new LangfuseClient({
    secretKey,
    publicKey,
    baseUrl: host,
  });

  initialized = true;

  // Flush on process exit; errors swallowed so they never fail a request.
  for (const signal of ["beforeExit", "SIGINT", "SIGTERM"] as const) {
    process.once(signal, async () => {
      try {
        await sdk.shutdown();
      } catch (err) {
        // eslint-disable-next-line no-console
        console.error("Langfuse shutdown failed (non-fatal):", err);
      }
      if (signal !== "beforeExit") process.exit(0);
    });
  }

  return { sdk, client: langfuseClient };
}

export function getLangfuseClient(): LangfuseClient {
  if (!langfuseClient) {
    throw new Error(
      "Langfuse: not initialised. Call initLangfuse() at app entrypoint first.",
    );
  }
  return langfuseClient;
}

/**
 * Start a Langfuse observation around an agent turn / skill / tool call.
 * Call .end() on the returned span when the turn completes.
 */
export function startTurnSpan(
  name: string,
  attrs?: {
    input?: unknown;
    userId?: string;
    sessionId?: string;
    tags?: string[];
  },
) {
  return startObservation(name, {
    input: attrs?.input,
    userId: attrs?.userId,
    sessionId: attrs?.sessionId,
    tags: attrs?.tags,
    metadata: { component: "mcode-agent-turn" },
  });
}

export async function shutdownLangfuse(): Promise<void> {
  if (!sdk) return;
  try {
    await sdk.shutdown();
  } catch (err) {
    // eslint-disable-next-line no-console
    console.error("Langfuse shutdown failed (non-fatal):", err);
  } finally {
    sdk = null;
    langfuseClient = null;
    initialized = false;
  }
}
