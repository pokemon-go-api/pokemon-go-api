<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

final class PokemonCombatMoveBuffs
{
    private int $activationChance;
    private ?int $attackerAttackStatStageChange;
    private ?int $attackerDefenseStatStageChange;
    private ?int $targetAttackStatStageChange;
    private ?int $targetDefenseStatStageChange;

    public function __construct(
        int $activationChance,
        ?int $attackerAttackStatStageChange,
        ?int $attackerDefenseStatStageChange,
        ?int $targetAttackStatStageChange,
        ?int $targetDefenseStatStageChange
    ) {
        $this->activationChance               = $activationChance;
        $this->attackerAttackStatStageChange  = $attackerAttackStatStageChange;
        $this->attackerDefenseStatStageChange = $attackerDefenseStatStageChange;
        $this->targetAttackStatStageChange    = $targetAttackStatStageChange;
        $this->targetDefenseStatStageChange   = $targetDefenseStatStageChange;
    }

    public function getActivationChance(): int
    {
        return $this->activationChance;
    }

    public function getAttackerAttackStatStageChange(): ?int
    {
        return $this->attackerAttackStatStageChange;
    }

    public function getAttackerDefenseStatStageChange(): ?int
    {
        return $this->attackerDefenseStatStageChange;
    }

    public function getTargetAttackStatStageChange(): ?int
    {
        return $this->targetAttackStatStageChange;
    }

    public function getTargetDefenseStatStageChange(): ?int
    {
        return $this->targetDefenseStatStageChange;
    }
}
