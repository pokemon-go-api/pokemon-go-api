<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use PokemonGoApi\PogoAPI\Collections\TranslationCollection;

use function fclose;
use function feof;
use function fgets;
use function file_exists;
use function fopen;
use function strlen;
use function strpos;
use function strtoupper;
use function substr;
use function trim;

class TranslationParser
{
    public const ENGLISH  = 'English';
    public const GERMAN   = 'German';
    public const FRENCH   = 'French';
    public const ITALIAN  = 'Italian';
    public const JAPANESE = 'Japanese';
    public const KOREAN   = 'Korean';
    public const SPANISH  = 'Spanish';

    public const LANGUAGES = [
        self::ENGLISH,
        self::GERMAN,
        self::FRENCH,
        self::ITALIAN,
        self::JAPANESE,
        self::KOREAN,
        self::SPANISH,
    ];

    /**
     * @param array<string, string> $customTranslations
     */
    public function loadLanguage(
        string $language,
        string $apkFile,
        string $remoteFile,
        array $customTranslations
    ): TranslationCollection {
        $collection = new TranslationCollection($language);
        $files      = [$apkFile, $remoteFile];

        foreach ($files as $fileName) {
            if (! file_exists($fileName)) {
                continue;
            }

            $file = fopen($fileName, 'r+');
            if ($file === false) {
                continue;
            }

            do {
                $currentLine = trim(fgets($file) ?: '');

                if (empty($currentLine)) {
                    continue;
                }

                $nextLine = trim(fgets($file) ?: '');
                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: pokemon_name_')) {
                    $dexId              = (int) substr($currentLine, 26, 4);
                    $megaEvolutionIndex = substr($currentLine, 30) ?: '';

                    $translation = $this->readTranslation($nextLine);
                    if (empty($megaEvolutionIndex)) {
                        $collection->addPokemonName($dexId, $translation);
                    } else {
                        $collection->addPokemonMegaName($dexId, $megaEvolutionIndex, $translation);
                    }
                }

                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: form_')) {
                    $type        = strtoupper(trim(substr($currentLine, 18)));
                    $translation = $this->readTranslation($nextLine);
                    $collection->addPokemonFormName($type, $translation);
                }

                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: pokemon_type_')) {
                    $type        = strtoupper(trim(substr($currentLine, 26)));
                    $translation = $this->readTranslation($nextLine);
                    $collection->addTypeName($type, $translation);
                }

                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: move_name_')) {
                    $moveId      = (int) trim(substr($currentLine, 23));
                    $translation = $this->readTranslation($nextLine);
                    $collection->addMoveName($moveId, $translation);
                }

                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: quest_')) {
                    $questKey    = trim(substr($currentLine, 13));
                    $translation = $this->readTranslation($nextLine);
                    $collection->addQuest($questKey, $translation);
                }

                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: weather_')) {
                    $weatherKey  = trim(substr($currentLine, 21));
                    $translation = $this->readTranslation($nextLine);
                    $collection->addWeather($weatherKey, $translation);
                }

                if (
                    $this->lineStartsWith($currentLine, 'RESOURCE ID: item_')
                    && $this->lineEndsWith($currentLine, '_name')
                ) {
                    $itemKey     = trim(substr($currentLine, 13, -5));
                    $translation = $this->readTranslation($nextLine);
                    $collection->addItem($itemKey, $translation);
                }

                if (! $this->lineStartsWith($currentLine, 'RESOURCE ID: challenge_')) {
                    continue;
                }

                $questKey    = trim(substr($currentLine, 13));
                $translation = $this->readTranslation($nextLine);
                $collection->addQuest($questKey, $translation);
            } while (! feof($file));

            fclose($file);
        }

        foreach ($customTranslations as $customTranslation => $translation) {
            if (strpos($customTranslation, CustomTranslations::REGIONAL_PREFIX) !== false) {
                $regionalForm = substr($customTranslation, 14);
                $collection->addRegionalForm($regionalForm, $translation);
                continue;
            }

            $collection->addCustomTranslation($customTranslation, $translation);
        }

        return $collection;
    }

    private function lineStartsWith(string $line, string $startsWith): bool
    {
        return strpos($line, $startsWith) === 0;
    }

    private function lineEndsWith(string $line, string $endsWith): bool
    {
        return substr($line, strlen($endsWith) * -1) === $endsWith;
    }

    private function readTranslation(string $line): string
    {
        return trim(substr($line, 6));
    }
}
