<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

final readonly class BattleResult
{
    public function __construct(private BattleConfiguration $battleConfiguration, private float $totalEstimator)
    {
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
