<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\RaidOverwrite;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Logger\NoopLogger;
use PokemonGoApi\PogoAPI\RaidOverwrite\RaidBossOverwrite;
use PokemonGoApi\PogoAPI\RaidOverwrite\RaidBossOverwriteStruct;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;

#[CoversClass(RaidBossOverwrite::class)]
#[UsesClass(PokemonCollection::class)]
#[UsesClass(RaidBossCollection::class)]
#[UsesClass(RaidBossOverwriteStruct::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(RaidBoss::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(NoopLogger::class)]
class RaidBossOverwriteTest extends TestCase
{
    public function testOverwrite(): void
    {
        $basePokemon               = new Pokemon(1, 'Test', '', PokemonType::normal(), PokemonType::none());
        $basePokemon               = $basePokemon->withAddedPokemonRegionForm(
            new Pokemon(1, 'Test', 'Test_Form1', PokemonType::normal(), PokemonType::none()),
        );
        $basePokemon               = $basePokemon->withAddedPokemonRegionForm(
            new Pokemon(1, 'Test', 'Test_Form2', PokemonType::normal(), PokemonType::none()),
        );
        $basePokemon               = $basePokemon->withAddedPokemonRegionForm(
            new Pokemon(1, 'Test', 'Test_Form3', PokemonType::normal(), PokemonType::none()),
        );
        $existingPokemonCollection = new PokemonCollection();
        $existingPokemonCollection->add($basePokemon);

        $existingRaidBossCollection = new RaidBossCollection();
        $existingRaidBossCollection->add(
            new RaidBoss(
                new Pokemon(1, 'Test', 'Test_Form1', PokemonType::none(), PokemonType::none()),
                true,
                RaidBoss::RAID_LEVEL_1,
                null,
            ),
        );
        $existingRaidBossCollection->add(
            new RaidBoss(
                new Pokemon(1, 'Test', 'Test_Form2', PokemonType::none(), PokemonType::none()),
                true,
                RaidBoss::RAID_LEVEL_1,
                null,
            ),
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
            new NoopLogger(),
        );

        $this->assertNotNull($existingRaidBossCollection->getById('Test_Form1'));
        $this->assertNotNull($existingRaidBossCollection->getById('Test_Form2'));
        $this->assertNull($existingRaidBossCollection->getById('Test_Form3'));

        $sut->overwrite($existingRaidBossCollection);

        $this->assertNotNull($existingRaidBossCollection->getById('Test_Form1'));
        $this->assertNull($existingRaidBossCollection->getById('Test_Form2'));
        $this->assertNotNull($existingRaidBossCollection->getById('Test_Form3'));
    }
}
