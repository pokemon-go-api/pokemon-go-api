<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Util;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Types\PokemonStats;
use PokemonGoLingen\PogoAPI\Util\CpCalculator;

/**
 * @uses \PokemonGoLingen\PogoAPI\Types\PokemonStats
 *
 * @covers \PokemonGoLingen\PogoAPI\Util\CpCalculator
 */
class CpCalculatorTest extends TestCase
{
    public function testCalculateRaidMaxLevelCp(): void
    {
        // Example for Kyurem
        $stats = new PokemonStats(245, 246, 170);
        self::assertSame(1957, CpCalculator::calculateRaidMinCp($stats));
        self::assertSame(2042, CpCalculator::calculateRaidMaxCp($stats));
        self::assertSame(2446, CpCalculator::calculateRaidWeatherBoostMinCp($stats));
        self::assertSame(2553, CpCalculator::calculateRaidWeatherBoostMaxCp($stats));

        // Example for Gengar
        $stats = new PokemonStats(155, 261, 149);
        self::assertSame(1566, CpCalculator::calculateRaidMinCp($stats));
        self::assertSame(1644, CpCalculator::calculateRaidMaxCp($stats));
        self::assertSame(1958, CpCalculator::calculateRaidWeatherBoostMinCp($stats));
        self::assertSame(2055, CpCalculator::calculateRaidWeatherBoostMaxCp($stats));
    }
}
