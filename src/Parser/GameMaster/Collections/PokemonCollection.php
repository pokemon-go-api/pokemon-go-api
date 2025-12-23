<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Collections;

use PokemonGoApi\PogoAPI\Types\Pokemon;

use function array_key_exists;

class PokemonCollection
{
    /** @var array<string, Pokemon> */
    private array $storage = [];

    /** @var array<int, Pokemon> */
    private array $indexedByDexId = [];

    /** @var array<string, Pokemon> */
    private array $indexedByForm = [];

    public function add(Pokemon $pokemon): void
    {
        $this->storage[$pokemon->getId()]           = $pokemon;
        $this->indexedByDexId[$pokemon->getDexNr()] = &$this->storage[$pokemon->getId()];
        foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
            $this->indexedByForm[$regionForm->getFormId()] = &$this->storage[$pokemon->getId()];
        }
    }

    public function get(string $id): Pokemon|null
    {
        if (! array_key_exists($id, $this->storage)) {
            return null;
        }

        return $this->storage[$id];
    }

    public function getByDexId(int $dexEntry): Pokemon|null
    {
        if (! array_key_exists($dexEntry, $this->indexedByDexId)) {
            return null;
        }

        return $this->indexedByDexId[$dexEntry];
    }

    public function getByFormId(string $formId): Pokemon|null
    {
        if (! array_key_exists($formId, $this->indexedByForm)) {
            return $this->get($formId);
        }

        $pokemon = $this->indexedByForm[$formId];

        return $pokemon->getPokemonRegionForms()[$formId] ?? null;
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
