<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

final class PokemonCombatMoveBuffs
{
    public function __construct(
        private int $activationChance,
        private int|null $attackerAttackStatStageChange,
        private int|null $attackerDefenseStatStageChange,
        private int|null $targetAttackStatStageChange,
        private int|null $targetDefenseStatStageChange,
    ) {
    }

    public function getActivationChance(): int
    {
        return $this->activationChance;
    }

    public function getAttackerAttackStatStageChange(): int|null
    {
        return $this->attackerAttackStatStageChange;
    }

    public function getAttackerDefenseStatStageChange(): int|null
    {
        return $this->attackerDefenseStatStageChange;
    }

    public function getTargetAttackStatStageChange(): int|null
    {
        return $this->targetAttackStatStageChange;
    }

    public function getTargetDefenseStatStageChange(): int|null
    {
        return $this->targetDefenseStatStageChange;
    }
}
