<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Collections;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\AttacksCollection;
use PokemonGoApi\PogoAPI\Types\PokemonMove;
use PokemonGoApi\PogoAPI\Types\PokemonType;

/**
 * @uses \PokemonGoApi\PogoAPI\Types\PokemonType
 * @uses \PokemonGoApi\PogoAPI\Types\PokemonMove
 *
 * @covers \PokemonGoApi\PogoAPI\Collections\AttacksCollection
 */
class AttacksCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $pokemonMove = new PokemonMove(
            100,
            'TESTMOVE',
            PokemonType::fire(),
            10.0,
            15.0,
            100.0,
            true
        );
        $sut         = new AttacksCollection();
        $sut->add($pokemonMove);

        self::assertCount(1, $sut->toArray());
        self::assertSame($pokemonMove, $sut->getById(100));
        self::assertSame($pokemonMove, $sut->getByName('TESTMOVE'));
    }
}
