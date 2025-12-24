<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

class PokeBattlerResult
{
    public float $estimator;

    /** @param non-empty-list<array{randomMove: array{total: array{estimator: float}}}> $attackers */
    public function __construct(
        array $attackers,
    ) {
        $this->estimator = $attackers[0]['randomMove']['total']['estimator'];
    }
}
