<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\IO;

use Exception;

use function file_get_contents;

final class RemoteFileLoader
{
    public function load(string $url): File
    {
        $content = file_get_contents($url);

        if ($content === false) {
            throw new Exception('Cant download given file');
        }

        return new File($content);
    }
}
