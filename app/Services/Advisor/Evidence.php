<?php

declare(strict_types=1);

namespace App\Services\Advisor;

/**
 * One retrieved passage, with the provenance a surface needs to print or navigate (Advisor AI slice,
 * Phase C).
 *
 * `path` and `heading` are the citation targets the frontend can display: the file exists in this
 * repository and the heading exists in that file, so a Trainer can open it. A passage built from a
 * summary would have neither, which is why nothing here is summarised.
 */
final readonly class Evidence
{
    /**
     * @param  list<string>  $servers  subset of ['Global', 'JP'] the passage declares, or []
     */
    public function __construct(
        public string $id,
        public string $path,
        public string $heading,
        public string $text,
        public array $servers,
        public string $sourceHash,
        public int $line,
        public float $score = 0.0,
        public ?int $vectorRank = null,
    ) {}

    /**
     * The citation line a surface prints: the file, the heading it came from, and the server scope.
     *
     * Built from the record rather than from the retrieved text, so a passage cannot claim a source
     * the index did not read it from.
     */
    public function citation(): string
    {
        $scope = $this->servers === [] ? '' : ' ['.implode('/', $this->servers).']';

        return "{$this->path} § {$this->heading}{$scope}";
    }

    /**
     * What is sent to a provider for this passage.
     *
     * Truncated on the document's own sentence boundary where one exists, because a sentence cut
     * mid-word reads as a corrupted quote to a model and it will complete the thought itself.
     *
     * @return array{path: string, heading: string, servers: list<string>, text: string}
     */
    public function forProvider(int $maxChars): array
    {
        $text = $this->text;

        if (strlen($text) > $maxChars) {
            $slice = substr($text, 0, $maxChars);
            $lastStop = max(
                (int) strrpos($slice, '. '),
                (int) strrpos($slice, ".\n"),
            );

            $text = $lastStop > 200 ? substr($slice, 0, $lastStop + 1) : $slice;
        }

        return [
            'path' => $this->path,
            'heading' => $this->heading,
            'servers' => $this->servers,
            'text' => $text,
        ];
    }
}
