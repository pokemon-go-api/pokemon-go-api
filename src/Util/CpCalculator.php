<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Util;

use PokemonGoLingen\PogoAPI\Types\PokemonStats;

use function floor;
use function pow;

class CpCalculator
{
    private const CPM_LEVEL_20 = 0.5974;
    private const CPM_LEVEL_25 = 0.667934;

    public static function calculateRaidMaxCp(PokemonStats $stats): int
    {
        $baseAttack  = $stats->getAttack();
        $baseDefense = $stats->getDefense();
        $baseStamina = $stats->getStamina();
        $levelCPM    = self::CPM_LEVEL_20;

        return self::calculateCP($baseAttack + 15, $baseDefense + 15, $baseStamina + 15, $levelCPM);
    }

    public static function calculateRaidMinCp(PokemonStats $stats): int
    {
        $baseAttack  = $stats->getAttack();
        $baseDefense = $stats->getDefense();
        $baseStamina = $stats->getStamina();
        $levelCPM    = self::CPM_LEVEL_20;

        return self::calculateCP($baseAttack + 10, $baseDefense + 10, $baseStamina + 10, $levelCPM);
    }

    public static function calculateRaidWeatherBoostMaxCp(PokemonStats $stats): int
    {
        $baseAttack  = $stats->getAttack();
        $baseDefense = $stats->getDefense();
        $baseStamina = $stats->getStamina();
        $levelCPM    = self::CPM_LEVEL_25;

        return self::calculateCP($baseAttack + 15, $baseDefense + 15, $baseStamina + 15, $levelCPM);
    }

    public static function calculateRaidWeatherBoostMinCp(PokemonStats $stats): int
    {
        $baseAttack  = $stats->getAttack();
        $baseDefense = $stats->getDefense();
        $baseStamina = $stats->getStamina();
        $levelCPM    = self::CPM_LEVEL_25;

        return self::calculateCP($baseAttack + 10, $baseDefense + 10, $baseStamina + 10, $levelCPM);
    }

    private static function calculateCP(int $attack, int $defense, int $stamina, float $levelCPM): int
    {
        return (int) floor($attack * pow($defense, 0.5) * pow($stamina, 0.5) * pow($levelCPM, 2) / 10);
    }
}
