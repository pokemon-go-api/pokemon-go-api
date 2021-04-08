<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Collections;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PokemonGoLingen\PogoAPI\Types\PokemonType;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;

/**
 * @uses \PokemonGoLingen\PogoAPI\Types\PokemonType
 * @uses \PokemonGoLingen\PogoAPI\Types\Pokemon
 * @uses \PokemonGoLingen\PogoAPI\Types\RaidBoss
 *
 * @covers \PokemonGoLingen\PogoAPI\Collections\RaidBossCollection
 */
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
                PokemonType::fire()
            ),
            false,
            RaidBoss::RAID_LEVEL_EX,
            null
        );
        $collection = new RaidBossCollection();
        $collection->add($raidBoss);
        self::assertCount(1, $collection->toArray());
        // assert the same Boss can't be added twice
        $collection->add($raidBoss);
        self::assertCount(1, $collection->toArray());

        self::assertSame($raidBoss, $collection->get('TESTPOKEMON_MEGA'));
        self::assertTrue($collection->has($raidBoss));

        self::assertNull($collection->get('NOT_EXISTINGS'));

        $collection->remove($raidBoss->getPokemonId());
        self::assertFalse($collection->has($raidBoss));
    }

    public function testOrderOfCollection(): void
    {
        $raidBoss1  = new RaidBoss(
            new Pokemon(100, 'TESTPOKEMON', 'TESTPOKEMON_EX', PokemonType::water(), PokemonType::fire()),
            false,
            RaidBoss::RAID_LEVEL_EX,
            null
        );
        $raidBoss2  = new RaidBoss(
            new Pokemon(100, 'TESTPOKEMON', 'TESTPOKEMON_MEGA', PokemonType::water(), PokemonType::fire()),
            false,
            RaidBoss::RAID_LEVEL_MEGA,
            null
        );
        $collection = new RaidBossCollection();
        $collection->add($raidBoss2);
        $collection->add($raidBoss1);

        self::assertSame('TESTPOKEMON_EX', $collection->toArray()[0]->getPokemonId());
    }
}
