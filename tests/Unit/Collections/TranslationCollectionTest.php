<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Collections;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollection;

/**
 * @covers \PokemonGoLingen\PogoAPI\Collections\TranslationCollection
 */
class TranslationCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $collection = new TranslationCollection('Testlanguage');
        $collection->addMoveName(123, 'Movename in testlanguage');
        $collection->addTypeName('TYPENAME', 'Typename in testlanguage');
        $collection->addPokemonName(5, 'Pokemon 5 in testlanguage');
        $collection->addPokemonMegaName(5, 'Mega 1');
        $collection->addPokemonMegaName(5, 'Mega 2');
        $collection->addPokemonFormName('POKEMON_FORM', 'Pokemon Form in testlanguage');

        self::assertSame('Testlanguage', $collection->getLanguageName());

        self::assertSame('Movename in testlanguage', $collection->getMoveName(123));
        self::assertNull($collection->getMoveName(1230));

        self::assertSame('Typename in testlanguage', $collection->getTypeName('TYPENAME'));
        self::assertSame('Pokemon 5 in testlanguage', $collection->getPokemonName(5));
        self::assertSame(
            [
                'Mega 1',
                'Mega 2',
            ],
            $collection->getPokemonMegaNames(5)
        );
        self::assertSame('Pokemon Form in testlanguage', $collection->getPokemonFormName('POKEMON_FORM'));
        self::assertNull($collection->getPokemonFormName('FALLBACK'));
    }
}
