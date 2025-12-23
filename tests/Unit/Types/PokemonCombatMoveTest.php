<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonCombatMove;

use function file_get_contents;

use const PHP_FLOAT_EPSILON;

#[CoversClass(PokemonCombatMove::class)]
final class PokemonCombatMoveTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster  = file_get_contents(__DIR__ . '/Fixtures/COMBAT_V0013_MOVE_WRAP.json') ?: '{}';
        $pokemonData = JsonParser::decodeGameMasterFileData($gameMaster);

        $move = PokemonCombatMove::createFromGameMaster($pokemonData);
        $this->assertSame(-45.0, $move->getEnergy());
        $this->assertEqualsWithDelta(60.0, $move->getPower(), PHP_FLOAT_EPSILON);
    }
}
