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
            'TESTPOKEMON_MEGA',
            false,
            RaidBoss::RAID_LEVEL_EX,
            new Pokemon(
                100,
                'TESTPOKEMON',
                'TESTPOKEMON_FORM',
                PokemonType::water(),
                PokemonType::fire()
            ),
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
    }
}
