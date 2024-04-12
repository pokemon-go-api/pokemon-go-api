<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer\Types;

use function count;
use function explode;
use function str_contains;
use function str_starts_with;
use function strlen;

class SplitBossName
{
    private readonly string $firstLine;
    private readonly string|null $secondLine;
    private readonly bool $isFirstLineSmall;

    public function __construct(private readonly string $fullName, int $splitByChars = 10)
    {
        $explodedName = [$this->fullName];
        if (strlen($this->fullName) >= $splitByChars) {
            if (str_contains($this->fullName, '-')) {
                $explodedName = explode('-', $this->fullName, 2);
            } elseif (str_contains($this->fullName, ' ')) {
                $explodedName = explode(' ', $this->fullName, 2);
            }
        }

        $firstLineSmall = true;
        if (count($explodedName) === 2 && str_starts_with($explodedName[1], '(')) {
            $firstLineSmall = false;
        }

        $this->firstLine        = $explodedName[0];
        $this->secondLine       = $explodedName[1] ?? null;
        $this->isFirstLineSmall = $firstLineSmall;
    }

    public function getFirstLine(): string
    {
        return $this->firstLine;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getSecondLine(): string|null
    {
        return $this->secondLine;
    }

    public function isFirstLineSmall(): bool
    {
        return $this->isFirstLineSmall;
    }

    public function isMultiline(): bool
    {
        return $this->secondLine !== null;
    }
}
