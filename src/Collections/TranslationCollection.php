<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use function array_key_exists;
use function array_values;
use function mb_strtolower;
use function mb_strtoupper;
use function sprintf;
use function str_replace;

final class TranslationCollection
{
    private string $languageName;
    /** @var array<string, string> */
    private array $pokemonNames = [];
    /** @var array<string, array<int, string>> */
    private array $pokemonMegaNames = [];
    /** @var array<string, string> */
    private array $typeNames = [];
    /** @var array<string, string> */
    private array $moveNames = [];
    /** @var array<string, string> */
    private array $pokemonFormNames = [];
    /** @var array<string, string> */
    private array $regionalForms = [];
    /** @var array<string, string> */
    private array $customTranslations = [];
    /** @var array<string, string> */
    private array $quests = [];
    /** @var array<string, string> */
    private array $weather = [];
    /** @var array<string, string> */
    private array $items = [];

    public function __construct(string $languageName)
    {
        $this->languageName = $languageName;
    }

    public function getLanguageName(): string
    {
        return $this->languageName;
    }

    public function addQuest(string $key, string $translation): void
    {
        $this->quests[mb_strtolower($key)] = $translation;
    }

    public function addWeather(string $key, string $translation): void
    {
        $this->weather[mb_strtolower($key)] = $translation;
    }

    public function addItem(string $key, string $translation): void
    {
        $this->items[mb_strtolower($key)] = $translation;
    }

    public function addPokemonName(int $dexNr, string $translation): void
    {
        $dexNrKey                      = sprintf('%04d', $dexNr);
        $this->pokemonNames[$dexNrKey] = $translation;
    }

    public function addPokemonMegaName(int $dexNr, string $megaEvolutionIndex, string $translation): void
    {
        $dexNrKey = sprintf('%04d', $dexNr);
        if (! array_key_exists($dexNrKey, $this->pokemonMegaNames)) {
            $this->pokemonMegaNames[$dexNrKey] = [];
        }

        $this->pokemonMegaNames[$dexNrKey][$megaEvolutionIndex] = $translation;
    }

    public function addPokemonFormName(string $formName, string $translation): void
    {
        $this->pokemonFormNames[mb_strtoupper($formName)] = $translation;
    }

    public function addTypeName(string $type, string $translation): void
    {
        $this->typeNames[mb_strtolower($type)] = $translation;
    }

    public function addMoveName(int $moveId, string $translation): void
    {
        $this->moveNames[sprintf('%04d', $moveId)] = $translation;
    }

    public function getTypeName(string $typeName): ?string
    {
        return $this->typeNames[mb_strtolower($typeName)] ?? null;
    }

    public function getWeatherName(string $weatherName): ?string
    {
        return $this->weather[mb_strtolower($weatherName)] ?? null;
    }

    public function getMoveName(int $moveId): ?string
    {
        return $this->moveNames[sprintf('%04d', $moveId)] ?? null;
    }

    public function getPokemonName(int $dexNr): ?string
    {
        $dexNrKey = sprintf('%04d', $dexNr);

        return $this->pokemonNames[$dexNrKey] ?? null;
    }

    public function getItemName(string $itemName): ?string
    {
        return $this->items[mb_strtolower($itemName)] ?? null;
    }

    /**
     * @return string[]
     */
    public function getPokemonMegaNames(int $dexNr): array
    {
        $dexNrKey = sprintf('%04d', $dexNr);

        return array_values($this->pokemonMegaNames[$dexNrKey] ?? []);
    }

    public function getPokemonFormName(string $formName): ?string
    {
        return $this->pokemonFormNames[mb_strtoupper($formName)] ?? null;
    }

    public function addRegionalForm(string $regionalForm, string $translation): void
    {
        $this->regionalForms[$regionalForm] = $translation;
    }

    public function getRegionalForm(string $regionalForm): ?string
    {
        return $this->regionalForms[$regionalForm] ?? null;
    }

    public function addCustomTranslation(string $key, string $translation): void
    {
        $this->customTranslations[$key] = $translation;
    }

    public function getCustomTranslation(string $key): ?string
    {
        return $this->customTranslations[$key];
    }

    /**
     * @return array<string, string>
     */
    public function getQuests(): array
    {
        return $this->quests;
    }

    public function getQuest(string $quest, ?string $replaceArgument): ?string
    {
        $questName = $this->quests[mb_strtolower($quest)] ?? null;
        if ($replaceArgument !== null && $questName !== null) {
            return str_replace(
                '{0}',
                $replaceArgument,
                $questName
            );
        }

        return $questName;
    }
}
