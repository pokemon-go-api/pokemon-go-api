<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonStats;

#[CoversClass(PokemonStats::class)]
class PokemonStatsTest extends TestCase
{
    public function testGetters(): void
    {
        $sut = new PokemonStats(10, 20, 30);
        $this->assertSame(10, $sut->getStamina());
        $this->assertSame(20, $sut->getAttack());
        $this->assertSame(30, $sut->getDefense());
    }

    public function testJsonSerialize(): void
    {
        $sut = new PokemonStats(10, 20, 30);
        $this->assertSame([
            'stamina' => 10,
            'attack'  => 20,
            'defense' => 30,
        ], $sut->jsonSerialize());
    }
}
