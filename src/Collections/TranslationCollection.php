<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Collections;

use function array_key_exists;
use function sprintf;

final class TranslationCollection
{
    private string $languageName;
    /** @var array<string, array<int, string>> */
    private array $pokemonNames = [];
    /** @var array<string, string> */
    private array $typeNames = [];
    /** @var array<string, string> */
    private array $moveNames = [];
    /** @var array<string, string> */
    private array $pokemonFormNames = [];

    public function __construct(string $languageName)
    {
        $this->languageName = $languageName;
    }

    public function getLanguageName(): string
    {
        return $this->languageName;
    }

    public function addPokemonName(int $dexNr, string $translation): void
    {
        $dexNrKey = sprintf('%04d', $dexNr);
        if (! array_key_exists($dexNrKey, $this->pokemonNames)) {
            $this->pokemonNames[$dexNrKey] = [];
        }

        $this->pokemonNames[$dexNrKey][] = $translation;
    }

    public function addPokemonFormName(string $formName, string $translation): void
    {
        $this->pokemonFormNames[$formName] = $translation;
    }

    public function addTypeName(string $type, string $translation): void
    {
        $this->typeNames[$type] = $translation;
    }

    public function addMoveName(string $move, string $translation): void
    {
        $this->moveNames[$move] = $translation;
    }

    public function getTypeName(string $typeName): ?string
    {
        return $this->typeNames[$typeName] ?? null;
    }

    public function getMoveName(int $moveId): ?string
    {
        return $this->moveNames[sprintf('%04d', $moveId)] ?? null;
    }

    /**
     * @return string[]
     */
    public function getPokemonNames(int $dexNr): array
    {
        $dexNrKey = sprintf('%04d', $dexNr);

        return $this->pokemonNames[$dexNrKey] ?? [];
    }

    public function getPokemonFormName(string $formName, string $fallbackName): ?string
    {
        return $this->pokemonFormNames[$formName] ?? $this->pokemonFormNames[$fallbackName] ?? null;
    }
}
