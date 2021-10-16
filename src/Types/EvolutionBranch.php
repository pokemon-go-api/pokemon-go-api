<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use stdClass;

final class EvolutionBranch
{
    private string $evolutionId;
    private string $evolutionFormId;
    private int $candyCost;
    private ?string $requiredItem;
    /** @var list<string> */
    private array $questIds = [];

    public static function createFromGameMaster(stdClass $evolutionBranch): self
    {
        $self                  = new self();
        $self->evolutionId     = $evolutionBranch->evolution;
        $self->evolutionFormId = $evolutionBranch->form ?? $evolutionBranch->evolution;
        $self->candyCost       = $evolutionBranch->candyCost;
        $self->requiredItem    = $evolutionBranch->evolutionItemRequirement ?? null;
        foreach ($evolutionBranch->questDisplay ?? [] as $quest) {
            $self->questIds[] = $quest->questRequirementTemplateId;
        }

        return $self;
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

    public function getRequiredItem(): ?string
    {
        return $this->requiredItem;
    }

    /** @return list<string> */
    public function getQuestIds(): array
    {
        return $this->questIds;
    }
}
