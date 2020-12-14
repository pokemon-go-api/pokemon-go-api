<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Collections;

use PokemonGoLingen\PogoAPI\Types\Pokemon;

use function array_key_exists;

final class PokemonCollection
{
    /** @var array<string, Pokemon> */
    private array $storage = [];
    /** @var array<int, Pokemon> */
    private array $indexedByDexId = [];

    public function add(Pokemon $pokemon): void
    {
        if ($this->has($pokemon)) {
            return;
        }

        $this->storage[$pokemon->getId()]           = $pokemon;
        $this->indexedByDexId[$pokemon->getDexNr()] = &$this->storage[$pokemon->getId()];
    }

    public function get(string $id): ?Pokemon
    {
        if (! array_key_exists($id, $this->storage)) {
            return null;
        }

        return $this->storage[$id];
    }

    public function getByDexId(int $dexEntry): ?Pokemon
    {
        if (! array_key_exists($dexEntry, $this->indexedByDexId)) {
            return null;
        }

        return $this->indexedByDexId[$dexEntry];
    }

    public function has(Pokemon $pokemon): bool
    {
        return array_key_exists($pokemon->getId(), $this->storage);
    }

    /** @return Pokemon[] */
    public function toArray(): array
    {
        return $this->storage;
    }
}
