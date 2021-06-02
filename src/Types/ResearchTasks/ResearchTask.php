<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types\ResearchTasks;

final class ResearchTask
{
    private ResearchTaskQuest $researchTaskQuest;
    /** @var ResearchReward[] */
    private array $rewards;

    public function __construct(
        ResearchTaskQuest $researchTaskQuest,
        ResearchReward ...$rewards
    ) {
        $this->researchTaskQuest = $researchTaskQuest;
        $this->rewards           = $rewards;
    }

    public function getResearchTaskQuest(): ResearchTaskQuest
    {
        return $this->researchTaskQuest;
    }

    /**
     * @return ResearchReward[]
     */
    public function getRewards(): array
    {
        return $this->rewards;
    }
}
