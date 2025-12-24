<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer;

use PokemonGoApi\PogoAPI\Collections\TranslationCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoApi\PogoAPI\Parser\CustomTranslations;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonForm;

use function array_shift;
use function is_numeric;
use function sprintf;
use function str_replace;
use function strtolower;
use function ucfirst;

class PokemonNameRenderer
{
    /** @return array<string, string|null> */
    public static function renderPokemonNames(
        Pokemon $pokemon,
        TranslationCollectionCollection $translationCollectionCollection,
    ): array {
        $names = [];
        foreach ($translationCollectionCollection->getCollections() as $translationCollection) {
            $names[$translationCollection->getLanguageName()] = self::renderPokemonName(
                $pokemon,
                $translationCollection,
            );
        }

        return $names;
    }

    public static function renderPokemonName(Pokemon $pokemon, TranslationCollection $translationCollection): string
    {
        $pokemonName = $translationCollection->getPokemonName($pokemon->getDexNr());

        $pokemonFormName = $translationCollection->getPokemonFormName($pokemon->getFormId());
        if ($pokemonFormName !== null) {
            $pokemonName = sprintf('%s (%s)', $pokemonName, $pokemonFormName);
        } else {
            $singleFormOnly  = str_replace($pokemon->getId() . '_', '', $pokemon->getFormId());
            $pokemonFormName = $translationCollection->getPokemonFormName($singleFormOnly);
            if ($pokemonFormName !== null) {
                $pokemonName = sprintf('%s (%s)', $pokemonName, $pokemonFormName);
            }
        }

        $pokemonForm = $pokemon->getPokemonForm();
        if ($pokemonForm instanceof PokemonForm) {
            if ($pokemonForm->isAlola()) {
                $pokemonName = sprintf(
                    $translationCollection->getRegionalForm(CustomTranslations::REGIONFORM_ALOLAN) ?: '%s',
                    $pokemonName,
                );
            }

            if ($pokemonForm->isGalarian()) {
                $pokemonName = sprintf(
                    $translationCollection->getRegionalForm(CustomTranslations::REGIONFORM_GALARIAN) ?: '%s',
                    $pokemonName,
                );
            }

            if ($pokemonForm->isHisuian()) {
                $pokemonName = sprintf(
                    $translationCollection->getRegionalForm(CustomTranslations::REGIONFORM_HISUIAN) ?: '%s',
                    $pokemonName,
                );
            }
        }

        $pokemonFallbackName = $pokemon->getId();
        if (is_numeric($pokemonFallbackName)) {
            $pokemonFallbackName = ucfirst(strtolower($singleFormOnly ?? $pokemon->getFormId()));
        }

        return $pokemonName ?? $pokemonFallbackName;
    }

    public static function renderPokemonMegaName(
        Pokemon $pokemon,
        string $megaEvolutionId,
        TranslationCollection $translationCollection,
    ): string|null {
        $megaNamesByPokemonId = [];
        $megaNames            = $translationCollection->getPokemonMegaNames($pokemon->getDexNr());

        foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
            $megaEvolutionName                                                                   = array_shift($megaNames);
            $megaNamesByPokemonId[$temporaryEvolution->getTempEvoId()]                           = $megaEvolutionName;
            $megaNamesByPokemonId[$pokemon->getId() . '_' . $temporaryEvolution->getTempEvoId()] = $megaEvolutionName;
            $megaNamesByPokemonId[$temporaryEvolution->getId()]                                  = $megaEvolutionName;
        }

        return $megaNamesByPokemonId[$megaEvolutionId] ?? null;
    }
}
