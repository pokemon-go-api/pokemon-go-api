<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\RaidOverwrite\RaidBossOverwrite;
use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PokemonGoLingen\PogoAPI\Types\PokemonType;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;

/**
 * @uses \PokemonGoLingen\PogoAPI\Collections\PokemonCollection
 * @uses \PokemonGoLingen\PogoAPI\Collections\RaidBossCollection
 * @uses \PokemonGoLingen\PogoAPI\RaidOverwrite\RaidBossOverwriteStruct
 * @uses \PokemonGoLingen\PogoAPI\Types\Pokemon
 * @uses \PokemonGoLingen\PogoAPI\Types\RaidBoss
 * @uses \PokemonGoLingen\PogoAPI\Types\PokemonType
 *
 * @covers \PokemonGoLingen\PogoAPI\RaidOverwrite\RaidBossOverwrite
 */
class RaidBossOverwriteTest extends TestCase
{
    public function testOverwrite(): void
    {
        $existingPokemonCollection = new PokemonCollection();
        $existingPokemonCollection->add(
            new Pokemon(1, 'Test3', '', PokemonType::normal(), PokemonType::none())
        );

        $pokemonStub                = new Pokemon(1, 'Test0', '', PokemonType::none(), PokemonType::none());
        $existingRaidBossCollection = new RaidBossCollection();
        $existingRaidBossCollection->add(
            new RaidBoss('Test1', true, RaidBoss::RAID_LEVEL_1, $pokemonStub, null)
        );
        $existingRaidBossCollection->add(
            new RaidBoss('Test2', true, RaidBoss::RAID_LEVEL_1, $pokemonStub, null)
        );

        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin'));

        $sut = new RaidBossOverwrite(
            [
                (object) [
                    'startDate' => (object) ['date' => $now->modify('-1 Week')->format('Y-m-d H:i')],
                    'endDate' => (object) ['date' => $now->modify('-2 Hours 59 Minutes')->format('Y-m-d H:i')],
                    'pokemon' => 'Test1',
                    'level' => RaidBoss::RAID_LEVEL_1,
                    'shiny' => 'false',
                ],
                (object) [
                    'startDate' => (object) ['date' => $now->modify('-1 Week')->format('Y-m-d H:i')],
                    'endDate' => (object) ['date' => $now->modify('-3 Hours 1 Minute')->format('Y-m-d H:i')],
                    'pokemon' => 'Test2',
                    'level' => RaidBoss::RAID_LEVEL_1,
                    'shiny' => 'false',
                ],
                (object) [
                    'startDate' => (object) ['date' => $now->modify('-1 Minute')->format('Y-m-d H:i')],
                    'endDate' => (object) ['date' => $now->modify('+3 Hours')->format('Y-m-d H:i')],
                    'pokemon' => 'Test3',
                    'level' => RaidBoss::RAID_LEVEL_1,
                    'shiny' => 'false',
                ],
            ],
            $existingPokemonCollection
        );

        self::assertNotNull($existingRaidBossCollection->get('Test1'));
        self::assertNotNull($existingRaidBossCollection->get('Test2'));
        self::assertNull($existingRaidBossCollection->get('Test3'));

        $sut->overwrite($existingRaidBossCollection);

        self::assertNotNull($existingRaidBossCollection->get('Test1'));
        self::assertNull($existingRaidBossCollection->get('Test2'));
        self::assertNotNull($existingRaidBossCollection->get('Test3'));
    }
}
