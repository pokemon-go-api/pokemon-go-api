<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonMove;
use PokemonGoApi\PogoAPI\Types\PokemonType;

use function file_get_contents;

use const PHP_FLOAT_EPSILON;

#[CoversClass(PokemonMove::class)]
#[UsesClass(PokemonType::class)]
final class PokemonMoveTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster  = file_get_contents(__DIR__ . '/Fixtures/V0021_MOVE_FLAME_WHEEL.json') ?: '{}';
        $pokemonData = JsonParser::decodeGameMasterFileData($gameMaster);

        $move = PokemonMove::createFromGameMaster($pokemonData);
        $this->assertSame(21, $move->getId());
        $this->assertSame('FLAME_WHEEL', $move->getName());
        $this->assertSame('POKEMON_TYPE_FIRE', $move->getPokemonType()->getGameMasterTypeName());
        $this->assertEqualsWithDelta(2700.0, $move->getDurationMs(), PHP_FLOAT_EPSILON);
        $this->assertFalse($move->isFastMove());
        $this->assertSame(-50.0, $move->getEnergy());
        $this->assertEqualsWithDelta(60.0, $move->getPower(), PHP_FLOAT_EPSILON);
    }

    public function testCreateFastMoveFromGameMaster(): void
    {
        $gameMaster  = file_get_contents(__DIR__ . '/Fixtures/V0253_MOVE_DRAGON_TAIL_FAST.json') ?: '{}';
        $pokemonData = JsonParser::decodeGameMasterFileData($gameMaster);

        $move = PokemonMove::createFromGameMaster($pokemonData);
        $this->assertSame(253, $move->getId());
        $this->assertSame('DRAGON_TAIL_FAST', $move->getName());
        $this->assertSame('POKEMON_TYPE_DRAGON', $move->getPokemonType()->getGameMasterTypeName());
        $this->assertEqualsWithDelta(1100.0, $move->getDurationMs(), PHP_FLOAT_EPSILON);
        $this->assertTrue($move->isFastMove());
        $this->assertEqualsWithDelta(9.0, $move->getEnergy(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(15.0, $move->getPower(), PHP_FLOAT_EPSILON);
    }
}
