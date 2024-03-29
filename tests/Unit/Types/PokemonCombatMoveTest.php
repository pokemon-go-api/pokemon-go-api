<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonCombatMove;
use stdClass;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/** @covers \PokemonGoApi\PogoAPI\Types\PokemonCombatMove */
class PokemonCombatMoveTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/COMBAT_V0013_MOVE_WRAP.json') ?: '{}';
        $data       = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        self::assertInstanceOf(stdClass::class, $data);
        $move = PokemonCombatMove::createFromGameMaster($data->data);
        self::assertSame(-45.0, $move->getEnergy());
        self::assertSame(60.0, $move->getPower());
    }
}
