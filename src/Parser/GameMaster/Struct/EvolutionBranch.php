<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;

final class EvolutionBranch
{
    public function __construct(
        private string $evolutionId,
        private string $evolutionFormId,
        private int $candyCost,
        private string|null $requiredItem,
        /** @var list<string> */
        private array $questIds = [],
    ) {
    }

    /** @param list<array{questRequirementTemplateId: string}> $questDisplay */
    #[Constructor]
    public static function fromArray(
        string $evolution,
        int|null $candyCost,
        string|null $form,
        string|null $evolutionItemRequirement,
        array|null $questDisplay,
    ): self {
        $questIds = [];
        if ($questDisplay !== null) {
            foreach ($questDisplay as $questDisplayItem) {
                $questIds[] = $questDisplayItem['questRequirementTemplateId'];
            }
        }

        return new self(
            $evolution,
            $form ?? $evolution,
            $candyCost ?? 0,
            $evolutionItemRequirement ?? null,
            $questIds,
        );
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

    public function getRequiredItem(): string|null
    {
        return $this->requiredItem;
    }

    /** @return list<string> */
    public function getQuestIds(): array
    {
        return $this->questIds;
    }
}
