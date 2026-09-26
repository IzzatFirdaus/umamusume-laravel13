<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Reads the declarative skill definitions from .agents/skills.json and hands
 * them out as validated-shaped arrays. Part of the skill-automation subsystem
 * that predates the Umamusume domain; touches none of the catalog/run tables.
 */
class SkillRegistry
{
    /** @var array<string, array{triggers?: array<string, mixed>, dependencies?: array<int, string>, enabled?: bool, command?: string, runner?: string, id: string, name: string, description: string, input?: array<string, mixed>, output?: array<string, mixed>}> */
    private array $skills = [];

    private string $registryPath;

    public function __construct(?string $registryPath = null)
    {
        $this->registryPath = $registryPath ?? base_path('.agents/skills.json');
        $this->load();
    }

    private function load(): void
    {
        if (! File::exists($this->registryPath)) {
            $this->skills = [];

            return;
        }

        $data = json_decode(File::get($this->registryPath), true, 512, JSON_THROW_ON_ERROR);
        $this->skills = array_column($data['skills'] ?? [], null, 'id');
    }

    /**
     * @return array<string, array{triggers?: array<string, mixed>, dependencies?: array<int, string>, enabled?: bool, command?: string, runner?: string, id: string, name: string, description: string, input?: array<string, mixed>, output?: array<string, mixed>}>
     */
    public function all(): array
    {
        return $this->skills;
    }

    /**
     * @return array<string, array{triggers?: array<string, mixed>, dependencies?: array<int, string>, enabled?: bool, command?: string, runner?: string, id: string, name: string, description: string, input?: array<string, mixed>, output?: array<string, mixed>}>
     */
    public function enabled(): array
    {
        return array_filter($this->skills, static fn (array $skill): bool => $skill['enabled'] ?? true);
    }

    /**
     * @return array{triggers?: array<string, mixed>, dependencies?: array<int, string>, enabled?: bool, command?: string, runner?: string, id: string, name: string, description: string, input?: array<string, mixed>, output?: array<string, mixed>}|null
     */
    public function get(string $id): ?array
    {
        return $this->skills[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->skills);
    }

    public function count(): int
    {
        return count($this->skills);
    }

    public function countEnabled(): int
    {
        return count($this->enabled());
    }

    public function registryPath(): string
    {
        return $this->registryPath;
    }

    /**
     * @return array{triggers?: array<string, mixed>, dependencies?: array<int, string>, enabled?: bool, command?: string, runner?: string, id: string, name: string, description: string, input?: array<string, mixed>, output?: array<string, mixed>}
     */
    public function require(string $id): array
    {
        $skill = $this->get($id);

        if ($skill === null) {
            throw new \InvalidArgumentException("Skill [{$id}] not found in registry.");
        }

        return $skill;
    }
}
