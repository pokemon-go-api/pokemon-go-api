<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Collections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\TranslationCollection;

#[CoversClass(TranslationCollection::class)]
class TranslationCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $collection = new TranslationCollection('Testlanguage');
        $collection->addMoveName(123, 'Movename in testlanguage');
        $collection->addTypeName('TYPENAME', 'Typename in testlanguage');
        $collection->addPokemonName(5, 'Pokemon 5 in testlanguage');
        $collection->addPokemonMegaName(5, '001', 'Mega 1');
        $collection->addPokemonMegaName(5, '002', 'Mega 2');
        $collection->addPokemonFormName('POKEMON_FORM', 'Pokemon Form in testlanguage');
        $collection->addRegionalForm('regional_form_alola', 'alola');
        $collection->addCustomTranslation('customKey', 'Custom Translation');

        $this->assertSame('Testlanguage', $collection->getLanguageName());
        $this->assertSame('alola', $collection->getRegionalForm('regional_form_alola'));

        $this->assertSame('Movename in testlanguage', $collection->getMoveName(123));
        $this->assertNull($collection->getMoveName(1230));

        $this->assertSame('Typename in testlanguage', $collection->getTypeName('TYPENAME'));
        $this->assertSame('Pokemon 5 in testlanguage', $collection->getPokemonName(5));
        $this->assertSame([
            'Mega 1',
            'Mega 2',
        ], $collection->getPokemonMegaNames(5));
        $this->assertSame('Pokemon Form in testlanguage', $collection->getPokemonFormName('POKEMON_FORM'));
        $this->assertNull($collection->getPokemonFormName('FALLBACK'));
        $this->assertSame('Custom Translation', $collection->getCustomTranslation('customKey'));
    }
}
