<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PokemonGoLingen\PogoAPI\Types\PokemonStats;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @uses \PokemonGoLingen\PogoAPI\Types\PokemonStats
 * @uses \PokemonGoLingen\PogoAPI\Types\PokemonType
 * @uses \PokemonGoLingen\PogoAPI\Types\TemporaryEvolution
 *
 * @covers \PokemonGoLingen\PogoAPI\Types\Pokemon
 */
class PokemonTest extends TestCase
{
    public function testCreateFromGameMasterWillFail(): void
    {
        self::expectExceptionCode(1608127311711);
        Pokemon::createFromGameMaster((object) []);
    }

    public function testCreateFromGameMaster(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/V0105_POKEMON_MAROWAK_ALOLA.json') ?: '{}';

        $pokemonData = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        $pokemon     = Pokemon::createFromGameMaster($pokemonData->data);

        self::assertSame(105, $pokemon->getDexNr());
        self::assertSame(0, $pokemon->getAssetsBundleId());
        self::assertSame('MAROWAK', $pokemon->getId());
        self::assertSame('MAROWAK_ALOLA', $pokemon->getFormId());
        self::assertSame('POKEMON_TYPE_FIRE', $pokemon->getTypePrimary()->getGameMasterTypeName());
        $secondaryType = $pokemon->getTypeSecondary();
        self::assertNotNull($secondaryType);
        self::assertSame('POKEMON_TYPE_GHOST', $secondaryType->getGameMasterTypeName());
        self::assertEquals(
            new PokemonStats(155, 144, 186),
            $pokemon->getStats()
        );

        self::assertCount(3, $pokemon->getQuickMoveNames());
        self::assertCount(4, $pokemon->getCinematicMoveNames());
        self::assertCount(0, $pokemon->getEliteQuickMoveNames());
        self::assertCount(1, $pokemon->getEliteCinematicMoveNames());
    }

    public function testCreateFromGameMasterWithTemporaryEvolutions(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/V0006_POKEMON_CHARIZARD.json') ?: '{}';

        $pokemonData         = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        $pokemon             = Pokemon::createFromGameMaster($pokemonData->data);
        $temporaryEvolutions = $pokemon->getTemporaryEvolutions();
        self::assertTrue($pokemon->hasTemporaryEvolutions());
        self::assertCount(2, $temporaryEvolutions);
        self::assertSame('CHARIZARD_MEGA_X', $temporaryEvolutions[0]->getId());
        self::assertSame('Fire', $temporaryEvolutions[0]->getTypePrimary()->getType());
        self::assertSame('Dragon', $temporaryEvolutions[0]->getTypeSecondary()->getType());

        self::assertSame('CHARIZARD_MEGA_Y', $temporaryEvolutions[1]->getId());
        self::assertSame('Fire', $temporaryEvolutions[1]->getTypePrimary()->getType());
        self::assertSame('Flying', $temporaryEvolutions[1]->getTypeSecondary()->getType());
    }
}
