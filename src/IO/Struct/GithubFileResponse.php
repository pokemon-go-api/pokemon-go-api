<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\IO\Struct;

class GithubFileResponse
{
    public function __construct(
        public readonly string $name,
        public readonly string $path,
        public readonly string $sha,
        public readonly string $downloadUrl,
    ) {
    }
}
