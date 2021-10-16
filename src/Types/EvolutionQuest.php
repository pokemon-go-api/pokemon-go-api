<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use stdClass;

final class EvolutionQuest
{
    private string $questId;
    private string $questType;
    private int $goalTarget;
    private string $goalTranslationId;

    public static function createFromGameMaster(stdClass $evolutionQuest): self
    {
        $self                    = new self();
        $self->questId           = $evolutionQuest->evolutionQuestTemplate->questTemplateId;
        $self->questType         = $evolutionQuest->evolutionQuestTemplate->questType;
        $self->goalTarget        = $evolutionQuest->evolutionQuestTemplate->goals[0]->target;
        $self->goalTranslationId = $evolutionQuest->evolutionQuestTemplate->display->title;

        return $self;
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
