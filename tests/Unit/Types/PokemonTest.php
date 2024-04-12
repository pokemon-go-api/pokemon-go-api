<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonStats;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\TemporaryEvolution;
use stdClass;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Pokemon::class)]
#[UsesClass(PokemonStats::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(TemporaryEvolution::class)]
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
        $this->assertInstanceOf(stdClass::class, $pokemonData);
        $pokemon = Pokemon::createFromGameMaster($pokemonData->data);

        $this->assertSame(105, $pokemon->getDexNr());
        $this->assertSame(0, $pokemon->getAssetsBundleId());
        $this->assertSame('MAROWAK', $pokemon->getId());
        $this->assertSame('MAROWAK_ALOLA', $pokemon->getFormId());
        $this->assertSame('POKEMON_TYPE_FIRE', $pokemon->getTypePrimary()->getGameMasterTypeName());
        $secondaryType = $pokemon->getTypeSecondary();
        $this->assertNotNull($secondaryType);
        $this->assertSame('POKEMON_TYPE_GHOST', $secondaryType->getGameMasterTypeName());
        $this->assertEquals(new PokemonStats(155, 144, 186), $pokemon->getStats());

        $this->assertCount(3, $pokemon->getQuickMoveNames());
        $this->assertCount(4, $pokemon->getCinematicMoveNames());
        $this->assertCount(0, $pokemon->getEliteQuickMoveNames());
        $this->assertCount(1, $pokemon->getEliteCinematicMoveNames());
    }

    public function testCreateFromGameMasterWithTemporaryEvolutions(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/V0006_POKEMON_CHARIZARD.json') ?: '{}';

        $pokemonData = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        $this->assertInstanceOf(stdClass::class, $pokemonData);
        $pokemon             = Pokemon::createFromGameMaster($pokemonData->data);
        $temporaryEvolutions = $pokemon->getTemporaryEvolutions();
        $this->assertTrue($pokemon->hasTemporaryEvolutions());
        $this->assertCount(2, $temporaryEvolutions);
        $this->assertSame('CHARIZARD_MEGA_X', $temporaryEvolutions[0]->getId());
        $this->assertSame('Fire', $temporaryEvolutions[0]->getTypePrimary()->getType());
        $this->assertSame('Dragon', $temporaryEvolutions[0]->getTypeSecondary()->getType());

        $this->assertSame('CHARIZARD_MEGA_Y', $temporaryEvolutions[1]->getId());
        $this->assertSame('Fire', $temporaryEvolutions[1]->getTypePrimary()->getType());
        $this->assertSame('Flying', $temporaryEvolutions[1]->getTypeSecondary()->getType());
    }
}
