<?php

declare(strict_types=1);

namespace Tests\Integration\PokemonGoLingen\PogoAPI;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Parser\CustomTranslations;
use PokemonGoApi\PogoAPI\Parser\LeekduckParser;
use PokemonGoApi\PogoAPI\Parser\MasterDataParser;
use PokemonGoApi\PogoAPI\Parser\PokemonGoImagesParser;
use PokemonGoApi\PogoAPI\Parser\TranslationParser;
use PokemonGoApi\PogoAPI\Renderer\RaidBossGraphicRenderer;
use PokemonGoApi\PogoAPI\Renderer\Types\RaidBossGraphicConfig;

use function file_put_contents;

/**
 * @coversNothing
 */
class RaidBossListTest extends TestCase
{
    /**
     * @depends testRenderDummyRaidList
     */
    public function testRenderDummyList(RaidBossCollection $raidBossCollection): void
    {
        $translationLoader     = new TranslationParser();
        $translationCollection = $translationLoader->loadLanguage(
            'English',
            __DIR__ . '/../../data/tmp/latest_apk_English.txt',
            __DIR__ . '/../../data/tmp/latest_remote_English.txt',
            CustomTranslations::load()['English']
        );

        $renderer        = new RaidBossGraphicRenderer();
        $raidBossGraphic = $renderer->buildGraphic(
            $raidBossCollection,
            $translationCollection,
            new RaidBossGraphicConfig(
                RaidBossGraphicConfig::ORDER_MEGA_TO_LVL1,
                true,
                __DIR__ . '/Fixtures/radilistTemplate.phtml'
            )
        );
        file_put_contents(__DIR__ . '/../../data/tmp/raidBossListTest.svg', $raidBossGraphic->getImageContent());
        self::assertFileEquals(
            __DIR__ . '/Fixtures/expected_raidlist.svg',
            __DIR__ . '/../../data/tmp/raidBossListTest.svg'
        );
    }

    public function testRenderDummyRaidList(): RaidBossCollection
    {
        $pokemonImages = __DIR__ . '/../../data/tmp/pokemon_images.json';
        $gameMaster    = __DIR__ . '/../../data/tmp/GAME_MASTER_LATEST.json';

        $pokemonImagesParser     = new PokemonGoImagesParser();
        $pokemonAssetsCollection = $pokemonImagesParser->parseFile($pokemonImages);

        $masterData = new MasterDataParser($pokemonAssetsCollection);
        $masterData->parseFile($gameMaster);

        $leekduckParser = new LeekduckParser($masterData->getPokemonCollection());
        $raidBosses     = $leekduckParser->parseRaidBosses(
            __DIR__ . '/Fixtures/leekduck_bosses.html'
        );

        $generatedRaidBosses = [];
        foreach ($raidBosses->toArray() as $raidBoss) {
            $image = $raidBoss->getPokemonImage();
            if ($image === null) {
                continue;
            }

            $generatedRaidBosses[] = [
                'dexNr' => $raidBoss->getPokemon()->getDexNr(),
                'pokemonID' => $raidBoss->getPokemon()->getId(),
                'formID' => $raidBoss->getPokemonWithMegaFormId(),
                'image' => $image->buildUrl(false),
            ];
        }

        self::assertSame([
            [
                'dexNr' => 6,
                'pokemonID' => 'CHARIZARD',
                'formID' => 'CHARIZARD_MEGA_Y',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets/pm6.fMEGA_Y.icon.png',
            ],
            [
                'dexNr' => 18,
                'pokemonID' => 'PIDGEOT',
                'formID' => 'PIDGEOT_MEGA',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets/pm18.fMEGA.icon.png',
            ],
            [
                'dexNr' => 460,
                'pokemonID' => 'ABOMASNOW',
                'formID' => 'ABOMASNOW_MEGA',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_460_51.png',
            ],
            [
                'dexNr' => 25,
                'pokemonID' => 'PIKACHU',
                'formID' => 'PIKACHU',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets/pm25.icon.png',
            ],
            [
                'dexNr' => 25,
                'pokemonID' => 'PIKACHU',
                'formID' => 'PIKACHU',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_pm0025_01_pgo_5thanniversary.png',
            ],
            [
                'dexNr' => 26,
                'pokemonID' => 'RAICHU',
                'formID' => 'RAICHU_ALOLA',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets/pm26.fALOLA.icon.png',
            ],
            [
                'dexNr' => 46,
                'pokemonID' => 'PARAS',
                'formID' => 'PARAS',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets/pm46.icon.png',
            ],
            [
                'dexNr' => 79,
                'pokemonID' => 'SLOWPOKE',
                'formID' => 'SLOWPOKE_GALARIAN',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets/pm79.fGALARIAN.icon.png',
            ],
            [
                'dexNr' => 201,
                'pokemonID' => 'UNOWN',
                'formID' => 'UNOWN',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_201_31.png',
            ],
            [
                'dexNr' => 386,
                'pokemonID' => 'DEOXYS',
                'formID' => 'DEOXYS_SPEED',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_386_14.png',
            ],
            [
                'dexNr' => 422,
                'pokemonID' => 'SHELLOS',
                'formID' => 'SHELLOS_WEST_SEA',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_422_11.png',
            ],
            [
                'dexNr' => 422,
                'pokemonID' => 'SHELLOS',
                'formID' => 'SHELLOS_EAST_SEA',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_422_12.png',
            ],
            [
                'dexNr' => 487,
                'pokemonID' => 'GIRATINA',
                'formID' => 'GIRATINA_ALTERED',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_487_11.png',
            ],
            [
                'dexNr' => 888,
                'pokemonID' => 'ZACIAN',
                'formID' => 'ZACIAN_HERO',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets/pm888.icon.png',
            ],
        ], $generatedRaidBosses);

        return $raidBosses;
    }
}
