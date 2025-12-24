<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Pokemon;
use PokemonGoApi\PogoAPI\Parser\SnacknapParser;
use PokemonGoApi\PogoAPI\Types\MaxBattle;
use PokemonGoApi\PogoAPI\Types\PokemonImage;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\RaidBoss;

use function array_map;

#[CoversClass(SnacknapParser::class)]
#[UsesClass(RaidBossCollection::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(RaidBoss::class)]
#[UsesClass(PokemonImage::class)]
final class SnacknapParserTest extends TestCase
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

        $sut          = new SnacknapParser($collection);
        $parsedBosses = $sut->parseMaxBattle(__DIR__ . '/Fixtures/snacknap_maxbattle.html')->toArray();
        $simpleResult = array_map(
            static fn (MaxBattle $maxBattle): array => [
                'dexNr' => $maxBattle->pokemon->getDexNr(),
                'level' => $maxBattle->maxBattleLevel->value,
                'shiny' => $maxBattle->shinyAvailable,
            ],
            $parsedBosses,
        );
        $expected     = [
            ['dexNr' => 12, 'level' => 6, 'shiny' => true],
            ['dexNr' => 1, 'level' => 2, 'shiny' => true],
            ['dexNr' => 1, 'level' => 1, 'shiny' => true],
            ['dexNr' => 813, 'level' => 1, 'shiny' => false],
        ];

        $this->assertSame($expected, $simpleResult);
    }
}
