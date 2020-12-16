<?php

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Types;

use PokemonGoLingen\PogoAPI\Types\PokemonCombatMove;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PokemonGoLingen\PogoAPI\Types\PokemonCombatMove
 */
class PokemonCombatMoveTest extends TestCase
{

    public function testCreateFromGameMaster(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/COMBAT_V0013_MOVE_WRAP.json');
        $data       = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        $move       = PokemonCombatMove::createFromGameMaster($data->data);
        self::assertSame(-45.0, $move->getEnergy());
        self::assertSame(60.0, $move->getPower());
    }
}
