<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Util\GenerationDeterminer;

#[CoversClass(GenerationDeterminer::class)]
class GenerationDeterminerTest extends TestCase
{
    #[DataProvider('dexNrDataProvider')]
    public function testFromDexNr(int $input, int $expected): void
    {
        $this->assertSame($expected, GenerationDeterminer::fromDexNr($input));
    }

    /** @return iterable<int, array<int, int>> */
    public static function dexNrDataProvider(): iterable
    {
        yield [0, 1];
        yield [1, 1];
        yield [151, 1];
        yield [152, 2];
        yield [251, 2];
        yield [252, 3];
        yield [386, 3];
        yield [387, 4];
        yield [493, 4];
        yield [494, 5];
        yield [649, 5];
        yield [650, 6];
        yield [721, 6];
        yield [722, 7];
        yield [809, 7];
        yield [810, 8];
        yield [898, 8];
        yield [899, 9];
        yield [1000, 9];
    }
}
