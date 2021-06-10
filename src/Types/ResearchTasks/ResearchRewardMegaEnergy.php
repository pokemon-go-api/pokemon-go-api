<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchRewardMegaEnergy implements ResearchReward
{
    private string $pokemonFormId;
    private int $megaEnergy;

    public function __construct(
        string $pokemonFormId,
        int $megaEnergy
    ) {
        $this->pokemonFormId = $pokemonFormId;
        $this->megaEnergy = $megaEnergy;
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
