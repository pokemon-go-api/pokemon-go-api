<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchTaskQuest
{
    private string $translationKey;
    private ?int $replaceArgument;
    private bool $isEventTask;

    public function __construct(
        string $translationKey,
        ?int $replaceArgument,
        bool $isEventTask
    ) {
        $this->translationKey  = $translationKey;
        $this->replaceArgument = $replaceArgument;
        $this->isEventTask     = $isEventTask;
    }

    public function getReplaceArgument(): ?int
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
