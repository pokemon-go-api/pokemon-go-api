<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Collections;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;

/**
 * @uses \PokemonGoApi\PogoAPI\Types\PokemonType
 * @uses \PokemonGoApi\PogoAPI\Types\Pokemon
 *
 * @covers \PokemonGoApi\PogoAPI\Collections\PokemonCollection
 */
class PokemonCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $pokemon           = new Pokemon(
            100,
            'TESTPOKEMON',
            'TESTPOKEMON_FORM',
            PokemonType::water(),
            PokemonType::fire()
        );
        $pokemonRegionForm = new Pokemon(
            100,
            'TESTPOKEMON',
            'TESTPOKEMON_FORM',
            PokemonType::water(),
            PokemonType::steel()
        );
        $collection        = new PokemonCollection();
        $collection->add($pokemon);

        $pokemon->addPokemonRegionForm($pokemonRegionForm);
        self::assertNull($collection->getByFormId('TESTPOKEMON_FORM'));
        $collection->add($pokemon);
        self::assertSame($pokemonRegionForm, $collection->getByFormId('TESTPOKEMON_FORM'));
        self::assertSame($pokemon, $collection->getByFormId('TESTPOKEMON'));

        self::assertCount(1, $collection->toArray());
        // assert the same pokemon can't be added twice
        $collection->add($pokemon);
        self::assertCount(1, $collection->toArray());

        self::assertSame($pokemon, $collection->get('TESTPOKEMON'));
        self::assertSame($pokemon, $collection->getByDexId(100));
        self::assertTrue($collection->has($pokemon));

        self::assertNull($collection->get('NOT_EXISTINGS'));
        self::assertNull($collection->getByDexId(0));
    }
}
