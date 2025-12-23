<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;
use PokemonGoApi\PogoAPI\IO\GithubLoader;

use function count;
use function preg_match;
use function str_replace;

final readonly class PokemonImage
{
    private function __construct(
        private string $imageName,
        private int $dexNr,
        private int|null $assetBundleValue,
        private bool $isShiny,
        private string|null $assetBundleSuffix,
        private string|null $costume,
        private bool $isFemale,
    ) {
    }

    public static function createFromFilePath(string $path): self
    {
        $matches = [];
        $result  = preg_match(
            <<<'REGEX'
                ~/?pm(?<dexNr>\d{1,4})
                (\.f(?<form>[^\.]*))?
                (\.c(?<costume>[^\.]*))?
                (?<gender>\.g2)?
                (?<isShiny>\.s)?
                \.icon\.png~x
                REGEX,
            $path,
            $matches,
        );

        if ($result === false || count($matches) === 0) {
            throw new Exception('Path "' . $path . '" does not match Regex', 1617886414508);
        }

        return new self(
            $matches[0],
            (int) ($matches['dexNr']),
            null,
            ($matches['isShiny'] ?? '') !== '',
            $matches['form'] ?? null,
            $matches['costume'] ?? null,
            ($matches['gender'] ?? '') !== '',
        );
    }

    public function buildUrl(bool $shiny = false): string
    {
        if ($shiny) {
            return GithubLoader::ASSETS_BASE_URL . str_replace('.icon', '.s.icon', $this->imageName);
        }

        return GithubLoader::ASSETS_BASE_URL . $this->imageName;
    }

    public function getAssetBundleSuffix(): string|null
    {
        return $this->assetBundleSuffix;
    }

    public function getAssetBundleValue(): int|null
    {
        return $this->assetBundleValue;
    }

    public function getCostume(): string|null
    {
        return $this->costume;
    }

    public function getDexNr(): int
    {
        return $this->dexNr;
    }

    public function isShiny(): bool
    {
        return $this->isShiny;
    }

    public function isFemale(): bool
    {
        return $this->isFemale;
    }
}
