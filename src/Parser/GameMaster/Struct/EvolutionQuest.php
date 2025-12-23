<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use stdClass;

final class EvolutionQuest
{
    private string $questId;

    private string $questType;

    private int $goalTarget;

    private string $goalTranslationId;

    /**
     * @param array{
     *     questTemplateId: string,
     *     questType: string,
     *     goals: list<array{target: int}>,
     *     display: array{title: string}
     * } $evolutionQuestTemplate
     */
    public function __construct(
        array $evolutionQuestTemplate
    )
    {
        $this->questId = $evolutionQuestTemplate['questTemplateId'];
        $this->questType = $evolutionQuestTemplate['questType'];
        $this->goalTarget = $evolutionQuestTemplate['goals'][0]['target'] ?? 0;
        $this->goalTranslationId = $evolutionQuestTemplate['display']['title'];
    }

    public function getQuestId(): string
    {
        return $this->questId;
    }

    public function getQuestType(): string
    {
        return $this->questType;
    }

    public function getGoalTarget(): int
    {
        return $this->goalTarget;
    }

    public function getGoalTranslationId(): string
    {
        return $this->goalTranslationId;
    }
}
