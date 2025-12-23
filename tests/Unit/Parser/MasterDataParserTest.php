<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonAssetsCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\AttacksCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonCombatMove;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonCombatMoveBuffs;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonForms;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonMove;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonStats;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\TemporaryEvolution;
use PokemonGoApi\PogoAPI\Parser\MasterDataParser;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use function array_map;

#[CoversClass(MasterDataParser::class)]
#[UsesClass(AttacksCollection::class)]
#[UsesClass(PokemonCollection::class)]
#[UsesClass(PokemonCombatMove::class)]
#[UsesClass(PokemonMove::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(PokemonStats::class)]
#[UsesClass(PokemonCombatMoveBuffs::class)]
#[UsesClass(JsonParser::class)]
#[UsesClass(PokemonForm::class)]
#[UsesClass(PokemonForms::class)]
#[UsesClass(TemporaryEvolution::class)]
final class MasterDataParserTest extends TestCase
{
    public function testConstruct(): void
    {
        $sut = new MasterDataParser(new PokemonAssetsCollection());
        $this->assertEmpty($sut->getAttacksCollection()->toArray());
        $this->assertEmpty($sut->getPokemonCollection()->toArray());
    }

    public function testParseFileWithPokemons(): void
    {
        $sut = new MasterDataParser(new PokemonAssetsCollection());
        $sut->parseFile(__DIR__ . '/Fixtures/GAME_MASTER_LATEST.json');

        $pokemons = $sut->getPokemonCollection()->toArray();
        $this->assertArrayHasKey('MEOWTH', $pokemons);
        $meowth        = $pokemons['MEOWTH'];
        $pokemonMeowth = new Pokemon(
            52,
            'MEOWTH',
            'MEOWTH',
            PokemonType::normal(),
            PokemonType::none(),
        );

        foreach ($meowth->getPokemonRegionForms() as $form) {
            $pokemonMeowth = $pokemonMeowth->withAddedPokemonRegionForm($form);
        }

        $forms = array_map(
            static fn (Pokemon $form): string => $form->getFormId(),
            $meowth->getPokemonRegionForms(),
        );

        $this->assertSame(61, $meowth->getPokemonRegionForms()['MEOWTH_ALOLA']->getAssetsBundleId());
        $this->assertSame(31, $meowth->getPokemonRegionForms()['MEOWTH_GALARIAN']->getAssetsBundleId());

        $this->assertSame([
            'MEOWTH_ALOLA'    => 'MEOWTH_ALOLA',
            'MEOWTH_GALARIAN' => 'MEOWTH_GALARIAN',
        ], $forms);
        $this->assertEquals(new PokemonStats(120, 92, 78), $meowth->getStats());
        $this->assertSame(['SCRATCH_FAST', 'BITE_FAST'], $meowth->getQuickMoveNames());
        $this->assertSame(['NIGHT_SLASH', 'DARK_PULSE', 'FOUL_PLAY'], $meowth->getCinematicMoveNames());
        $this->assertSame(['BODY_SLAM'], $meowth->getEliteCinematicMoveNames());
    }

    public function testParseFileWithPokemonMoves(): void
    {
        $sut = new MasterDataParser(new PokemonAssetsCollection());
        $sut->parseFile(__DIR__ . '/Fixtures/GAME_MASTER_LATEST.json');

        $moves = $sut->getAttacksCollection()->toArray();
        $this->assertCount(2, $moves);
        $move49 = new PokemonMove(
            49,
            'BUG_BUZZ',
            PokemonType::bug(),
            90.0,
            -50.0,
            3700.0,
            false,
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
                -1,
            ),
        ));
        $this->assertEquals($move49, $moves['m49']);

        $move203 = new PokemonMove(
            203,
            'SUCKER_PUNCH_FAST',
            PokemonType::dark(),
            7.0,
            8.0,
            700.0,
            true,
        );
        $move203->setCombatMove(new PokemonCombatMove(5.0, 7.0, 1, null));
        $this->assertEquals($move203, $moves['m203']);
    }

    public function testParseFileWithTempoarayEvolutions(): void
    {
        $sut = new MasterDataParser(new PokemonAssetsCollection());
        $sut->parseFile(__DIR__ . '/Fixtures/GAME_MASTER_LATEST.json');

        $collection = $sut->getPokemonCollection()->toArray();
        $this->assertArrayHasKey('CHARIZARD', $collection);
        $charizard = $collection['CHARIZARD'];
        $this->assertCount(2, $charizard->getTemporaryEvolutions());
        $this->assertSame('CHARIZARD_MEGA_X', $charizard->getTemporaryEvolutions()[0]->getId());
        $this->assertSame(51, $charizard->getTemporaryEvolutions()[0]->getAssetsBundleId());
        $this->assertSame('CHARIZARD_MEGA_Y', $charizard->getTemporaryEvolutions()[1]->getId());
        $this->assertSame(52, $charizard->getTemporaryEvolutions()[1]->getAssetsBundleId());
    }
}
