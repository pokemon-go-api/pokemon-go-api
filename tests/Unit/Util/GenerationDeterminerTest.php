<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Util;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Util\GenerationDeterminer;

/** @covers \PokemonGoApi\PogoAPI\Util\GenerationDeterminer */
class GenerationDeterminerTest extends TestCase
{
    /** @dataProvider dexNrDataProvider */
    public function testFromDexNr(int $input, int $expected): void
    {
        self::assertSame($expected, GenerationDeterminer::fromDexNr($input));
    }

    /** @return array<int, array<int, int>> */
    public function dexNrDataProvider(): array
    {
        return [
            [0, 1],
            [1, 1],
            [151, 1],
            [152, 2],
            [251, 2],
            [252, 3],
            [386, 3],
            [387, 4],
            [493, 4],
            [494, 5],
            [649, 5],
            [650, 6],
            [721, 6],
            [722, 7],
            [809, 7],
            [810, 8],
            [898, 8],
            [899, 9],
            [1000, 9],
        ];
    }
}
