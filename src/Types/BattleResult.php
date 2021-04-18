<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

final class BattleResult
{
    private BattleConfiguration $battleConfiguration;
    private float $totalEstimator;

    public function __construct(BattleConfiguration $battleConfiguration, float $totalEstimator)
    {
        $this->totalEstimator      = $totalEstimator;
        $this->battleConfiguration = $battleConfiguration;
    }

    public function getBattleConfiguration(): BattleConfiguration
    {
        return $this->battleConfiguration;
    }

    public function getTotalEstimator(): float
    {
        return $this->totalEstimator;
    }
}
