<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchReward
{
    private string $pokemonFormId;
    private bool $shiny;

    public function __construct(
        string $pokemonFormId,
        bool $shiny
    ) {
        $this->pokemonFormId = $pokemonFormId;
        $this->shiny         = $shiny;
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
