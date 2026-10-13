# Capture intake schema

New screenshots enter the research intake under `capture/`.  The original
1,159-frame corpus is never modified by an import; a capture reaches the
canonical corpus only through `--promote` with a named reviewer.

`capture/manifest.jsonl` is append-only: one JSON object per line, one line per
import.  Fields:

| Field               | Type            | Meaning |
| ------------------- | --------------- | ------- |
| `intake_id`         | UUID string     | Unique id for this import event. |
| `capture_timestamp` | string          | Capture time, ISO 8601 or `YYYYMMDDTHHMMSS` stem. |
| `scenario`          | string          | Free string; canonical values include `trackblazer`, `our_grand_concert`, `ura_finale`. |
| `game_version`      | string or null  | Client version when known. |
| `region`            | string or null  | Region tag when known (e.g. `global`). |
| `screen_type`       | string          | The screen the capture shows. |
| `source_filename`   | string          | The imported file's original name. |
| `sha256`            | hex string      | Content hash of the imported bytes. |
| `width`             | integer or null | Pixel width when readable from the header. |
| `height`            | integer or null | Pixel height when readable from the header. |
| `operator_context`  | string          | Free-text context supplied by the operator. |
| `promoted`          | boolean         | True only when copied into the canonical corpus. |
| `reviewed_by`       | string or null  | The reviewer who authorized promotion. |

A `capture_required` entry uses the same manifest with `"status": "capture_required"`,
a `missing_states` list, a `blocker` string, and no file copied.
