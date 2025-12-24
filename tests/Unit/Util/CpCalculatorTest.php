<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonStats;
use PokemonGoApi\PogoAPI\Util\CpCalculator;

#[CoversClass(CpCalculator::class)]
#[UsesClass(PokemonStats::class)]
final class CpCalculatorTest extends TestCase
{
    public function testCalculateRaidMaxLevelCp(): void
    {
        // Example for Kyurem
        $stats = new PokemonStats(245, 246, 170);
        $this->assertSame(1957, CpCalculator::calculateRaidMinCp($stats));
        $this->assertSame(2042, CpCalculator::calculateRaidMaxCp($stats));
        $this->assertSame(2446, CpCalculator::calculateRaidWeatherBoostMinCp($stats));
        $this->assertSame(2553, CpCalculator::calculateRaidWeatherBoostMaxCp($stats));

        // Example for Gengar
        $stats = new PokemonStats(155, 261, 149);
        $this->assertSame(1566, CpCalculator::calculateRaidMinCp($stats));
        $this->assertSame(1644, CpCalculator::calculateRaidMaxCp($stats));
        $this->assertSame(1958, CpCalculator::calculateRaidWeatherBoostMinCp($stats));
        $this->assertSame(2055, CpCalculator::calculateRaidWeatherBoostMaxCp($stats));
    }
}
