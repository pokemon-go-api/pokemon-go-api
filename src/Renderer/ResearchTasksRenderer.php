<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Renderer;

use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoLingen\PogoAPI\Types\PokemonStats;
use PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchTask;
use PokemonGoLingen\PogoAPI\Util\CpCalculator;

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
                    $translationCollection->getQuest($researchTask->getResearchTaskQuest()->getTranslationKey())
                );
            }

            $rewards = [];
            foreach ($researchTask->getRewards() as $reward) {
                $pokemon = $this->pokemonCollection->getByFormId($reward->getPokemonFormId());
                if ($pokemon === null) {
                    continue;
                }

                $pokemonStats = $pokemon->getStats() ?? new PokemonStats(0, 0, 0);
                $rewards[]    = [
                    'id'   => $pokemon->getFormId(),
                    'shiny'     => $reward->isShiny(),
                    'name' => PokemonNameRenderer::renderPokemonNames($pokemon, $this->translations),
                    'cpRange' => [
                        CpCalculator::calculateQuestMinCp($pokemonStats),
                        CpCalculator::calculateQuestMaxCp($pokemonStats),
                    ],
                    'assets' => [
                        'image'      => $pokemon->getPokemonImage()->buildUrl(false),
                        'shinyImage' => $pokemon->getPokemonImage()->buildUrl(true),
                    ],
                ];
            }

            $out[] = [
                'quest'   => $questTranslations,
                'rewards' => $rewards,
            ];
        }

        return $out;
    }
}
