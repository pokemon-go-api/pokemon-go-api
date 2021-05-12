<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Util;

use Exception;
use PokemonGoLingen\PogoAPI\Types\PokemonStats;

use function floor;
use function pow;

class CpCalculator
{
    // source https://gamepress.gg/pokemongo/cp-multiplier
    private const CPM_LEVEL_15 = 0.51739395;
    private const CPM_LEVEL_20 = 0.5974;
    private const CPM_LEVEL_25 = 0.667934;
    private const CMP_MAP      = [
        self::LEVEL_15 => self::CPM_LEVEL_15,
        self::LEVEL_20 => self::CPM_LEVEL_20,
        self::LEVEL_25 => self::CPM_LEVEL_25,
    ];

    public const LEVEL_15 = 15;
    public const LEVEL_20 = 20;
    public const LEVEL_25 = 25;

    public static function calculateMinCpForLevel(PokemonStats $stats, int $level): int
    {
        $baseAttack  = $stats->getAttack();
        $baseDefense = $stats->getDefense();
        $baseStamina = $stats->getStamina();
        $levelCPM    = self::CMP_MAP[$level] ?? null;
        if ($levelCPM === null) {
            throw new Exception('Invalid level given', 1620756788290);
        }

        return self::calculateCP($baseAttack + 10, $baseDefense + 10, $baseStamina + 10, $levelCPM);
    }

    public static function calculateMaxCpForLevel(PokemonStats $stats, int $level): int
    {
        $baseAttack  = $stats->getAttack();
        $baseDefense = $stats->getDefense();
        $baseStamina = $stats->getStamina();
        $levelCPM    = self::CMP_MAP[$level] ?? null;
        if ($levelCPM === null) {
            throw new Exception('Invalid level given', 1620756788290);
        }

        return self::calculateCP($baseAttack + 15, $baseDefense + 15, $baseStamina + 15, $levelCPM);
    }

    public static function calculateQuestMinCp(PokemonStats $stats): int
    {
        return self::calculateMinCpForLevel($stats, self::LEVEL_15);
    }

    public static function calculateQuestMaxCp(PokemonStats $stats): int
    {
        return self::calculateMaxCpForLevel($stats, self::LEVEL_15);
    }

    public static function calculateRaidMaxCp(PokemonStats $stats): int
    {
        return self::calculateMaxCpForLevel($stats, self::LEVEL_20);
    }

    public static function calculateRaidMinCp(PokemonStats $stats): int
    {
        return self::calculateMinCpForLevel($stats, self::LEVEL_20);
    }

    public static function calculateRaidWeatherBoostMaxCp(PokemonStats $stats): int
    {
        return self::calculateMaxCpForLevel($stats, self::LEVEL_25);
    }

    public static function calculateRaidWeatherBoostMinCp(PokemonStats $stats): int
    {
        return self::calculateMinCpForLevel($stats, self::LEVEL_25);
    }

    private static function calculateCP(int $attack, int $defense, int $stamina, float $levelCPM): int
    {
        return (int) floor($attack * pow($defense, 0.5) * pow($stamina, 0.5) * pow($levelCPM, 2) / 10);
    }
}
