<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

final class EvolutionBranch
{
    private string $evolutionId;

    private string $evolutionFormId;

    private int $candyCost;

    private string|int|null $requiredItem = null;

    /** @var list<string> */
    private array $questIds = [];

    /** @param list<array{questRequirementTemplateId: string}> $questDisplay */
    public function __construct(
        string $evolution,
        int|null $candyCost,
        string|null $form,
        string|null $evolutionItemRequirement,
        array|null $questDisplay,
    ) {
        $this->evolutionId     = $evolution;
        $this->evolutionFormId = $form ?? $evolution;
        $this->candyCost       = $candyCost ?? 0;
        $this->requiredItem    = $evolutionItemRequirement ?? null;
        if ($questDisplay === null) {
            return;
        }

        foreach ($questDisplay as $questDisplayItem) {
            $this->questIds[] = $questDisplayItem['questRequirementTemplateId'];
        }
    }

    public function getEvolutionId(): string
    {
        return $this->evolutionId;
    }

    public function getEvolutionFormId(): string
    {
        return $this->evolutionFormId;
    }

    public function getCandyCost(): int
    {
        return $this->candyCost;
    }

    public function getRequiredItem(): string|int|null
    {
        return $this->requiredItem;
    }

    /** @return list<string> */
    public function getQuestIds(): array
    {
        return $this->questIds;
    }
}
