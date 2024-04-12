<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Parser\LeekduckParser;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonImage;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;

use function array_map;

#[CoversClass(LeekduckParser::class)]
#[UsesClass(RaidBossCollection::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(RaidBoss::class)]
#[UsesClass(PokemonImage::class)]
class LeekduckParserTest extends TestCase
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

        $sut          = new LeekduckParser($collection);
        $parsedBosses = $sut->parseRaidBosses(__DIR__ . '/Fixtures/leekduck_raids.html')->toArray();
        $simpleResult = array_map(
            static fn (RaidBoss $raidBoss): array => [
                'dexNr' => $raidBoss->getPokemon()->getDexNr(),
                'level' => $raidBoss->getRaidLevel(),
                'shiny' => $raidBoss->isShinyAvailable(),
            ],
            $parsedBosses,
        );

        $expected = [
            ['dexNr' => 386, 'level' => RaidBoss::RAID_LEVEL_EX, 'shiny' => false],

            ['dexNr' => 6, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 9, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 18, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 94, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 229, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],
            ['dexNr' => 460, 'level' => RaidBoss::RAID_LEVEL_MEGA, 'shiny' => true],

            ['dexNr' => 379, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => true],
            ['dexNr' => 646, 'level' => RaidBoss::RAID_LEVEL_5, 'shiny' => false],

            ['dexNr' => 26, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 68, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 103, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 105, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 110, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 297, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 303, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => true],
            ['dexNr' => 306, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],
            ['dexNr' => 530, 'level' => RaidBoss::RAID_LEVEL_3, 'shiny' => false],

            ['dexNr' => 25, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 220, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 361, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 532, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 599, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 613, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => true],
            ['dexNr' => 677, 'level' => RaidBoss::RAID_LEVEL_1, 'shiny' => false],
        ];

        $this->assertSame($expected, $simpleResult);
    }
}
