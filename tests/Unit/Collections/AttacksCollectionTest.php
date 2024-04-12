<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Collections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\AttacksCollection;
use PokemonGoApi\PogoAPI\Types\PokemonMove;
use PokemonGoApi\PogoAPI\Types\PokemonType;

#[CoversClass(AttacksCollection::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(PokemonMove::class)]
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
            true,
        );
        $sut         = new AttacksCollection();
        $sut->add($pokemonMove);

        $this->assertCount(1, $sut->toArray());
        $this->assertSame($pokemonMove, $sut->getById(100));
        $this->assertSame($pokemonMove, $sut->getByName('TESTMOVE'));
    }
}
