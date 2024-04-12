<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Collections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;

#[CoversClass(PokemonCollection::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(Pokemon::class)]
class PokemonCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $pokemon           = new Pokemon(
            100,
            'TESTPOKEMON',
            'TESTPOKEMON_FORM',
            PokemonType::water(),
            PokemonType::fire(),
        );
        $pokemonRegionForm = new Pokemon(
            100,
            'TESTPOKEMON',
            'TESTPOKEMON_FORM',
            PokemonType::water(),
            PokemonType::steel(),
        );
        $collection        = new PokemonCollection();
        $collection->add($pokemon);

        $this->assertNull($collection->getByFormId('TESTPOKEMON_FORM'));

        $pokemon = $pokemon->withAddedPokemonRegionForm($pokemonRegionForm);
        $collection->add($pokemon);
        $this->assertSame($pokemonRegionForm, $collection->getByFormId('TESTPOKEMON_FORM'));
        $this->assertSame($pokemon, $collection->getByFormId('TESTPOKEMON'));

        $this->assertCount(1, $collection->toArray());
        // assert the same pokemon can't be added twice
        $collection->add($pokemon);
        $this->assertCount(1, $collection->toArray());

        $this->assertSame($pokemon, $collection->get('TESTPOKEMON'));
        $this->assertSame($pokemon, $collection->getByDexId(100));
        $this->assertTrue($collection->has($pokemon));

        $this->assertNull($collection->get('NOT_EXISTINGS'));
        $this->assertNull($collection->getByDexId(0));
    }
}
