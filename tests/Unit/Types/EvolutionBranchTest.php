<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\EvolutionBranch;

use function json_decode;

/**
 * @covers \PokemonGoApi\PogoAPI\Types\EvolutionBranch
 */
class EvolutionBranchTest extends TestCase
{
    public function testCreateSlowking(): void
    {
        $evolutionBranch = EvolutionBranch::createFromGameMaster(json_decode(<<<JSON
        {
            "evolution": "SLOWKING",
            "evolutionItemRequirement": "ITEM_KINGS_ROCK",
            "candyCost": 50,
            "form": "SLOWKING_NORMAL",
            "obPurificationEvolutionCandyCost": 45
        }
        JSON));

        self::assertSame('SLOWKING', $evolutionBranch->getEvolutionId());
        self::assertSame('SLOWKING_NORMAL', $evolutionBranch->getEvolutionFormId());
        self::assertSame(50, $evolutionBranch->getCandyCost());
        self::assertSame('ITEM_KINGS_ROCK', $evolutionBranch->getRequiredItem());
        self::assertEmpty($evolutionBranch->getQuestIds());
    }

    public function testCreateSlowkingGalarian(): void
    {
        $evolutionBranch = EvolutionBranch::createFromGameMaster(json_decode(<<<JSON
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

        self::assertSame('SLOWKING', $evolutionBranch->getEvolutionId());
        self::assertSame('SLOWKING_GALARIAN', $evolutionBranch->getEvolutionFormId());
        self::assertSame(50, $evolutionBranch->getCandyCost());
        self::assertNull($evolutionBranch->getRequiredItem());
        self::assertSame(['SLOWKING_G_EVOLUTION_QUEST'], $evolutionBranch->getQuestIds());
    }
}
