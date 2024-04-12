<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\EvolutionBranch;

use function json_decode;

#[CoversClass(EvolutionBranch::class)]
class EvolutionBranchTest extends TestCase
{
    public function testCreateSlowking(): void
    {
        $evolutionBranch = EvolutionBranch::createFromGameMaster(json_decode(<<<'JSON'
        {
            "evolution": "SLOWKING",
            "evolutionItemRequirement": "ITEM_KINGS_ROCK",
            "candyCost": 50,
            "form": "SLOWKING_NORMAL",
            "obPurificationEvolutionCandyCost": 45
        }
        JSON));

        $this->assertSame('SLOWKING', $evolutionBranch->getEvolutionId());
        $this->assertSame('SLOWKING_NORMAL', $evolutionBranch->getEvolutionFormId());
        $this->assertSame(50, $evolutionBranch->getCandyCost());
        $this->assertSame('ITEM_KINGS_ROCK', $evolutionBranch->getRequiredItem());
        $this->assertEmpty($evolutionBranch->getQuestIds());
    }

    public function testCreateSlowkingGalarian(): void
    {
        $evolutionBranch = EvolutionBranch::createFromGameMaster(json_decode(<<<'JSON'
        {
            "evolution": "SLOWKING",
            "candyCost": 50,
            "form": "SLOWKING_GALARIAN",
            "priority": 1,
            "questDisplay": [
              {
                "questRequirementTemplateId": "SLOWKING_G_EVOLUTION_QUEST"
              }
            ],
            "obPurificationEvolutionCandyCost": 45
        }
        JSON));

        $this->assertSame('SLOWKING', $evolutionBranch->getEvolutionId());
        $this->assertSame('SLOWKING_GALARIAN', $evolutionBranch->getEvolutionFormId());
        $this->assertSame(50, $evolutionBranch->getCandyCost());
        $this->assertNull($evolutionBranch->getRequiredItem());
        $this->assertSame(['SLOWKING_G_EVOLUTION_QUEST'], $evolutionBranch->getQuestIds());
    }
}
