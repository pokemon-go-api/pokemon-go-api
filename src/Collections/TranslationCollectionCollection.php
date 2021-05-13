<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Collections;

use Exception;

final class TranslationCollectionCollection
{
    /** @var array<string, TranslationCollection> */
    private array $collections = [];

    public function addTranslationCollection(TranslationCollection $translationCollection): void
    {
        $this->collections[$translationCollection->getLanguageName()] = $translationCollection;
    }

    public function getCollection(string $language): TranslationCollection
    {
        if (! isset($this->collections[$language])) {
            throw new Exception('Language not found', 1620668128817);
        }

        return $this->collections[$language];
    }

    /**
     * @return array<string, TranslationCollection>
     */
    public function getCollections(): array
    {
        return $this->collections;
    }
}
