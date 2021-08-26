<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonAssetsCollection;
use PokemonGoApi\PogoAPI\Parser\MasterDataParser;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonCombatMove;
use PokemonGoApi\PogoAPI\Types\PokemonCombatMoveBuffs;
use PokemonGoApi\PogoAPI\Types\PokemonMove;
use PokemonGoApi\PogoAPI\Types\PokemonStats;
use PokemonGoApi\PogoAPI\Types\PokemonType;

use function array_map;
use function assert;

/**
 * @uses   \PokemonGoApi\PogoAPI\Collections\AttacksCollection
 * @uses   \PokemonGoApi\PogoAPI\Collections\PokemonCollection
 * @uses   \PokemonGoApi\PogoAPI\Types\PokemonCombatMove
 * @uses   \PokemonGoApi\PogoAPI\Types\PokemonMove
 * @uses   \PokemonGoApi\PogoAPI\Types\PokemonType
 * @uses   \PokemonGoApi\PogoAPI\Types\Pokemon
 * @uses   \PokemonGoApi\PogoAPI\Types\PokemonStats
 * @uses   \PokemonGoApi\PogoAPI\Types\PokemonCombatMoveBuffs
 * @uses   \PokemonGoLingen\PogoAPI\IO\JsonParser
 * @uses   \PokemonGoLingen\PogoAPI\Types\PokemonForm
 * @uses   \PokemonGoLingen\PogoAPI\Types\PokemonFormCollection
 * @uses   \PokemonGoLingen\PogoAPI\Types\TemporaryEvolution
 *
 * @covers \PokemonGoApi\PogoAPI\Parser\MasterDataParser
 */
class MasterDataParserTest extends TestCase
{
    public function testConstruct(): void
    {
        $sut = new MasterDataParser(new PokemonAssetsCollection());
        self::assertEmpty($sut->getAttacksCollection()->toArray());
        self::assertEmpty($sut->getPokemonCollection()->toArray());
    }

    public function testParseFileWithPokemons(): void
    {
        $sut = new MasterDataParser(new PokemonAssetsCollection());
        $sut->parseFile(__DIR__ . '/Fixtures/GAME_MASTER_LATEST.json');

        $pokemons = $sut->getPokemonCollection()->toArray();
        self::assertArrayHasKey('MEOWTH', $pokemons);
        $meowth        = $pokemons['MEOWTH'];
        $pokemonMeowth = new Pokemon(
            52,
            'MEOWTH',
            'MEOWTH',
            PokemonType::normal(),
            PokemonType::none()
        );

        foreach ($meowth->getPokemonRegionForms() as $form) {
            $pokemonMeowth = $pokemonMeowth->withAddedPokemonRegionForm($form);
        }

        $forms = array_map(
            static fn (Pokemon $form): string => $form->getFormId(),
            $meowth->getPokemonRegionForms()
        );

        self::assertSame(61, $meowth->getPokemonRegionForms()['MEOWTH_ALOLA']->getAssetsBundleId());
        self::assertSame(31, $meowth->getPokemonRegionForms()['MEOWTH_GALARIAN']->getAssetsBundleId());

        self::assertEquals([
            'MEOWTH_ALOLA'    => 'MEOWTH_ALOLA',
            'MEOWTH_GALARIAN' => 'MEOWTH_GALARIAN',
        ], $forms);
        self::assertEquals(new PokemonStats(120, 92, 78), $meowth->getStats());
        self::assertEquals(['SCRATCH_FAST', 'BITE_FAST'], $meowth->getQuickMoveNames());
        self::assertEquals(['NIGHT_SLASH', 'DARK_PULSE', 'FOUL_PLAY'], $meowth->getCinematicMoveNames());
        self::assertEquals(['BODY_SLAM'], $meowth->getEliteCinematicMoveNames());
    }

    public function testParseFileWithPokemonMoves(): void
    {
        $sut = new MasterDataParser(new PokemonAssetsCollection());
        $sut->parseFile(__DIR__ . '/Fixtures/GAME_MASTER_LATEST.json');

        $moves = $sut->getAttacksCollection()->toArray();
        self::assertCount(2, $moves);
        $move49 = new PokemonMove(
            49,
            'BUG_BUZZ',
            PokemonType::bug(),
            90.0,
            -50.0,
            3700.0,
            false
        );
        $move49->setCombatMove(new PokemonCombatMove(
            90.0,
            -60.0,
            0,
            new PokemonCombatMoveBuffs(
                30,
                null,
                null,
                null,
                -1
            )
        ));
        self::assertEquals($move49, $moves['m49']);

        $move203 = new PokemonMove(
            203,
            'SUCKER_PUNCH_FAST',
            PokemonType::dark(),
            7.0,
            8.0,
            700.0,
            true
        );
        $move203->setCombatMove(new PokemonCombatMove(5.0, 7.0, 1, null));
        self::assertEquals($move203, $moves['m203']);
    }

    public function testParseFileWithTempoarayEvolutions(): void
    {
        $sut = new MasterDataParser(new PokemonAssetsCollection());
        $sut->parseFile(__DIR__ . '/Fixtures/GAME_MASTER_LATEST.json');

        $collection = $sut->getPokemonCollection()->toArray();
        self::assertArrayHasKey('CHARIZARD', $collection);
        $charizard = $collection['CHARIZARD'];
        assert($charizard instanceof Pokemon);
        self::assertCount(2, $charizard->getTemporaryEvolutions());
        self::assertSame('CHARIZARD_MEGA_X', $charizard->getTemporaryEvolutions()[0]->getId());
        self::assertSame(51, $charizard->getTemporaryEvolutions()[0]->getAssetsBundleId());
        self::assertSame('CHARIZARD_MEGA_Y', $charizard->getTemporaryEvolutions()[1]->getId());
        self::assertSame(52, $charizard->getTemporaryEvolutions()[1]->getAssetsBundleId());
    }
}
