<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

final class TemporaryEvolutionCamera
{
    /** @param array{cylinderRadiusM?: float} $camera */
    public function __construct(
        array $camera,
    ) {
    }
}
