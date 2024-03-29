<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Parser\PokefansNetParser;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;

use function array_map;

/**
 * @uses \PokemonGoApi\PogoAPI\Collections\RaidBossCollection
 * @uses \PokemonGoApi\PogoAPI\Types\Pokemon
 * @uses \PokemonGoApi\PogoAPI\Types\PokemonType
 * @uses \PokemonGoApi\PogoAPI\Types\RaidBoss
 *
 * @covers \PokemonGoApi\PogoAPI\Parser\PokefansNetParser
 */
class PokefansNetParserTest extends TestCase
{
    public function testParse(): void
    {
        $collection = $this->createMock(PokemonCollection::class);
        $collection->method('getByDexId')->willReturnCallback(static function (int $dexNr) {
            return new Pokemon($dexNr, 'id_' . $dexNr, 'id_' . $dexNr, PokemonType::none(), PokemonType::none());
        });

        $sut          = new PokefansNetParser($collection);
        $parsedBosses = $sut->parseRaidBosses(__DIR__ . '/Fixtures/pokefansNet_raids.html')->toArray();
        $simpleResult = array_map(
            static function (RaidBoss $raidBoss): array {
                return [
                    'dexNr' => $raidBoss->getPokemon()->getDexNr(),
                    'level' => $raidBoss->getRaidLevel(),
                    'shiny' => $raidBoss->isShinyAvailable(),
                ];
            },
            $parsedBosses,
        );

        $expected = [
            ['dexNr' => 9, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 18, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 181, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],

            ['dexNr' => 642, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],

            ['dexNr' => 75, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 82, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 227, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 375, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],

            ['dexNr' => 50, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 299, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 524, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 529, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => false],
            ['dexNr' => 597, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 599, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
        ];

        self::assertSame($expected, $simpleResult);
    }
}
