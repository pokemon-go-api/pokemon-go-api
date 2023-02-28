<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use PokemonGoApi\PogoAPI\Collections\PokemonAssetsCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;

use function file_get_contents;
use function is_file;

class PokemonGoImagesParser
{
    public function parseFile(string $gitSubtreeFilePath): PokemonAssetsCollection
    {
        if (! is_file($gitSubtreeFilePath)) {
            return new PokemonAssetsCollection();
        }

        $fileContent = file_get_contents($gitSubtreeFilePath) ?: '[]';
        /** @var list<string> $images */
        $images = JsonParser::decodeToArray($fileContent);

        return new PokemonAssetsCollection(...$images);
    }
}
