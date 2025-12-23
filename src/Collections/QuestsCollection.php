<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\EvolutionQuest;

final class QuestsCollection
{
    /** @var array<string, EvolutionQuest> */
    private array $storage = [];

    public function add(EvolutionQuest $evolutionQuest): void
    {
        $this->storage[$evolutionQuest->getQuestId()] = $evolutionQuest;
    }

    public function getByName(string $questName): EvolutionQuest|null
    {
        return $this->storage[$questName] ?? null;
    }

    /** @return EvolutionQuest[] */
    public function toArray(): array
    {
        return $this->storage;
    }
}
