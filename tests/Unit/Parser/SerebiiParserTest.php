<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Parser\SerebiiParser;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;

use function array_map;

#[CoversClass(SerebiiParser::class)]
#[UsesClass(RaidBossCollection::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(RaidBoss::class)]
class SerebiiParserTest extends TestCase
{
    public function testParse(): void
    {
        $collection = $this->createMock(PokemonCollection::class);
        $collection->method('getByDexId')->willReturnCallback(
            static fn (int $dexNr) => new Pokemon(
                $dexNr,
                'id_' . $dexNr,
                'id_' . $dexNr,
                PokemonType::none(),
                PokemonType::none(),
            ),
        );

        $sut          = new SerebiiParser($collection);
        $parsedBosses = $sut->parseRaidBosses(__DIR__ . '/Fixtures/serebii_raids.html')->toArray();
        $simpleResult = array_map(
            static fn (RaidBoss $raidBoss): array => [
                'dexNr' => $raidBoss->getPokemon()->getDexNr(),
                'level' => $raidBoss->getRaidLevel(),
                'shiny' => $raidBoss->isShinyAvailable(),
            ],
            $parsedBosses,
        );

        $expected = [
            ['dexNr' => 3, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 6, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 9, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],

            ['dexNr' => 144, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],
            ['dexNr' => 145, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],
            ['dexNr' => 146, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],
            ['dexNr' => 150, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],
            ['dexNr' => 641, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => false],

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

        $this->assertSame($expected, $simpleResult);
    }
}
