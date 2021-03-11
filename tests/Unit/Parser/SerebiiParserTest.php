<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Parser\SerebiiParser;
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
 * @covers \PokemonGoLingen\PogoAPI\Parser\SerebiiParser
 */
class SerebiiParserTest extends TestCase
{
    public function testParse(): void
    {
        $collection = $this->createMock(PokemonCollection::class);
        $collection->method('getByDexId')->willReturnCallback(static function (int $dexNr) {
            return new Pokemon($dexNr, 'id_' . $dexNr, 'id_' . $dexNr, PokemonType::none(), PokemonType::none());
        });

        $sut          = new SerebiiParser($collection);
        $parsedBosses = $sut->parseRaidBosses(__DIR__ . '/Fixtures/serebii_raids.html')->toArray();
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
            ['dexNr' => 3, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 6, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 9, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],

            ['dexNr' => 144, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],
            ['dexNr' => 145, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],
            ['dexNr' => 146, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],
            ['dexNr' => 150, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],

            ['dexNr' => 64, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 67, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 93, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 123, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 127, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 131, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],

            ['dexNr' => 1, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 4, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 7, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 25, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 27, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 129, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 147, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
        ];

        self::assertSame($expected, $simpleResult);
    }
}
