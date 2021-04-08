<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Parser;

use PokemonGoLingen\PogoAPI\Collections\PokemonAssetsCollection;
use stdClass;

use function array_map;
use function file_get_contents;
use function is_file;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class PokemonGoImagesParser
{
    public function parseFile(string $gitSubtreeFilePath): PokemonAssetsCollection
    {
        if (! is_file($gitSubtreeFilePath)) {
            return new PokemonAssetsCollection();
        }

        $fileContent = file_get_contents($gitSubtreeFilePath);
        $content     = json_decode($fileContent ?: 'false', false, 512, JSON_THROW_ON_ERROR);
        if (! $content instanceof stdClass) {
            return new PokemonAssetsCollection();
        }

        $images = array_map(
            static fn (stdClass $meta): string => $meta->path,
            $content->tree
        );

        return new PokemonAssetsCollection(...$images);
    }
}
