<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Collections;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollection;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollectionCollection;

/**
 * @uses \PokemonGoLingen\PogoAPI\Collections\TranslationCollection
 *
 * @covers \PokemonGoLingen\PogoAPI\Collections\TranslationCollectionCollection
 */
class TranslationCollectionCollectionTest extends TestCase
{
    public function testAddCollection(): void
    {
        $translationCollection = new TranslationCollection('dummyLanguage');
        $sut                   = new TranslationCollectionCollection();
        $sut->addTranslationCollection($translationCollection);
        self::assertCount(1, $sut->getCollections());
        $sut->addTranslationCollection($translationCollection);
        self::assertCount(1, $sut->getCollections());
    }
}
