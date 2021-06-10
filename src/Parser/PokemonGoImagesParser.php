<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use PokemonGoApi\PogoAPI\Collections\PokemonAssetsCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use stdClass;

use function array_map;
use function file_get_contents;
use function is_file;

class PokemonGoImagesParser
{
    public function parseFile(string $gitSubtreeFilePath): PokemonAssetsCollection
    {
        if (! is_file($gitSubtreeFilePath)) {
            return new PokemonAssetsCollection();
        }

        $fileContent = file_get_contents($gitSubtreeFilePath);
        $content     = JsonParser::decodeToObject($fileContent ?: '{}');
        $images      = array_map(
            static fn (stdClass $meta): string => $meta->path,
            $content->tree
        );

        return new PokemonAssetsCollection(...$images);
    }
}
