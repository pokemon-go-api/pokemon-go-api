<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchTaskQuest
{
    private string $translationKey;
    private ?int $replaceArgument;

    public function __construct(
        string $translationKey,
        ?int $replaceArgument
    ) {
        $this->translationKey  = $translationKey;
        $this->replaceArgument = $replaceArgument;
    }

    public function getReplaceArgument(): ?int
    {
        return $this->replaceArgument;
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }
}
