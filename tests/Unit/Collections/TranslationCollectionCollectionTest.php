<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Collections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\TranslationCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollectionCollection;

#[CoversClass(TranslationCollectionCollection::class)]
#[UsesClass(TranslationCollection::class)]
class TranslationCollectionCollectionTest extends TestCase
{
    public function testAddCollection(): void
    {
        $translationCollection = new TranslationCollection('dummyLanguage');
        $sut                   = new TranslationCollectionCollection();
        $sut->addTranslationCollection($translationCollection);
        $this->assertCount(1, $sut->getCollections());
        $sut->addTranslationCollection($translationCollection);
        $this->assertCount(1, $sut->getCollections());
    }
}
