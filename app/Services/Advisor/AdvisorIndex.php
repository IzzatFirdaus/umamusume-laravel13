<?php

declare(strict_types=1);

namespace App\Services\Advisor;

use PDO;
use RuntimeException;

/**
 * The local corpus index: a SQLite file with an FTS5 table and, when embeddings are available, a
 * parallel vector table (Advisor AI slice, Phase C/D).
 *
 * **Why SQLite and not a vector database.** The corpus is six documents and roughly 1.6 MB of
 * mechanics text; a brute-force cosine over that is microseconds and a vector database would add a
 * service to run, a port to fail, and a dependency `AGENTS.md` §5 requires approval for. FTS5 is
 * compiled into the PHP build already in use (`sqlite_compileoption_used('ENABLE_FTS5')` -> 1),
 * so lexical search costs nothing.
 *
 * **Why the index is a file and not a table.** It derives entirely from tracked markdown. A
 * migration would put derived rows in the database that `migrate:fresh --seed` cannot rebuild
 * without reading the same files again, and would make an index rebuild a schema concern. The file
 * lives under `storage/app/private/`, which is gitignored, so it can always be rebuilt.
 *
 * **Why vectors live beside the text rather than replacing it.** Lexical and semantic retrieval
 * answer different questions: an exact term like `Spirit Burst` needs the first, and "what do I do
 * about low Energy before a deadline" needs the second. Neither is a substitute for the other, and
 * an exact identifier looked up by cosine similarity is how a citation ends up pointing at a
 * passage that merely mentions the word.
 */
