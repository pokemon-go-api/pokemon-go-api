<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

final readonly class Item
{
    public function __construct(
        public string $templateId,
        public int|string $id,
    ) {
    }
}
