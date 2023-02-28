<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchRewardPokemon implements ResearchReward
{
    public function __construct(
        private string $pokemonFormId,
        private bool $shiny,
    ) {
    }

    public function getPokemonFormId(): string
    {
        return $this->pokemonFormId;
    }

    public function isShiny(): bool
    {
        return $this->shiny;
    }
}
