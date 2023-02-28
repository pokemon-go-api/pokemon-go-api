<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchTaskQuest
{
    public function __construct(
        private string $translationKey,
        private int|null $replaceArgument,
        private bool $isEventTask,
    ) {
    }

    public function getReplaceArgument(): int|null
    {
        return $this->replaceArgument;
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    public function isEventTask(): bool
    {
        return $this->isEventTask;
    }
}
