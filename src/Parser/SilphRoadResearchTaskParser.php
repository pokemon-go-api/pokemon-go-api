<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Parser;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollection;
use PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchReward;
use PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchTask;
use PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchTaskQuest;

use function assert;
use function count;
use function explode;
use function preg_match;
use function preg_replace;
use function stripos;
use function strpos;
use function strtoupper;
use function trim;

class SilphRoadResearchTaskParser
{
    private PokemonCollection $pokemonCollection;
    private TranslationCollection $englishTranslationCollection;

    public function __construct(
        PokemonCollection $pokemonCollection,
        TranslationCollection $englishTranslationCollection
    ) {
        $this->pokemonCollection            = $pokemonCollection;
        $this->englishTranslationCollection = $englishTranslationCollection;
    }

    /**
     * @return ResearchTask[]
     */
    public function parseTasks(string $htmlPage): array
    {
        $domDocument = new DOMDocument();
        @$domDocument->loadHTMLFile($htmlPage);
        $xpath      = new DOMXPath($domDocument);
        $tasksItems = $xpath->query(
            '//*[@id="taskGroupWraps"]//*[contains(@class, "task")][contains(@class, "pkmn")]'
        );
        assert($tasksItems instanceof DOMNodeList);

        $tasks = [];

        foreach ($tasksItems as $taskContainer) {
            assert($taskContainer instanceof DOMElement);
            $taskText = $xpath->query('*[@class="taskText"]', $taskContainer);
            assert($taskText instanceof DOMNodeList);
            $taskMessage = $taskText[0]->textContent;

            $quest = $this->findQuest($taskMessage);
            if ($quest === null) {
                continue;
            }

            $rewards     = [];
            $rewardsList = $xpath->query(
                '*/*[contains(@class, "task-reward")][contains(@class, "pokemon")]',
                $taskContainer
            );
            assert($rewardsList instanceof DOMNodeList);
            foreach ($rewardsList as $rewardItem) {
                assert($rewardItem instanceof DOMElement);
                $shiny        = strpos($rewardItem->getAttribute('class'), 'shinyAvailable') !== false;
                $pokemonImage = $rewardItem->getElementsByTagName('img')[0];
                if ($pokemonImage === null) {
                    continue;
                }

                assert($pokemonImage instanceof DOMElement);
                $imageSrc  = $pokemonImage->getAttribute('src');
                $pokemonId = $this->getPokemonIdFromImage($imageSrc);
                if ($pokemonId === null) {
                    continue;
                }

                $rewards[] = new ResearchReward($pokemonId, $shiny);
            }

            if (count($rewards) === 0) {
                continue;
            }

            $tasks[] = new ResearchTask(
                $quest,
                ...$rewards
            );
        }

        return $tasks;
    }

    private function findQuest(string $searchQuestName): ?ResearchTaskQuest
    {
        $searchQuestName = trim($searchQuestName, '. ');
        $matches         = [];
        preg_match('~\s(?<count>\d+)\s~', $searchQuestName, $matches);
        $searchQuestNameReplaced = preg_replace('~\s(?<count>\d+)\s~', ' {0} ', $searchQuestName);

        $quests = $this->englishTranslationCollection->getQuests();
        foreach ($quests as $questKey => $questName) {
            if ($questName === $searchQuestName) {
                return new ResearchTaskQuest($questKey, null);
            }

            if ($questName === $searchQuestNameReplaced) {
                return new ResearchTaskQuest($questKey, (int) $matches['count']);
            }
        }

        return null;
    }

    private function getPokemonIdFromImage(string $imageSrc): ?string
    {
        if (preg_match('~/(\d+)\.png$~', $imageSrc, $match)) {
            $pkmDexNr     = (int) $match[1];
            $pokemonByDex = $this->pokemonCollection->getByDexId($pkmDexNr);
            if ($pokemonByDex === null) {
                return null;
            }

            return $pokemonByDex->getFormId();
        }

        if (preg_match('~/96x96/(.*?).png$~', $imageSrc, $match)) {
            $imageName     = explode('-', strtoupper($match[1]));
            $pokemonByName = $this->pokemonCollection->get($imageName[0]);
            if ($pokemonByName === null) {
                return null;
            }

            $pokemon = $pokemonByName->getFormId();
            foreach ($pokemonByName->getPokemonRegionForms() as $pokemonForm) {
                if (stripos($pokemonForm->getFormId(), $imageName[1]) === false) {
                    continue;
                }

                $pokemon = $pokemonForm->getFormId();
            }

            return $pokemon;
        }

        return null;
    }
}
