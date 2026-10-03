<?php

declare(strict_types=1);

use App\Models\SupportCard;
use App\Services\DataPipeline\Contracts\SkillSourceParser;
use App\Services\DataPipeline\Parsers\GametoraSkillsParser;
use Database\Seeders\SourceDocumentSeeder;

/**
 * F-2: a seed that lost a source reported it in the log and exited 0. `uma:fetch` returns
 * `Command::FAILURE` when a source aborts, so the two paths that build the same catalogue disagreed about
 * what a partial result means, and `migrate --seed` on a broken body printed a warning into a green
 * command. The Trainer had no reason to believe the catalogue was incomplete.
 *
 * The continue stays (R76's precedent: one unimportable source must not cost the Trainer the other four),
 * and the failure becomes the command's exit status instead of a line nobody reads.
 */
function breakTheSkillsSource(): void
{
    // `GametoraSkillsParser` is final, so the failure is injected at the container rather than by
    // subclassing it: the branch is still selected by the config's own class string, and the instance
    // that gets resolved is one whose `parse()` throws the way a corrupt body would.
    app()->bind(
        GametoraSkillsParser::class,
        fn (): SkillSourceParser => new class implements SkillSourceParser
        {
            public function parse(string $body): array
            {
                throw new RuntimeException('body is unreadable');
            }
        },
    );
}

it('exits the seed as a failure when a source could not be imported', function (): void {
    breakTheSkillsSource();

    expect(function (): void {
        (new SourceDocumentSeeder)->run();
    })->toThrow(RuntimeException::class, 'gametora-skills');
});

it('still imports the other five, because the throw is the summary and not the abort', function (): void {
    breakTheSkillsSource();

    try {
        (new SourceDocumentSeeder)->run();
    } catch (RuntimeException) {
        // the point of the test, not a way to hide one
    }

    expect(SupportCard::query()->count())->toBeGreaterThan(0);
});

it('says nothing about failures when every body imports, so the first case is not every run throwing', function (): void {
    (new SourceDocumentSeeder)->run();

    expect(SupportCard::query()->count())->toBeGreaterThan(0);
});
