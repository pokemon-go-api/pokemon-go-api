<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Renderer;

use PokemonGoLingen\PogoAPI\Collections\TranslationCollection;
use PokemonGoLingen\PogoAPI\Parser\CustomTranslations;
use PokemonGoLingen\PogoAPI\Types\Pokemon;

use function array_shift;
use function sprintf;
use function str_replace;

class PokemonNameRenderer
{
    public static function renderPokemonName(Pokemon $pokemon, TranslationCollection $translationCollection): ?string
    {
        $pokemonName = $translationCollection->getPokemonName($pokemon->getDexNr());

        $pokemonFormName = $translationCollection->getPokemonFormName($pokemon->getFormId());
        if ($pokemonFormName !== null) {
            $pokemonName = sprintf('%s (%s)', $pokemonName, $pokemonFormName);
        }

        $singleFormOnly  = str_replace($pokemon->getId() . '_', '', $pokemon->getFormId());
        $pokemonFormName = $translationCollection->getPokemonFormName($singleFormOnly);
        if ($pokemonFormName !== null) {
            $pokemonName = sprintf('%s (%s)', $pokemonName, $pokemonFormName);
        }

        $pokemonForm = $pokemon->getPokemonForm();
        if ($pokemonForm !== null) {
            if ($pokemonForm->isAlola()) {
                $pokemonName = sprintf(
                    $translationCollection->getRegionalForm(CustomTranslations::REGIONFORM_ALOLAN) ?: '%s',
                    $pokemonName
                );
            }

            if ($pokemonForm->isGalarian()) {
                $pokemonName = sprintf(
                    $translationCollection->getRegionalForm(CustomTranslations::REGIONFORM_GALARIAN) ?: '%s',
                    $pokemonName
                );
            }
        }

        return $pokemonName;
    }

    public static function renderPokemonMegaName(
        Pokemon $pokemon,
        string $megaEvolutionId,
        TranslationCollection $translationCollection
    ): ?string {
        $megaNamesByPokemonId = [];
        $megaNames            = $translationCollection->getPokemonMegaNames($pokemon->getDexNr());

        foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
            $megaEvolutionName                                                            = array_shift($megaNames);
            $megaNamesByPokemonId[$pokemon->getId() . '_' . $temporaryEvolution->getId()] = $megaEvolutionName;
            $megaNamesByPokemonId[$temporaryEvolution->getId()]                           = $megaEvolutionName;
        }

        return $megaNamesByPokemonId[$megaEvolutionId] ?? null;
    }
}
