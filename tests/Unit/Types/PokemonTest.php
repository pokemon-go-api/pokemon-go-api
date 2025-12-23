<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonStats;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\TemporaryEvolution;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;

use function file_get_contents;

#[CoversClass(Pokemon::class)]
#[UsesClass(PokemonStats::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(TemporaryEvolution::class)]
final class PokemonTest extends TestCase
{
    public function testCreateFromGameMasterWillFail(): void
    {
        $this->expectExceptionCode(1608127311711);
        Pokemon::createFromGameMaster((object) []);
    }

    public function testCreateFromGameMaster(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/V0105_POKEMON_MAROWAK_ALOLA.json') ?: '{}';

        $pokemonData = JsonParser::decodeGameMasterFileData($gameMaster);
        $pokemon     = Pokemon::createFromGameMaster($pokemonData);

        $this->assertSame(105, $pokemon->getDexNr());
        $this->assertSame(0, $pokemon->getAssetsBundleId());
        $this->assertSame('MAROWAK', $pokemon->getId());
        $this->assertSame('MAROWAK_ALOLA', $pokemon->getFormId());
        $this->assertSame('POKEMON_TYPE_FIRE', $pokemon->getTypePrimary()->getGameMasterTypeName());
        $secondaryType = $pokemon->getTypeSecondary();
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

        $pokemonData         = JsonParser::decodeGameMasterFileData($gameMaster);
        $pokemon             = Pokemon::createFromGameMaster($pokemonData);
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
