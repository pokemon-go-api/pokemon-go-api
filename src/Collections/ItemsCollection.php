<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use PokemonGoApi\PogoAPI\Types\Item;

final class ItemsCollection
{
    /** @var array<string, Item> */
    private array $storage = [];

    public function add(Item $item): void
    {
        $this->storage[(string) $item->id] = $item;
    }

    public function getByItemId(string|int $itemId): Item|null
    {
        return $this->storage[(string) $itemId] ?? null;
    }
}
