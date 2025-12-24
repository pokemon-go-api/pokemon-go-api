<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;

final class EvolutionQuest
{
    /** @param array{ questTemplateId: string, questType: string, goals: list<array{target: int}>, display: array{title: string} } $evolutionQuestTemplate */
    #[Constructor]
    public static function fromArray(
        array $evolutionQuestTemplate,
    ): self {
        return new self(
            $evolutionQuestTemplate['questTemplateId'],
            $evolutionQuestTemplate['questType'],
            $evolutionQuestTemplate['goals'][0]['target'] ?? 0,
            $evolutionQuestTemplate['display']['title'],
        );
    }

    public function __construct(
        private string $questId,
        private string $questType,
        private int $goalTarget,
        private string $goalTranslationId,
    ) {
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
