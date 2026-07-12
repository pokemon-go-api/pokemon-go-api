<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Pokemon;
use PokemonGoApi\PogoAPI\Parser\LeekduckParser;
use PokemonGoApi\PogoAPI\Types\PokemonImage;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Types\RaidLevel;

use function array_map;

#[CoversClass(LeekduckParser::class)]
#[UsesClass(RaidBossCollection::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(RaidBoss::class)]
#[UsesClass(PokemonImage::class)]
final class LeekduckParserTest extends TestCase
{
    public function testParse(): void
    {
        $collection = $this->createStub(PokemonCollection::class);
        $collection->method('getByDexId')->willReturnCallback(
            static fn (int $dexNr): Pokemon => new Pokemon(
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
                'region' => $raidBoss->getPokemon()->getAssetBundleSuffix(),
                'costume' => $raidBoss->getCostumeId(),
            ],
            $parsedBosses,
        );

        $expected = [
            ['dexNr' => 150, 'level' => RaidLevel::RaidSuperMega, 'shiny' => true, 'region' => 'MEGA_Y', 'costume' => null],
            ['dexNr' => 382, 'level' => RaidLevel::RaidPrimal, 'shiny' => true, 'region' => 'PRIMAL', 'costume' => null],
            ['dexNr' => 383, 'level' => RaidLevel::RaidPrimal, 'shiny' => true, 'region' => 'PRIMAL', 'costume' => null],
            ['dexNr' => 229, 'level' => RaidLevel::RaidMega, 'shiny' => true, 'region' => 'MEGA', 'costume' => null],
            ['dexNr' => 359, 'level' => RaidLevel::RaidMega, 'shiny' => true, 'region' => 'MEGA', 'costume' => null],
            ['dexNr' => 649, 'level' => RaidLevel::Raid5, 'shiny' => true, 'region' => 'NORMAL', 'costume' => null],
            ['dexNr' => 649, 'level' => RaidLevel::Raid5, 'shiny' => true, 'region' => 'DOUSE', 'costume' => null],
            ['dexNr' => 649, 'level' => RaidLevel::Raid5, 'shiny' => true, 'region' => 'SHOCK', 'costume' => null],
            ['dexNr' => 105, 'level' => RaidLevel::Raid3, 'shiny' => true, 'region' => 'ALOLA', 'costume' => null],
            ['dexNr' => 157, 'level' => RaidLevel::Raid3, 'shiny' => true, 'region' => 'HISUIAN', 'costume' => null],
            ['dexNr' => 503, 'level' => RaidLevel::Raid3, 'shiny' => true, 'region' => 'HISUIAN', 'costume' => null],
            ['dexNr' => 25, 'level' => RaidLevel::Raid1, 'shiny' => true, 'region' => 'JAN_2023_NOEVOLVE', 'costume' => 'JAN_2023_NOEVOLVE'],
            ['dexNr' => 265, 'level' => RaidLevel::Raid1, 'shiny' => true, 'region' => '11', 'costume' => '11'],
            ['dexNr' => 562, 'level' => RaidLevel::Raid1, 'shiny' => true, 'region' => 'GALARIAN', 'costume' => null],
            ['dexNr' => 854, 'level' => RaidLevel::Raid1, 'shiny' => true, 'region' => null, 'costume' => null],
            ['dexNr' => 1012, 'level' => RaidLevel::Raid1, 'shiny' => false, 'region' => 'COUNTERFEIT', 'costume' => null],
            ['dexNr' => 380, 'level' => RaidLevel::ShadowRaid5, 'shiny' => true, 'region' => null, 'costume' => null],
            ['dexNr' => 123, 'level' => RaidLevel::ShadowRaid3, 'shiny' => true, 'region' => null, 'costume' => null],
            ['dexNr' => 142, 'level' => RaidLevel::ShadowRaid3, 'shiny' => true, 'region' => null, 'costume' => null],
            ['dexNr' => 302, 'level' => RaidLevel::ShadowRaid3, 'shiny' => true, 'region' => null, 'costume' => null],
            ['dexNr' => 562, 'level' => RaidLevel::ShadowRaid1, 'shiny' => false, 'region' => null, 'costume' => null],
            ['dexNr' => 708, 'level' => RaidLevel::ShadowRaid1, 'shiny' => false, 'region' => null, 'costume' => null],
        ];

        $this->assertSame($expected, $simpleResult);
    }
}
