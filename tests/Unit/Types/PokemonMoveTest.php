<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonMove;
use stdClass;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @uses \PokemonGoApi\PogoAPI\Types\PokemonType
 *
 * @covers \PokemonGoApi\PogoAPI\Types\PokemonMove
 */
class PokemonMoveTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/V0021_MOVE_FLAME_WHEEL.json') ?: '{}';
        $data       = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        self::assertInstanceOf(stdClass::class, $data);
        $move = PokemonMove::createFromGameMaster($data->data);
        self::assertSame(21, $move->getId());
        self::assertSame('FLAME_WHEEL', $move->getName());
        self::assertSame('POKEMON_TYPE_FIRE', $move->getPokemonType()->getGameMasterTypeName());
        self::assertSame(2700.0, $move->getDurationMs());
        self::assertFalse($move->isFastMove());
        self::assertSame(-50.0, $move->getEnergy());
        self::assertSame(60.0, $move->getPower());
    }

    public function testCreateFastMoveFromGameMaster(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/V0253_MOVE_DRAGON_TAIL_FAST.json') ?: '{}';
        $data       = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        self::assertInstanceOf(stdClass::class, $data);
        $move = PokemonMove::createFromGameMaster($data->data);
        self::assertSame(253, $move->getId());
        self::assertSame('DRAGON_TAIL_FAST', $move->getName());
        self::assertSame('POKEMON_TYPE_DRAGON', $move->getPokemonType()->getGameMasterTypeName());
        self::assertSame(1100.0, $move->getDurationMs());
        self::assertTrue($move->isFastMove());
        self::assertSame(9.0, $move->getEnergy());
        self::assertSame(15.0, $move->getPower());
    }
}
