<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\IO;

use RuntimeException;

use function is_dir;
use function mkdir;
use function sprintf;

final class Directory
{
    public static function create(string $path): void
    {
        if (! is_dir($path) && ! mkdir($path, 0777, true) && ! is_dir($path)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
    }
}