final class AdvisorIndex
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly string $path,
        private readonly int $chunkChars = 1200,
    ) {}

    /**
     * Opens (creating if needed) the index file.
     */
    public function connection(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $directory = dirname($this->path);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException("Cannot create the advisor index directory [{$directory}].");
        }

        $pdo = new PDO('sqlite:'.$this->path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        $this->pdo = $pdo;
        $this->migrate($pdo);

        return $pdo;
    }

    /**
     * Releases the index handle.
     *
     * The index is a container singleton, so its PDO outlives any single caller. A caller that
     * replaces the file underneath — a test building a per-process index, or a future reindex —
     * must be able to let go first: Windows refuses to unlink a SQLite file while a handle is open,
     * and the failure surfaces as "Resource temporarily unavailable" rather than as a lock.
     */
    public function close(): void
    {
        $this->pdo = null;
    }

    /**
     * Creates the three tables the index reads and writes.
     *
     * `chunks` holds the passage and its provenance; `chunks_fts` is the FTS5 mirror; `vectors` holds
     * one row per chunk that has an embedding, keyed by model so a changed embedding model cannot be
     * compared against vectors from the old one.
     */
    private function migrate(PDO $pdo): void
    {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS documents (
                path TEXT PRIMARY KEY,
                source_hash TEXT NOT NULL,
                indexed_at TEXT NOT NULL
            )
        SQL);

        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS chunks (
                id TEXT PRIMARY KEY,
                path TEXT NOT NULL,
                heading TEXT NOT NULL,
                line INTEGER NOT NULL,
                servers TEXT NOT NULL,
                text TEXT NOT NULL,
                source_hash TEXT NOT NULL
            )
        SQL);

        $pdo->exec(<<<'SQL'
            CREATE VIRTUAL TABLE IF NOT EXISTS chunks_fts
            USING fts5(chunk_id UNINDEXED, heading, text, tokenize = 'porter unicode61')
        SQL);

        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS vectors (
                chunk_id TEXT NOT NULL,
                model TEXT NOT NULL,
                dim INTEGER NOT NULL,
                vector BLOB NOT NULL,
                PRIMARY KEY (chunk_id, model)
            )
        SQL);
    }

    /**
     * Rebuilds one document's rows, or leaves it alone when its hash is unchanged.
     *
     * Returns whether anything was written, so a caller can report an incremental no-op honestly
     * instead of claiming a rebuild that did not happen.
     */
    public function indexDocument(string $relativePath, string $body): bool
    {
        $pdo = $this->connection();
        $hash = hash('sha256', $body);

        $existing = $pdo->prepare('SELECT source_hash FROM documents WHERE path = ?');
        $existing->execute([$relativePath]);

        if ($existing->fetchColumn() === $hash) {
            return false;
        }

        $pdo->beginTransaction();

        try {
            $delete = $pdo->prepare('DELETE FROM chunks WHERE path = ?');
            $delete->execute([$relativePath]);
            $pdo->prepare('DELETE FROM chunks_fts WHERE chunk_id NOT IN (SELECT id FROM chunks)')->execute();
            $pdo->prepare('DELETE FROM vectors WHERE chunk_id NOT IN (SELECT id FROM chunks)')->execute();

            $insert = $pdo->prepare(
                'INSERT INTO chunks (id, path, heading, line, servers, text, source_hash)
                 VALUES (:id, :path, :heading, :line, :servers, :text, :hash)'
            );
            $fts = $pdo->prepare('INSERT INTO chunks_fts (chunk_id, heading, text) VALUES (?, ?, ?)');

            foreach ($this->chunk($relativePath, $body) as $chunk) {
                $insert->execute([
                    'id' => $chunk['id'],
                    'path' => $relativePath,
                    'heading' => $chunk['heading'],
                    'line' => $chunk['line'],
                    'servers' => implode(',', $chunk['servers']),
                    'text' => $chunk['text'],
                    'hash' => $hash,
                ]);

                $fts->execute([$chunk['id'], $chunk['heading'], $chunk['text']]);
            }

            $pdo->prepare(
                'INSERT INTO documents (path, source_hash, indexed_at) VALUES (?, ?, ?)
                 ON CONFLICT(path) DO UPDATE SET source_hash = excluded.source_hash, indexed_at = excluded.indexed_at'
            )->execute([$relativePath, $hash, gmdate('c')]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Splits a document into passages on its own heading boundaries.
     *
     * A heading is the only structural marker the corpus reliably carries, and it is what a citation
     * points at, so a chunk never straddles two headings. A section longer than `chunkChars` is split
     * on paragraph boundaries; a section shorter than it stays whole, because padding a chunk to a
     * length is how a passage ends up quoting the neighbouring rule.
     *
     * @return list<array{id: string, heading: string, line: int, servers: list<string>, text: string}>
     */
    private function chunk(string $path, string $body): array
    {
        $lines = explode("\n", $body);
        $chunks = [];
        $heading = 'Document';
        $buffer = [];
        $bufferLine = 1;

        $flush = function () use (&$buffer, &$chunks, &$heading, &$bufferLine, $path): void {
            $text = trim(implode("\n", $buffer));

            if ($text === '') {
                return;
            }

            foreach ($this->splitLong($text) as $offset => $part) {
                $chunks[] = [
                    'id' => substr(hash('sha256', $path.'|'.$heading.'|'.$bufferLine.'|'.$offset), 0, 24),
                    'heading' => $heading,
                    'line' => $bufferLine,
                    'servers' => $this->servers($heading."\n".$part),
                    'text' => $part,
                ];
            }
        };

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;

            if (preg_match('/^(#{1,4})\s+(.*)$/', $line, $matches) === 1) {
                $flush();
                $buffer = [];
                $heading = trim($matches[2]);
                $bufferLine = $lineNumber;

                continue;
            }

            if ($buffer === []) {
                $bufferLine = $lineNumber;
            }

            $buffer[] = $line;
        }

        $flush();

        return $chunks;
    }

    /**
     * Splits an over-long section on paragraph boundaries, keeping paragraphs and tables intact.
     *
     * A markdown table is treated as one atomic block even though it carries no blank lines between
     * rows. Splitting it is how a retrieved passage ends up being a body row whose column headers
     * live in a different chunk: `| JP string as printed | 確率 (probability) |` answers nothing on
     * its own, and a model handed it will guess which column is which.
     *
     * @return list<string>
     */
    private function splitLong(string $text): array
    {
        if (strlen($text) <= $this->chunkChars) {
            return [$text];
        }

        $parts = [];
        $current = '';

        foreach ($this->blocks($text) as $block) {
            if ($current !== '' && strlen($current) + strlen($block) + 2 > $this->chunkChars) {
                $parts[] = $current;
                $current = $block;

                continue;
            }

            $current = $current === '' ? $block : $current."\n\n".$block;
        }

        if ($current !== '') {
            $parts[] = $current;
        }

        return $parts === [] ? [$text] : $parts;
    }

    /**
     * Groups a section into atomic blocks: contiguous paragraphs, and whole tables with their header.
     *
     * @return list<string>
     */
    private function blocks(string $text): array
    {
        $blocks = [];
        $current = [];
        $inTable = false;

        foreach (explode("\n", $text) as $line) {
            $isTableLine = str_starts_with(ltrim($line), '|');

            // A blank line closes a paragraph but never closes a table: markdown tables are written
            // as a run of rows, and a blank row inside one is a formatting choice, not a boundary.
            if (trim($line) === '' && ! $inTable) {
                if ($current !== []) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }

                continue;
            }

            if ($isTableLine && ! $inTable) {
                if ($current !== []) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }

                $inTable = true;
            } elseif (! $isTableLine && $inTable) {
                $blocks[] = implode("\n", $current);
                $current = [];
                $inTable = false;

                if (trim($line) === '') {
                    continue;
                }
            }

            $current[] = $line;
        }

        if ($current !== []) {
            $blocks[] = implode("\n", $current);
        }

        return array_values(array_filter(
            array_map('trim', $blocks),
            static fn (string $b): bool => $b !== '',
        ));
    }

    /**
     * The server scope a passage declares, read from the corpus's own `[Global]` / `[JP]` markers.
     *
     * Only what the text says. A passage that names neither is scoped `[]`, which a surface renders
     * as unknown rather than as either server: inferring a scope from the file it sits in would
     * silently promote a JP-only section to Global advice.
     *
     * @return list<string>
     */
    private function servers(string $text): array
    {
        $servers = [];

        if (stripos($text, '[Global]') !== false) {
            $servers[] = 'Global';
        }

        if (stripos($text, '[JP]') !== false) {
            $servers[] = 'JP';
        }

        return $servers;
    }

    /**
     * Lexical search over the FTS mirror, most relevant first.
     *
     * @param  int|null  $deadline  Unix microseconds deadline (unused for lexical search, accepted for signature compatibility).
     * @return list<array{id: string, path: string, heading: string, line: int, servers: list<string>, text: string, score: float}>
     */
    public function search(string $query, int $limit = 4, ?int $deadline = null): array
    {
        $terms = $this->terms($query);

        if ($terms === []) {
            return [];
        }

        $pdo = $this->connection();

        // Quoted per term with an OR join: FTS5 treats bare punctuation as syntax, and a query
        // containing a hyphen or a colon would otherwise throw rather than return nothing.
        $match = implode(' OR ', array_map(static fn (string $t): string => '"'.str_replace('"', '', $t).'"', $terms));

        $statement = $pdo->prepare(
            'SELECT c.id, c.path, c.heading, c.line, c.servers, c.text, bm25(chunks_fts) AS rank
             FROM chunks_fts f
             JOIN chunks c ON c.id = f.chunk_id
             WHERE chunks_fts MATCH :match
             ORDER BY rank
             LIMIT :limit'
        );
        $statement->bindValue(':match', $match);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        $rows = [];

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            /** @var array{id: string, path: string, heading: string, line: int|string, servers: string, text: string, rank: float|string} $row */
            $rows[] = [
                'id' => $row['id'],
                'path' => $row['path'],
                'heading' => $row['heading'],
                'line' => (int) $row['line'],
                'servers' => $row['servers'] === '' ? [] : explode(',', $row['servers']),
                'text' => $row['text'],
                // bm25 returns a negative score where more negative is better; flipped so callers
                // can sort descending and treat it as a relevance figure.
                'score' => -1 * (float) $row['rank'],
            ];
        }

        return $rows;
    }

    /**
     * The searchable terms of a free-text query.
     *
     * @return list<string>
     */
    private function terms(string $query): array
    {
        $words = preg_split('/[^a-z0-9]+/i', strtolower($query)) ?: [];

        return array_values(array_filter(
            $words,
            static fn (string $w): bool => strlen($w) >= 3 && ! in_array($w, self::STOPWORDS, true),
        ));
    }

    /** Words carrying no retrieval signal, filtered so a query is not matched on "the". */
    private const STOPWORDS = [
        'the', 'and', 'for', 'with', 'what', 'when', 'should', 'does', 'this', 'that', 'from',
        'have', 'has', 'are', 'was', 'were', 'will', 'can', 'not', 'but', 'you', 'your', 'its',
        'about', 'into', 'than', 'then', 'there', 'here', 'how', 'why', 'who', 'which',
    ];

    /**
     * Stores one embedding for one chunk.
     *
     * Values are packed as little-endian 32-bit floats rather than JSON: a 768-dimension vector is
     * 3 KB packed against roughly 15 KB as text, and the corpus is small enough that the difference
     * decides whether the index stays a single readable file.
     *
     * @param  list<float>  $vector
     */
    public function putVector(string $chunkId, string $model, array $vector): void
    {
        $packed = pack('g*', ...$vector);

        $this->connection()
            ->prepare('INSERT INTO vectors (chunk_id, model, dim, vector) VALUES (?, ?, ?, ?)
                       ON CONFLICT(chunk_id, model) DO UPDATE SET dim = excluded.dim, vector = excluded.vector')
            ->execute([$chunkId, $model, count($vector), $packed]);
    }

    /**
     * Every stored vector for one model, as chunk id to decoded floats.
     *
     * @return array<string, list<float>>
     */
    public function vectors(string $model): array
    {
        $statement = $this->connection()->prepare('SELECT chunk_id, dim, vector FROM vectors WHERE model = ?');
        $statement->execute([$model]);

        $vectors = [];

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            /** @var array{chunk_id: string, dim: int|string, vector: string} $row */
            /** @var list<float> $decoded */
            $decoded = array_values(unpack('g*', $row['vector']) ?: []);
            $vectors[$row['chunk_id']] = $decoded;
        }

        return $vectors;
    }

    /**
     * Chunks that have no vector for the given model yet, so indexing can be incremental.
     *
     * @return list<array{id: string, heading: string, text: string}>
     */
    public function unvectorised(string $model, int $limit = 64): array
    {
        $statement = $this->connection()->prepare(
            'SELECT c.id, c.heading, c.text FROM chunks c
             LEFT JOIN vectors v ON v.chunk_id = c.id AND v.model = :model
             WHERE v.chunk_id IS NULL
             LIMIT :limit'
        );
        $statement->bindValue(':model', $model);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        $rows = [];

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            /** @var array{id: string, heading: string, text: string} $row */
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * One chunk by id, so a vector hit can be turned back into its citation.
     *
     * @return array{id: string, path: string, heading: string, line: int, servers: list<string>, text: string}|null
     */
    public function chunkById(string $id): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT id, path, heading, line, servers, text FROM chunks WHERE id = ?'
        );
        $statement->execute([$id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        /** @var array{id: string, path: string, heading: string, line: int|string, servers: string, text: string} $row */
        return [
            'id' => $row['id'],
            'path' => $row['path'],
            'heading' => $row['heading'],
            'line' => (int) $row['line'],
            'servers' => $row['servers'] === '' ? [] : explode(',', $row['servers']),
            'text' => $row['text'],
        ];
    }

    /**
     * How many chunks the index holds, for a health report.
     */
    public function chunkCount(): int
    {
        return (int) $this->connection()->query('SELECT COUNT(*) FROM chunks')->fetchColumn();
    }

    /**
     * The hash of a document as last indexed, or null when it was never indexed.
     */
    public function indexedHash(string $path): ?string
    {
        $statement = $this->connection()->prepare('SELECT source_hash FROM documents WHERE path = ?');
        $statement->execute([$path]);
        $hash = $statement->fetchColumn();

        return $hash === false ? null : (string) $hash;
    }
}
