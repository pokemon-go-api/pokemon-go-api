<?php

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PokemonGoLingen\PogoAPI\Parser\TranslationParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PokemonGoLingen\PogoAPI\Parser\TranslationParser
 */
class TranslationParserTest extends TestCase
{
    public function testParseFile(): void
    {
        $sut = new TranslationParser(__DIR__.'/Fixtures');
        $collection = $sut->loadLanguage('English');

        self::assertSame('Charizard', $collection->getPokemonName(6));
        self::assertSame(['Mega Charizard X', 'Mega Charizard Y'], $collection->getPokemonMegaNames(6));
        self::assertSame('Defense', $collection->getPokemonFormName('defense', 'deoxys_defense'));
        self::assertSame('Normal', $collection->getPokemonFormName('normal', 'deoxys_normal'));
        self::assertSame('Bug', $collection->getTypeName('pokemon_type_bug'));
        self::assertSame('Aeroblast', $collection->getMoveName(335));
        self::assertSame('Fly', $collection->getMoveName(341));
    }
}
