<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\PokemonCollection;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollection;
use PokemonGoLingen\PogoAPI\Parser\SilphRoadResearchTaskParser;
use PokemonGoLingen\PogoAPI\Types\Pokemon;
use PokemonGoLingen\PogoAPI\Types\PokemonType;
use PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchReward;
use PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchTask;
use PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchTaskQuest;

/**
 * @uses \PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchTask
 * @uses \PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchTaskQuest
 * @uses \PokemonGoLingen\PogoAPI\Types\ResearchTasks\ResearchReward
 * @uses \PokemonGoLingen\PogoAPI\Collections\TranslationCollection
 * @uses \PokemonGoLingen\PogoAPI\Collections\PokemonCollection
 * @uses \PokemonGoLingen\PogoAPI\Types\Pokemon
 * @uses \PokemonGoLingen\PogoAPI\Types\PokemonType
 *
 * @covers \PokemonGoLingen\PogoAPI\Parser\SilphRoadResearchTaskParser
 */
class SilphRoadResearchTaskParserTest extends TestCase
{
    public function testParseTasks(): void
    {
        $translationCollection = new TranslationCollection('English');
        $translationCollection->addQuest('dummy_catch_fairy', 'Catch {0} Fairy-type Pokémon');
        $translationCollection->addQuest('dummy_level3_raid', 'Win a level 3 or higher raid');

        $pokemonCollection = new PokemonCollection();
        $pokemonCollection->add(new Pokemon(183, '183', '183', PokemonType::none(), null));
        $pokemonCollection->add(new Pokemon(546, '546', '546', PokemonType::none(), null));
        $pokemonCollection->add(new Pokemon(209, '209', '209', PokemonType::none(), null));
        $pokemonCollection->add(new Pokemon(138, '138', '138', PokemonType::none(), null));
        $pokemonCollection->add(new Pokemon(140, '140', '140', PokemonType::none(), null));
        $ponyta = new Pokemon(77, 'PONYTA', 'PONYTA', PokemonType::none(), null);
        $ponyta->addPokemonRegionForm(
            new Pokemon(77, 'PONYTA', 'PONYTA_GALARIAN', PokemonType::none(), null)
        );
        $pokemonCollection->add($ponyta);

        $sut = new SilphRoadResearchTaskParser(
            $pokemonCollection,
            $translationCollection
        );

        $tasks = $sut->parseTasks(__DIR__ . '/Fixtures/silphroad_researchtasks.html');

        self::assertEquals([
            new ResearchTask(
                new ResearchTaskQuest('dummy_catch_fairy', 15),
                new ResearchReward('PONYTA_GALARIAN', false)
            ),
            new ResearchTask(
                new ResearchTaskQuest('dummy_catch_fairy', 5),
                new ResearchReward('183', true),
                new ResearchReward('546', false),
                new ResearchReward('209', true)
            ),
            new ResearchTask(
                new ResearchTaskQuest('dummy_level3_raid', null),
                new ResearchReward('138', true),
                new ResearchReward('140', true)
            ),
        ], $tasks);
    }
}
