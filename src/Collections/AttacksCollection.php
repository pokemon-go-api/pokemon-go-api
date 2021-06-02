<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use PokemonGoApi\PogoAPI\Types\PokemonMove;

final class AttacksCollection
{
    /** @var array<string, PokemonMove> */
    private array $storage = [];
    /** @var array<string, PokemonMove> */
    private array $indexedByName = [];

    public function add(PokemonMove $pokemonMove): void
    {
        $this->storage['m' . $pokemonMove->getId()]   = $pokemonMove;
        $this->indexedByName[$pokemonMove->getName()] = &$this->storage['m' . $pokemonMove->getId()];
    }

    public function getById(int $moveId): ?PokemonMove
    {
        return $this->storage['m' . $moveId] ?? null;
    }

    public function getByName(string $moveName): ?PokemonMove
    {
        return $this->indexedByName[$moveName] ?? null;
    }

    /** @return PokemonMove[] */
    public function toArray(): array
    {
        return $this->storage;
    }
}
