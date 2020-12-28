<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Parser\LeekduckParser;
use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PokemonGoLingen\PogoAPI\Types\PokemonType;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;

use function array_map;

/**
 * @uses \PokemonGoLingen\PogoAPI\Collections\RaidBossCollection
 * @uses \PokemonGoLingen\PogoAPI\Types\Pokemon
 * @uses \PokemonGoLingen\PogoAPI\Types\PokemonType
 * @uses \PokemonGoLingen\PogoAPI\Types\RaidBoss
 *
 * @covers \PokemonGoLingen\PogoAPI\Parser\LeekduckParser
 */
class LeekduckParserTest extends TestCase
{
    public function testParse(): void
    {
        $collection = $this->createMock(PokemonCollection::class);
        $collection->method('getByDexId')->willReturnCallback(static function (int $dexNr) {
            return new Pokemon($dexNr, 'id_' . $dexNr, 'id_' . $dexNr, PokemonType::none(), PokemonType::none());
        });

        $sut          = new LeekduckParser($collection);
        $parsedBosses = $sut->parseRaidBosses(__DIR__ . '/Fixtures/leekduck_raids.html')->toArray();
        $simpleResult = array_map(
            static function (RaidBoss $raidBoss): array {
                return [
                    'dexNr' => $raidBoss->getPokemon()->getDexNr(),
                    'level' => $raidBoss->getRaidLevel(),
                    'shiny' => $raidBoss->isShinyAvailable(),
                ];
            },
            $parsedBosses
        );

        $expected = [
            ['dexNr' => 386, 'level' => RaidBoss::RAID_LEVEL_EX, 'shiny' => false],

            ['dexNr' => 229, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 18, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 9, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 460, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 94, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 6, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],

            ['dexNr' => 646, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => false],
            ['dexNr' => 379, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],

            ['dexNr' => 306, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 110, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 530, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 303, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 297, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 105, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 68, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 26, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 103, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],

            ['dexNr' => 220, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 677, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => false],
            ['dexNr' => 613, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 599, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 532, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 361, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 25, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
        ];

        self::assertSame($expected, $simpleResult);
    }
}
