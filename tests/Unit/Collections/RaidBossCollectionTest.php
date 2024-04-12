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
            RaidBoss::RAID_LEVEL_EX,
            null,
        );
        $collection = new RaidBossCollection();
        $collection->add($raidBoss);
        $this->assertCount(1, $collection->toArray());
        // assert the same Boss can't be added twice
        $collection->add($raidBoss);
        $this->assertCount(1, $collection->toArray());

        $this->assertSame($raidBoss, $collection->getById('TESTPOKEMON_MEGA'));
        $this->assertTrue($collection->has($raidBoss));

        $this->assertNull($collection->getById('NOT_EXISTINGS'));

        $collection->remove($raidBoss->getPokemonWithMegaFormId());
        $this->assertFalse($collection->has($raidBoss));
    }

    public function testOrderOfCollection(): void
    {
        $raidBoss1  = new RaidBoss(
            new Pokemon(100, 'TESTPOKEMON', 'TESTPOKEMON_EX', PokemonType::water(), PokemonType::fire()),
            false,
            RaidBoss::RAID_LEVEL_EX,
            null,
        );
        $raidBoss2  = new RaidBoss(
            new Pokemon(100, 'TESTPOKEMON', 'TESTPOKEMON_MEGA', PokemonType::water(), PokemonType::fire()),
            false,
            RaidBoss::RAID_LEVEL_MEGA,
            null,
        );
        $collection = new RaidBossCollection();
        $collection->add($raidBoss2);
        $collection->add($raidBoss1);

        $this->assertSame('TESTPOKEMON_EX', $collection->toArray()[0]->getPokemonWithMegaFormId());
    }
}
