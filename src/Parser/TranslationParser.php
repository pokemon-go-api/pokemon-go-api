<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Parser;

use PokemonGoLingen\PogoAPI\Collections\TranslationCollection;

use function fclose;
use function feof;
use function fgets;
use function fopen;
use function strpos;
use function strtoupper;
use function substr;
use function trim;

class TranslationParser
{
    public const LANGUAGES = [
        'English',
        'German',
        'French',
        'Italian',
        'Japanese',
        'Korean',
        'Spanish',
    ];

    private string $basedir;

    public function __construct(string $basedir)
    {
        $this->basedir = $basedir;
    }

    public function loadLanguage(string $language): TranslationCollection
    {
        $collection = new TranslationCollection($language);
        $files      = [
            $this->basedir . '/latest_apk_' . $language . '.txt',
            $this->basedir . '/latest_remote_' . $language . '.txt',
        ];

        foreach ($files as $fileName) {
            $file = fopen($fileName, 'r+');
            if ($file === false) {
                continue;
            }

            do {
                $currentLine = fgets($file) ?: '';

                if (empty(trim($currentLine))) {
                    continue;
                }

                $nextLine = fgets($file) ?: '';
                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: pokemon_name_')) {
                    $dexId       = (int) substr($currentLine, 26, 4);
                    $translation = $this->readTranslation($nextLine);
                    $collection->addPokemonName($dexId, $translation);
                }

                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: form_')) {
                    $type        = strtoupper(trim(substr($currentLine, 18)));
                    $translation = $this->readTranslation($nextLine);
                    $collection->addPokemonFormName($type, $translation);
                }

                if ($this->lineStartsWith($currentLine, 'RESOURCE ID: pokemon_type_')) {
                    $type        = strtoupper(trim(substr($currentLine, 13)));
                    $translation = $this->readTranslation($nextLine);
                    $collection->addTypeName($type, $translation);
                }

                if (! $this->lineStartsWith($currentLine, 'RESOURCE ID: move_name_')) {
                    continue;
                }

                $type        = trim(substr($currentLine, 23));
                $translation = $this->readTranslation($nextLine);
                $collection->addMoveName($type, $translation);
            } while (! feof($file));

            fclose($file);
        }

        return $collection;
    }

    private function lineStartsWith(string $line, string $startsWith): bool
    {
        return strpos($line, $startsWith) === 0;
    }

    private function readTranslation(string $line): string
    {
        return trim(substr($line, 6));
    }
}
