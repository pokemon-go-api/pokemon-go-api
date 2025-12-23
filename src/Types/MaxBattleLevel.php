<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

enum MaxBattleLevel: int
{
    case LEVEL_1 = 1;
    case LEVEL_2 = 2;
    case LEVEL_3 = 3;
    case LEVEL_4 = 4;
    case LEVEL_5 = 5;
    case LEVEL_6 = 6;
}
