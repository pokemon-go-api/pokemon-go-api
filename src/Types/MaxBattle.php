<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Pokemon;

final class MaxBattle
{
    public function __construct(
        public readonly Pokemon $pokemon,
        public readonly bool $shinyAvailable,
        public readonly MaxBattleLevel $maxBattleLevel,
    ) {
    }
}
