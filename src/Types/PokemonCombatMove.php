<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

use stdClass;

final class PokemonCombatMove
{
    private float $power;
    private float $energy;

    public function __construct(float $power, float $energy)
    {
        $this->power  = $power;
        $this->energy = $energy;
    }

    public static function createFromGameMaster(stdClass $combatMoveData): self
    {
        return new self(
            $combatMoveData->combatMove->power,
            $combatMoveData->combatMove->energyDelta
        );
    }

    public function getPower(): float
    {
        return $this->power;
    }

    public function getEnergy(): float
    {
        return $this->energy;
    }
}
