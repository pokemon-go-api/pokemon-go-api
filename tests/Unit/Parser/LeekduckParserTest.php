<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Parser\LeekduckParser;
use PokemonGoApi\PogoAPI\Types\Pokemon;
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
        $collection = $this->createMock(PokemonCollection::class);
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
            ],
            $parsedBosses,
        );

        $expected = [
            ['dexNr' => 229, 'level' => RaidLevel::RaidMega, 'shiny' => true, 'region' => 'MEGA'],
            ['dexNr' => 359, 'level' => RaidLevel::RaidMega, 'shiny' => true, 'region' => 'MEGA'],
            ['dexNr' => 649, 'level' => RaidLevel::Raid5, 'shiny' => true, 'region' => 'NORMAL'],
            ['dexNr' => 649, 'level' => RaidLevel::Raid5, 'shiny' => true, 'region' => 'DOUSE'],
            ['dexNr' => 649, 'level' => RaidLevel::Raid5, 'shiny' => true, 'region' => 'SHOCK'],
            ['dexNr' => 105, 'level' => RaidLevel::Raid3, 'shiny' => true, 'region' => 'ALOLA'],
            ['dexNr' => 157, 'level' => RaidLevel::Raid3, 'shiny' => true, 'region' => 'HISUIAN'],
            ['dexNr' => 503, 'level' => RaidLevel::Raid3, 'shiny' => true, 'region' => 'HISUIAN'],
            ['dexNr' => 562, 'level' => RaidLevel::Raid1, 'shiny' => true, 'region' => 'GALARIAN'],
            ['dexNr' => 854, 'level' => RaidLevel::Raid1, 'shiny' => true, 'region' => null],
            ['dexNr' => 1012, 'level' => RaidLevel::Raid1, 'shiny' => false, 'region' => 'COUNTERFEIT'],
            ['dexNr' => 380, 'level' => RaidLevel::ShadowRaid5, 'shiny' => true, 'region' => null],
            ['dexNr' => 123, 'level' => RaidLevel::ShadowRaid3, 'shiny' => true, 'region' => null],
            ['dexNr' => 142, 'level' => RaidLevel::ShadowRaid3, 'shiny' => true, 'region' => null],
            ['dexNr' => 302, 'level' => RaidLevel::ShadowRaid3, 'shiny' => true, 'region' => null],
            ['dexNr' => 562, 'level' => RaidLevel::ShadowRaid1, 'shiny' => false, 'region' => null],
            ['dexNr' => 708, 'level' => RaidLevel::ShadowRaid1, 'shiny' => false, 'region' => null],
        ];

        $this->assertSame($expected, $simpleResult);
    }
}
