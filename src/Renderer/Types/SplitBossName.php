<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer\Types;

use function count;
use function explode;
use function strlen;
use function strpos;

class SplitBossName
{
    private string $fullName;
    private string $firstLine;
    private ?string $secondLine;
    private bool $isFirstLineSmall;

    public function __construct(string $name, int $splitByChars = 10)
    {
        $this->fullName = $name;

        $explodedName = [$name];
        if (strlen($name) >= $splitByChars) {
            if (strpos($name, '-') !== false) {
                $explodedName = explode('-', $name, 2);
            } elseif (strpos($name, ' ') !== false) {
                $explodedName = explode(' ', $name, 2);
            }
        }

        $firstLineSmall = true;
        if (count($explodedName) === 2 && strpos($explodedName[1], '(') === 0) {
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

    public function getSecondLine(): ?string
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
