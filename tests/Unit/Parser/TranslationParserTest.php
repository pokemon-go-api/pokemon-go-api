<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Parser\CustomTranslations;
use PokemonGoApi\PogoAPI\Parser\TranslationParser;

/**
 * @uses \PokemonGoApi\PogoAPI\Collections\TranslationCollection
 *
 * @covers \PokemonGoApi\PogoAPI\Parser\TranslationParser
 */
class TranslationParserTest extends TestCase
{
    public function testParseFile(): void
    {
        $sut        = new TranslationParser();
        $collection = $sut->loadLanguage(
            'English',
            __DIR__ . '/Fixtures/latest_apk_English.txt',
            '',
            [
                CustomTranslations::REGIONAL_PREFIX . 'dummy' => 'Dummy',
                'nonexisting_dummy2' => 'Dummy2',
            ]
        );

        self::assertSame('Charizard', $collection->getPokemonName(6));
        self::assertSame(['Mega Charizard X', 'Mega Charizard Y'], $collection->getPokemonMegaNames(6));
        self::assertSame('Defense', $collection->getPokemonFormName('defense'));
        self::assertNull($collection->getPokemonFormName('deoxys_defense'));
        self::assertNull($collection->getPokemonFormName('normal'));
        self::assertSame('Normal', $collection->getPokemonFormName('deoxys_normal'));
        self::assertSame('Bug', $collection->getTypeName('bug'));
        self::assertSame('Aeroblast', $collection->getMoveName(335));
        self::assertSame('Fly', $collection->getMoveName(341));
        self::assertSame('Dummy', $collection->getRegionalForm('dummy'));
        self::assertSame([
            'quest_power_up_plural' => 'Power up Pokémon {0} times',
            'challenge_buddy_affection_single' => 'Earn a heart with your buddy',
        ], $collection->getQuests());
        self::assertNull($collection->getRegionalForm('dummy2'));
    }
}
