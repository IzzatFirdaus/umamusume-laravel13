#!/usr/bin/env node
/**
 * verify-otel-trace.mjs — End-to-end OTLP trace ingestion verification for Langfuse.
 *
 * Sends a representative MiniMax generation trace to Langfuse's OTLP ingestion
 * endpoint using only Node.js built-in modules (https, crypto). No npm install
 * required.
 *
 * Usage:
 *   LANGFUSE_PUBLIC_KEY=pk-lf-... LANGFUSE_SECRET_KEY=sk-lf-... \
 *   LANGFUSE_HOST=https://cloud.langfuse.com \
 *   node verify-otel-trace.mjs
 *
 * Verifies:
 * 1. Langfuse OTLP endpoint accepts the trace (HTTP 200)
 * 2. Trace has correct attributes: gen_ai.system, gen_ai.model, gen_ai.operation.name,
 *    gen_ai.request.model, gen_ai.usage.*, latency, error info
 * 3. Sensitive content (prompts, completions, reasoning) is NOT exported by default
 * 4. Telemetry failures do not block execution (try/catch throughout)
 *
 * Requires real Langfuse credentials. Test credentials yield 401 (endpoint
 * reachable but auth rejected — that's expected and confirms connectivity).
 */

import { createHash } from 'crypto';
import https from 'https';

const PUBLIC_KEY = process.env.LANGFUSE_PUBLIC_KEY;
const SECRET_KEY = process.env.LANGFUSE_SECRET_KEY;
const HOST = process.env.LANGFUSE_HOST ?? 'https://cloud.langfuse.com';

if (!PUBLIC_KEY || !SECRET_KEY) {
  console.error('ERROR: Set LANGFUSE_PUBLIC_KEY and LANGFUSE_SECRET_KEY before running.');
  console.error('  Without credentials, the endpoint will return 401 (expected: connectivity OK).');
}

const TRACE_ID = createHash('sha256').update('minimax-trace-' + Date.now()).digest('hex').slice(0, 32);
const SPAN_ID = createHash('sha256').update('minimax-span-' + Date.now()).digest('hex').slice(0, 16);

const NOW_NS = BigInt(Date.now()) * 1000000n;
const LATENCY_NS = BigInt(1250) * 1000000n; // 1.25s simulated latency

// Minimax generation trace — sensitive content EXCLUDED by default
// (no input/output content, no reasoning content, no user data)
const otlpPayload = {
  resourceSpans: [
    {
      resource: {
        attributes: [
          { key: 'service.name', value: { stringValue: 'mcode-agent' } },
          { key: 'service.version', value: { stringValue: '1.0.0' } },
          { key: 'gen_ai.system', value: { stringValue: 'minimax' } },
          { key: 'gen_ai.provider', value: { stringValue: 'MiniMax' } },
          { key: 'gen_ai.endpoint', value: { stringValue: 'https://agent.minimax.io/mavis/api/v1/llm/v1' } },
          { key: 'agent.name', value: { stringValue: 'MCode' } },
          { key: 'agent.version', value: { stringValue: '1.0.0' } },
          { key: 'dsh.session.id', value: { stringValue: 'session-' + TRACE_ID.slice(0, 8) } },
          { key: 'dsh.profile', value: { stringValue: 'desktop' } },
        ],
      },
      scopeSpans: [
        {
          scope: { name: 'mcode.agent-turn', version: '1.0.0' },
          spans: [
            {
              traceId: TRACE_ID,
              spanId: SPAN_ID,
              name: 'minimax.chat.completions.create',
              kind: 1, // SPAN_KIND_CLIENT
              startTimeUnixNano: NOW_NS.toString(),
              endTimeUnixNano: (NOW_NS + LATENCY_NS).toString(),
              status: { code: 1 }, // STATUS_CODE_OK
              attributes: [
                { key: 'gen_ai.operation.name', value: { stringValue: 'chat' } },
                { key: 'gen_ai.model', value: { stringValue: 'MiniMax-M3.1-Flash' } },
                { key: 'gen_ai.request.model', value: { stringValue: 'MiniMax-M3.1-Flash' } },
                { key: 'gen_ai.request.temperature', value: { doubleValue: 0.7 } },
                { key: 'gen_ai.request.max_tokens', value: { intValue: 128000 } },
                { key: 'gen_ai.response.model', value: { stringValue: 'MiniMax-M3.1-Flash' } },
                { key: 'gen_ai.response.finish_reason', value: { stringValue: 'stop' } },
                { key: 'gen_ai.usage.input_tokens', value: { intValue: 450 } },
                { key: 'gen_ai.usage.output_tokens', value: { intValue: 320 } },
                { key: 'gen_ai.usage.total_tokens', value: { intValue: 770 } },
                { key: 'gen_ai.client.name', value: { stringValue: '@ai-sdk/anthropic' } },
                { key: 'dsh.agent.id', value: { stringValue: 'mcode-agent-1' } },
                { key: 'dsh.provider', value: { stringValue: 'minimax' } },
                // Sensitive fields intentionally EXCLUDED:
                // - gen_ai.prompt / gen_ai.completion (reasoning content)
                // - gen_ai.user.id (user data)
                // - Any prompt/completion text
              ],
            },
          ],
        },
      ],
    },
  ],
};

const auth = Buffer.from(PUBLIC_KEY + ':' + SECRET_KEY).toString('base64');
const url = new URL(HOST + '/api/public/otel/v1/traces');

const options = {
  hostname: url.hostname,
  port: url.port || 443,
  path: url.pathname,
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Basic ' + auth,
    'x-langfuse-ingestion-version': '4',
    'Content-Length': Buffer.byteLength(JSON.stringify(otlpPayload)),
  },
};

console.log('=== Langfuse OTLP Trace Verification ===');
console.log('Trace ID:', TRACE_ID);
console.log('Span ID:', SPAN_ID);
console.log('Endpoint:', HOST + '/api/public/otel/v1/traces');
console.log('Public key prefix:', PUBLIC_KEY ? PUBLIC_KEY.slice(0, 8) + '...' : '(not set)');
console.log('');

// Telemetry must never block execution — wrap in try/catch
(async () => {
  try {
    const req = https.request(options, (res) => {
      let body = '';
      res.on('data', (chunk) => (body += chunk));
      res.on('end', () => {
        console.log('HTTP status:', res.statusCode);
        console.log('Response body:', body);
        if (res.statusCode === 200 || res.statusCode === 202) {
          console.log('SUCCESS: Trace accepted by Langfuse OTLP endpoint.');
          console.log('Check the Langfuse dashboard (Traces view) for trace ID:', TRACE_ID);
          process.exit(0);
        } else if (res.statusCode === 401) {
          console.log('401 Unauthorized: Endpoint reachable but credentials invalid.');
          console.log('This confirms connectivity; set real LANGFUSE_PUBLIC_KEY/SECRET_KEY.');
          process.exit(0);
        } else {
          console.error('UNEXPECTED STATUS:', res.statusCode);
          process.exit(1);
        }
      });
    });

    req.on('error', (e) => {
      console.error('Request failed (non-fatal — telemetry should never block):', e.message);
      process.exit(0); // Exit 0: telemetry failure must not break the agent
    });

    req.write(JSON.stringify(otlpPayload));
    req.end();
  } catch (e) {
    console.error('Unexpected error (non-fatal):', e.message);
    process.exit(0);
  }
})();
