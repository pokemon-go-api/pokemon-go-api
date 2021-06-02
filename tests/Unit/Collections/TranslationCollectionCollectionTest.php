<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Collections;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\TranslationCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollectionCollection;

/**
 * @uses \PokemonGoApi\PogoAPI\Collections\TranslationCollection
 *
 * @covers \PokemonGoApi\PogoAPI\Collections\TranslationCollectionCollection
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
