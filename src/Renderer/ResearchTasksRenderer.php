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

use function str_replace;

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
                $questTranslations[$name] = str_replace(
                    '{0}',
                    (string) $researchTask->getResearchTaskQuest()->getReplaceArgument(),
                    $translationCollection->getQuest($researchTask->getResearchTaskQuest()->getTranslationKey()) ?? ''
                );
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
                        'id'     => $pokemon->getFormId(),
                        'name'   => PokemonNameRenderer::renderPokemonNames($pokemon, $this->translations),
                        'energy' => $reward->getMegaEnergy(),
                    ];
                } else if ($reward instanceof ResearchRewardPokemon) {
                    $pokemon = $this->pokemonCollection->getByFormId($reward->getPokemonFormId());
                    if ($pokemon === null) {
                        continue;
                    }
                    $pokemonStats = $pokemon->getStats() ?? new PokemonStats(0, 0, 0);
                    $rewards[]    = [
                        'type'    => 'POKEMON',
                        'id'      => $pokemon->getFormId(),
                        'shiny'   => $reward->isShiny(),
                        'name'    => PokemonNameRenderer::renderPokemonNames($pokemon, $this->translations),
                        'cpRange' => [
                            CpCalculator::calculateQuestMinCp($pokemonStats),
                            CpCalculator::calculateQuestMaxCp($pokemonStats),
                        ],
                        'assets'  => [
                            'image'      => $pokemon->getPokemonImage()->buildUrl(false),
                            'shinyImage' => $pokemon->getPokemonImage()->buildUrl(true),
                        ],
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
