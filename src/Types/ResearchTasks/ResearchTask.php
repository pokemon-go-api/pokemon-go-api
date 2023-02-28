<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchTask
{
    /** @var ResearchReward[] */
    private array $rewards;

    public function __construct(
        private ResearchTaskQuest $researchTaskQuest,
        ResearchReward ...$rewards,
    ) {
        $this->rewards = $rewards;
    }

    public function getResearchTaskQuest(): ResearchTaskQuest
    {
        return $this->researchTaskQuest;
    }

    /** @return ResearchReward[] */
    public function getRewards(): array
    {
        return $this->rewards;
    }
}
