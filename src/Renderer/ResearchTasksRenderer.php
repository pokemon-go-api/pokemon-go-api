<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer;

use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoApi\PogoAPI\Types\PokemonStats;
use PokemonGoApi\PogoAPI\Types\ResearchTasks\ResearchRewardMegaEnergy;
use PokemonGoApi\PogoAPI\Types\ResearchTasks\ResearchRewardPokemon;
use PokemonGoApi\PogoAPI\Types\ResearchTasks\ResearchTask;
use PokemonGoApi\PogoAPI\Util\CpCalculator;

final class ResearchTasksRenderer
{
    private TranslationCollectionCollection $translations;

    private PokemonCollection $pokemonCollection;

    public function __construct(
        TranslationCollectionCollection $translations,
        PokemonCollection $pokemonCollection
    ) {
        $this->translations      = $translations;
        $this->pokemonCollection = $pokemonCollection;
    }

    /**
     * @return mixed[]
     */
    public function render(ResearchTask ...$researchTasks): array
    {
        $out = [];
        foreach ($researchTasks as $researchTask) {
            $questTranslations = [];
            foreach ($this->translations->getCollections() as $name => $translationCollection) {
                $questTranslations[$name] = $translationCollection->getQuest(
                    $researchTask->getResearchTaskQuest()->getTranslationKey(),
                    (string) $researchTask->getResearchTaskQuest()->getReplaceArgument()
                );

                if (! $researchTask->getResearchTaskQuest()->isEventTask()) {
                    continue;
                }

                $questTranslations[$name] .= ' (Event)';
            }

            $rewards = [];
            foreach ($researchTask->getRewards() as $reward) {
                if ($reward instanceof ResearchRewardMegaEnergy) {
                    $pokemon = $this->pokemonCollection->getByFormId($reward->getPokemonFormId());
                    if ($pokemon === null) {
                        continue;
                    }

                    $rewards[] = [
                        'type'   => 'MEGA_ENERGY',
                        'id'     => $pokemon->getId(),
                        'name'   => PokemonNameRenderer::renderPokemonNames($pokemon, $this->translations),
                        'energy' => $reward->getMegaEnergy(),
                    ];
                } elseif ($reward instanceof ResearchRewardPokemon) {
                    $pokemon = $this->pokemonCollection->getByFormId($reward->getPokemonFormId());
                    if ($pokemon === null) {
                        continue;
                    }

                    $pokemonStats = $pokemon->getStats() ?? new PokemonStats(0, 0, 0);

                    $pokemonImage = $pokemon->getPokemonImage();
                    $rewards[]    = [
                        'type'    => 'POKEMON',
                        'id'      => $pokemon->getId(),
                        'shiny'   => $reward->isShiny(),
                        'name'    => PokemonNameRenderer::renderPokemonNames($pokemon, $this->translations),
                        'cpRange' => [
                            CpCalculator::calculateQuestMinCp($pokemonStats),
                            CpCalculator::calculateQuestMaxCp($pokemonStats),
                        ],
                        'assets'  => $pokemonImage ? [
                            'image'      => $pokemonImage->buildUrl(false),
                            'shinyImage' => $pokemonImage->buildUrl(true),
                        ] : null,
                    ];
                }
            }

            $out[] = [
                'quest'   => $questTranslations,
                'rewards' => $rewards,
            ];
        }

        return $out;
    }
}
