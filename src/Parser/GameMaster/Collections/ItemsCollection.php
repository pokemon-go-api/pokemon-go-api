<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Collections;

use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Item;

final class ItemsCollection
{
    /** @var array<string, Item> */
    private array $storage = [];

    public function add(Item $item): void
    {
        $this->storage[$item->id] = $item;
    }

    public function getByItemId(string $itemId): Item|null
    {
        return $this->storage[$itemId] ?? null;
    }
}
