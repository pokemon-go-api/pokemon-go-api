<?php

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Collections;

use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PokemonGoLingen\PogoAPI\Types\PokemonType;

/**
 * @covers \PokemonGoLingen\PogoAPI\Collections\PokemonCollection
 */
class PokemonCollectionTest extends TestCase
{
    public function testCollection() : void
    {
        $pokemon = new Pokemon(
            100,
            'TESTPOKEMON',
            'TESTPOKEMON_FORM',
            new PokemonType('WATER'),
            new PokemonType('FIRE')
        );
        $collection = new PokemonCollection();
        $collection->add($pokemon);
        self::assertCount(1, $collection->toArray());

        self::assertSame($pokemon, $collection->get('TESTPOKEMON'));
        self::assertSame($pokemon, $collection->getByDexId(100));
        self::assertTrue($collection->has($pokemon));

        self::assertNull($collection->get('NOT_EXISTINGS'));
        self::assertNull($collection->getByDexId(0));
    }
}
