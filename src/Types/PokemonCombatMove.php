<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use stdClass;

final class PokemonCombatMove
{
    public function __construct(
        private float $power,
        private float $energy,
        private int $durationTurns,
        private PokemonCombatMoveBuffs|null $buffs,
    ) {
    }

    public static function createFromGameMaster(stdClass $combatMoveData): self
    {
        $moveBuffs = null;
        if (isset($combatMoveData->combatMove->buffs)) {
            $moveBuffs = new PokemonCombatMoveBuffs(
                (int) ($combatMoveData->combatMove->buffs->buffActivationChance * 100),
                $combatMoveData->combatMove->buffs->attackerAttackStatStageChange ?? null,
                $combatMoveData->combatMove->buffs->attackerDefenseStatStageChange ?? null,
                $combatMoveData->combatMove->buffs->targetAttackStatStageChange ?? null,
                $combatMoveData->combatMove->buffs->targetDefenseStatStageChange ?? null,
            );
        }

        return new self(
            $combatMoveData->combatMove->power ?? 0,
            $combatMoveData->combatMove->energyDelta ?? 0,
            $combatMoveData->combatMove->durationTurns ?? 0,
            $moveBuffs,
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

    public function getDurationTurns(): int
    {
        return $this->durationTurns;
    }

    public function getBuffs(): PokemonCombatMoveBuffs|null
    {
        return $this->buffs;
    }
}
