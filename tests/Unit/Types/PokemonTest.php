<?php

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Types;

use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Types\PokemonStats;

/**
 * @covers \PokemonGoLingen\PogoAPI\Types\Pokemon
 * @covers \PokemonGoLingen\PogoAPI\Types\PokemonStats
 */
class PokemonTest extends TestCase
{

    public function testCreateFromGameMasterWillFail(): void
    {
        self::expectExceptionCode(1608127311711);
        Pokemon::createFromGameMaster((object)[]);
    }

    public function testCreateFromGameMaster(): void
    {
        $gameMaster  = file_get_contents(__DIR__ . '/Fixtures/V0105_POKEMON_MAROWAK_ALOLA.json');
        $pokemonData = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        $pokemon     = Pokemon::createFromGameMaster($pokemonData->data);

        self::assertSame(105, $pokemon->getDexNr());
        self::assertSame('MAROWAK', $pokemon->getId());
        self::assertSame('MAROWAK_ALOLA', $pokemon->getFormId());
        self::assertSame('POKEMON_TYPE_FIRE', $pokemon->getTypePrimary()->getTypeName());
        self::assertSame('POKEMON_TYPE_GHOST', $pokemon->getTypeSecondary()->getTypeName());
        self::assertEquals(
            new PokemonStats(155, 144, 186),
            $pokemon->getStats()
        );

        self::assertCount(3, $pokemon->getQuickMoveNames());
        self::assertCount(4, $pokemon->getCinematicMoveNames());
        self::assertCount(0, $pokemon->getEliteQuickMoveNames());
        self::assertCount(1, $pokemon->getEliteCinematicMoveNames());
    }
}
