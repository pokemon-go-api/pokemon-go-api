<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Parser\TranslationParser;

/**
 * @covers \PokemonGoLingen\PogoAPI\Parser\TranslationParser
 */
class TranslationParserTest extends TestCase
{
    public function testParseFile(): void
    {
        $sut        = new TranslationParser();
        $collection = $sut->loadLanguage(
            'English',
            __DIR__ . '/Fixtures/latest_apk_English.txt',
            ''
        );

        self::assertSame('Charizard', $collection->getPokemonName(6));
        self::assertSame(['Mega Charizard X', 'Mega Charizard Y'], $collection->getPokemonMegaNames(6));
        self::assertSame('Defense', $collection->getPokemonFormName('defense', 'deoxys_defense'));
        self::assertSame('Normal', $collection->getPokemonFormName('normal', 'deoxys_normal'));
        self::assertSame('Bug', $collection->getTypeName('pokemon_type_bug'));
        self::assertSame('Aeroblast', $collection->getMoveName(335));
        self::assertSame('Fly', $collection->getMoveName(341));
    }
}
