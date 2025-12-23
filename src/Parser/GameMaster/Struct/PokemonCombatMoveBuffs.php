<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

final readonly class PokemonCombatMoveBuffs
{
    private int $activationChance;

    public function __construct(
        float|null $buffActivationChance,
        private int|null $attackerAttackStatStageChange,
        private int|null $attackerDefenseStatStageChange,
        private int|null $targetAttackStatStageChange,
        private int|null $targetDefenseStatStageChange,
    ) {
        if ($buffActivationChance === null) {
            $buffActivationChance = 0;
        }

        $this->activationChance = (int) ($buffActivationChance * 100);
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
