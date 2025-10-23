<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Collections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\RaidLevel;

#[CoversClass(RaidBossCollection::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(RaidBoss::class)]
class RaidBossCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $raidBoss   = new RaidBoss(
            new Pokemon(
                100,
                'TESTPOKEMON',
                'TESTPOKEMON_MEGA',
                PokemonType::water(),
                PokemonType::fire(),
            ),
            false,
            RaidLevel::RaidEx,
            null,
        );
        $collection = new RaidBossCollection();
        $collection->add($raidBoss);
        $this->assertCount(1, $collection->toArray());
        // assert the same Boss can't be added twice
        $collection->add($raidBoss);
        $this->assertCount(1, $collection->toArray());

        $raidBossId = 'TESTPOKEMON_MEGA-ex';
        $this->assertSame($raidBoss, $collection->getById($raidBossId));
        $this->assertTrue($collection->has($raidBoss));

        $this->assertNull($collection->getById('NOT_EXISTINGS'));

        $collection->remove($raidBossId);
        $this->assertFalse($collection->has($raidBoss));
    }

    public function testOrderOfCollection(): void
    {
        $raidBoss1  = new RaidBoss(
            new Pokemon(100, 'TESTPOKEMON', 'TESTPOKEMON_EX', PokemonType::water(), PokemonType::fire()),
            false,
            RaidLevel::RaidEx,
            null,
        );
        $raidBoss2  = new RaidBoss(
            new Pokemon(100, 'TESTPOKEMON', 'TESTPOKEMON_MEGA', PokemonType::water(), PokemonType::fire()),
            false,
            RaidLevel::RaidMega,
            null,
        );
        $collection = new RaidBossCollection();
        $collection->add($raidBoss2);
        $collection->add($raidBoss1);

        $this->assertSame('TESTPOKEMON_EX', $collection->toArray()[0]->getPokemonWithMegaFormId());
    }
}
