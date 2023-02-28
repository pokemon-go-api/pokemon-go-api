<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchRewardMegaEnergy implements ResearchReward
{
    public function __construct(
        private string $pokemonFormId,
        private int $megaEnergy,
    ) {
    }

    public function getPokemonFormId(): string
    {
        return $this->pokemonFormId;
    }

    public function getMegaEnergy(): int
    {
        return $this->megaEnergy;
    }
}
