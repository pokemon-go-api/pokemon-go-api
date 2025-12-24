<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

final class TemporaryEvolutionBranch
{
    public readonly string $tempEvoId;

    public readonly int|null $energyCost;

    public function __construct(
        string $temporaryEvolution,
        int|null $temporaryEvolutionEnergyCost,
    ) {
        $this->tempEvoId  = $temporaryEvolution;
        $this->energyCost = $temporaryEvolutionEnergyCost;
    }
}
