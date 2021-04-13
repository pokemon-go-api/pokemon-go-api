<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Collections\RaidBossCollection;
use PokemonGoLingen\PogoAPI\Logger\NoopLogger;
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
        $basePokemon = new Pokemon(1, 'Test', '', PokemonType::normal(), PokemonType::none());
        $basePokemon->addPokemonRegionForm(
            new Pokemon(1, 'Test', 'Test_Form1', PokemonType::normal(), PokemonType::none())
        );
        $basePokemon->addPokemonRegionForm(
            new Pokemon(1, 'Test', 'Test_Form2', PokemonType::normal(), PokemonType::none())
        );
        $basePokemon->addPokemonRegionForm(
            new Pokemon(1, 'Test', 'Test_Form3', PokemonType::normal(), PokemonType::none())
        );
        $existingPokemonCollection = new PokemonCollection();
        $existingPokemonCollection->add($basePokemon);

        $existingRaidBossCollection = new RaidBossCollection();
        $existingRaidBossCollection->add(
            new RaidBoss(
                new Pokemon(1, 'Test', 'Test_Form1', PokemonType::none(), PokemonType::none()),
                true,
                RaidBoss::RAID_LEVEL_1,
                null
            )
        );
        $existingRaidBossCollection->add(
            new RaidBoss(
                new Pokemon(1, 'Test', 'Test_Form2', PokemonType::none(), PokemonType::none()),
                true,
                RaidBoss::RAID_LEVEL_1,
                null
            )
        );

        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin'));

        $sut = new RaidBossOverwrite(
            [
                (object) [
                    'startDate' => (object) ['date' => $now->modify('-1 Week')->format('Y-m-d H:i')],
                    'endDate' => (object) ['date' => $now->modify('-2 Hours 59 Minutes')->format('Y-m-d H:i')],
                    'pokemon' => 'Test',
                    'level' => RaidBoss::RAID_LEVEL_1,
                    'shiny' => 'false',
                ],
                (object) [
                    'startDate' => (object) ['date' => $now->modify('-1 Week')->format('Y-m-d H:i')],
                    'endDate' => (object) ['date' => $now->modify('-3 Hours 1 Minute')->format('Y-m-d H:i')],
                    'pokemon' => 'Test',
                    'form' => 'Test_Form2',
                    'level' => RaidBoss::RAID_LEVEL_1,
                    'shiny' => 'false',
                ],
                (object) [
                    'startDate' => (object) ['date' => $now->modify('-1 Minute')->format('Y-m-d H:i')],
                    'endDate' => (object) ['date' => $now->modify('+3 Hours')->format('Y-m-d H:i')],
                    'pokemon' => 'Test',
                    'form' => 'Test_Form3',
                    'level' => RaidBoss::RAID_LEVEL_1,
                    'shiny' => 'false',
                ],
            ],
            $existingPokemonCollection,
            new NoopLogger()
        );

        self::assertNotNull($existingRaidBossCollection->get('Test_Form1'));
        self::assertNotNull($existingRaidBossCollection->get('Test_Form2'));
        self::assertNull($existingRaidBossCollection->get('Test_Form3'));

        $sut->overwrite($existingRaidBossCollection);

        self::assertNotNull($existingRaidBossCollection->get('Test_Form1'));
        self::assertNull($existingRaidBossCollection->get('Test_Form2'));
        self::assertNotNull($existingRaidBossCollection->get('Test_Form3'));
    }
}
