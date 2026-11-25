/**
 * Latitude observability init for MCode (MiniMax Code).
 * Cloud-first: no Docker required. Sends spans to https://ingest.latitude.so/v1/traces.
 *
 * Install first (in MCode's actual project, not this scaffolding dir):
 *   npm install @latitude-data/telemetry openai
 *
 * Env vars (see .env.example):
 *   LATITUDE_API_KEY, LATITUDE_PROJECT_SLUG, LATITUDE_TELEMETRY_URL
 */
import { Latitude } from "@latitude-data/telemetry";
import { createOpenAIInstrumentation } from "@latitude-data/telemetry/instrumentations/openai";
import OpenAI from "openai";

let latitude: Latitude | null = null;
let initialized = false;

export interface LatitudeInitOptions {
  apiKey?: string;
  project?: string;
  telemetryUrl?: string;
  instrumentations?: unknown[];
}

export function initLatitude(opts?: LatitudeInitOptions): Latitude {
  if (initialized && latitude) return latitude;

  const apiKey = opts?.apiKey ?? process.env.LATITUDE_API_KEY;
  const project = opts?.project ?? process.env.LATITUDE_PROJECT_SLUG;
  const telemetryUrl = opts?.telemetryUrl ?? process.env.LATITUDE_TELEMETRY_URL;

  if (!apiKey || !project) {
    throw new Error(
      "Latitude: LATITUDE_API_KEY and LATITUDE_PROJECT_SLUG must be set",
    );
  }

  latitude = new Latitude({
    apiKey,
    project,
    baseUrl: telemetryUrl,
    instrumentations: [
      createOpenAIInstrumentation(OpenAI),
      ...(opts?.instrumentations ?? []),
    ],
  });

  // Latitude SDK is async-initialised; await readiness before capturing.
  void latitude.ready.then(() => {
    initialized = true;
  });

  return latitude;
}

export function getLatitude(): Latitude {
  if (!latitude) {
    throw new Error(
      "Latitude: not initialised. Call initLatitude() at app entrypoint first.",
    );
  }
  return latitude;
}

/**
 * Wrap an agent turn / skill invocation / tool call so it appears as a
 * Latitude capture with session + user + tag context.
 */
export async function captureTurn<T>(
  name: string,
  fn: () => Promise<T>,
  ctx?: {
    userId?: string;
    sessionId?: string;
    tags?: string[];
    metadata?: Record<string, unknown>;
  },
): Promise<T> {
  const lat = getLatitude();
  return lat.capture(
    name,
    async () => {
      return await fn();
    },
    {
      userId: ctx?.userId,
      sessionId: ctx?.sessionId,
      tags: ctx?.tags,
      metadata: ctx?.metadata,
    },
  );
}

/**
 * Flush buffered spans and shut down. Call on process exit / app teardown.
 * Export failures are caught and logged — never fail the request.
 */
export async function shutdownLatitude(): Promise<void> {
  if (!latitude) return;
  try {
    await latitude.flush();
    await latitude.shutdown();
  } catch (err) {
    // eslint-disable-next-line no-console
    console.error("Latitude shutdown failed (non-fatal):", err);
  } finally {
    latitude = null;
    initialized = false;
  }
}
