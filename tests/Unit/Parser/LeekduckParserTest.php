<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Parser\LeekduckParser;

/**
 * @covers \PokemonGoLingen\PogoAPI\Parser\LeekduckParser
 */
class LeekduckParserTest extends TestCase
{
    public function testParse(): void
    {
        $sut      = new LeekduckParser();
        $expected = [
            1 => [
                ['name' => 'Pikachu', 'dexNr' => 25, 'level' => '1', 'shiny' => true, 'form' => null],
                ['name' => 'Swinub', 'dexNr' => 220, 'level' => '1', 'shiny' => true, 'form' => null],
                ['name' => 'Snorunt', 'dexNr' => 361, 'level' => '1', 'shiny' => true, 'form' => null],
                ['name' => 'Timburr', 'dexNr' => 532, 'level' => '1', 'shiny' => true, 'form' => null],
                ['name' => 'Klink', 'dexNr' => 599, 'level' => '1', 'shiny' => true, 'form' => null],
                ['name' => 'Cubchoo', 'dexNr' => 613, 'level' => '1', 'shiny' => true, 'form' => null],
                ['name' => 'Espurr', 'dexNr' => 677, 'level' => '1', 'shiny' => false, 'form' => null],
            ],
            3 => [
                ['name' => 'Alolan Exeggutor', 'dexNr' => 103, 'level' => '3', 'shiny' => true, 'form' => 'Alola'],
                ['name' => 'Alolan Raichu', 'dexNr' => 26, 'level' => '3', 'shiny' => true, 'form' => 'Alola'],
                ['name' => 'Machamp', 'dexNr' => 68, 'level' => '3', 'shiny' => false, 'form' => null],
                ['name' => 'Alolan Marowak', 'dexNr' => 105, 'level' => '3', 'shiny' => true, 'form' => 'Alola'],
                ['name' => 'Hariyama', 'dexNr' => 297, 'level' => '3', 'shiny' => false, 'form' => null],
                ['name' => 'Mawile', 'dexNr' => 303, 'level' => '3', 'shiny' => true, 'form' => null],
                ['name' => 'Aggron', 'dexNr' => 306, 'level' => '3', 'shiny' => false, 'form' => null],
                ['name' => 'Excadrill', 'dexNr' => 530, 'level' => '3', 'shiny' => false, 'form' => null],
                ['name' => 'Galarian Weezing', 'dexNr' => 110, 'level' => '3', 'shiny' => false, 'form' => 'Galar'],
            ],
            5 => [
                ['name' => 'Registeel', 'dexNr' => 379, 'level' => '5', 'shiny' => true, 'form' => null],
                ['name' => 'Kyurem', 'dexNr' => 646, 'level' => '5', 'shiny' => false, 'form' => null],
            ],
            'Mega' => [
                ['name' => 'Mega Charizard X', 'dexNr' => 6, 'level' => 'Mega', 'shiny' => true, 'form' => 'Mega X'],
                ['name' => 'Mega Gengar', 'dexNr' => 94, 'level' => 'Mega', 'shiny' => true, 'form' => 'Mega'],
                ['name' => 'Mega Abomasnow', 'dexNr' => 460, 'level' => 'Mega', 'shiny' => true, 'form' => 'Mega'],
                ['name' => 'Mega Charizard Y', 'dexNr' => 6, 'level' => 'Mega', 'shiny' => true, 'form' => 'Mega Y'],
                ['name' => 'Mega Blastoise', 'dexNr' => 9, 'level' => 'Mega', 'shiny' => true, 'form' => 'Mega'],
                ['name' => 'Mega Pidgeot', 'dexNr' => 18, 'level' => 'Mega', 'shiny' => true, 'form' => 'Mega'],
                ['name' => 'Mega Houndoom', 'dexNr' => 229, 'level' => 'Mega', 'shiny' => true, 'form' => 'Mega'],
            ],
            'EX' => [
                ['name' => 'Deoxys', 'dexNr' => 386, 'level' => 'EX', 'shiny' => false, 'form' => 'Speed'],
            ],
        ];

        self::assertSame($expected, $sut->parseRaidBosses(__DIR__ . '/Fixtures/leekduck_raids.html'));
    }
}
