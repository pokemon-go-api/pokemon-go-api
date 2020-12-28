<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Collections;

final class TranslationCollectionCollection
{
    /** @var array<string, TranslationCollection> */
    private array $collections = [];

    public function addTranslationCollection(TranslationCollection $translationCollection): void
    {
        $this->collections[$translationCollection->getLanguageName()] = $translationCollection;
    }

    /**
     * @return array<string, TranslationCollection>
     */
    public function getCollections(): array
    {
        return $this->collections;
    }
}
