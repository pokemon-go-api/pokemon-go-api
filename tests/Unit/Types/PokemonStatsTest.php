<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonStats;

/** @covers \PokemonGoApi\PogoAPI\Types\PokemonStats */
class PokemonStatsTest extends TestCase
{
    public function testGetters(): void
    {
        $sut = new PokemonStats(10, 20, 30);
        self::assertSame(10, $sut->getStamina());
        self::assertSame(20, $sut->getAttack());
        self::assertSame(30, $sut->getDefense());
    }

    public function testJsonSerialize(): void
    {
        $sut = new PokemonStats(10, 20, 30);
        self::assertSame([
            'stamina' => 10,
            'attack'  => 20,
            'defense' => 30,
        ], $sut->jsonSerialize());
    }
}
