<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\TranslationCollection;
use PokemonGoApi\PogoAPI\Parser\CustomTranslations;
use PokemonGoApi\PogoAPI\Parser\TranslationParser;

#[CoversClass(TranslationParser::class)]
#[UsesClass(TranslationCollection::class)]
final class TranslationParserTest extends TestCase
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
            ],
        );

        $this->assertSame('Charizard', $collection->getPokemonName(6));
        $this->assertSame(['Mega Charizard X', 'Mega Charizard Y'], $collection->getPokemonMegaNames(6));
        $this->assertSame('Defense', $collection->getPokemonFormName('defense'));
        $this->assertNull($collection->getPokemonFormName('deoxys_defense'));
        $this->assertNull($collection->getPokemonFormName('normal'));
        $this->assertSame('Normal', $collection->getPokemonFormName('deoxys_normal'));
        $this->assertSame('Bug', $collection->getTypeName('bug'));
        $this->assertSame('Aeroblast', $collection->getMoveName(335));
        $this->assertSame('Fly', $collection->getMoveName(341));
        $this->assertSame('Dummy', $collection->getRegionalForm('dummy'));
        $this->assertSame([
            'quest_power_up_plural' => 'Power up Pokémon {0} times',
            'challenge_buddy_affection_single' => 'Earn a heart with your buddy',
        ], $collection->getQuests());
        $this->assertNull($collection->getRegionalForm('dummy2'));
    }
}
