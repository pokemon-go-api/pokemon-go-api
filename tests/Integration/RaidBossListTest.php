<?php

declare(strict_types=1);

namespace Tests\Integration\PokemonGoLingen\PogoAPI;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Parser\LeekduckParser;
use PokemonGoApi\PogoAPI\Parser\MasterDataParser;
use PokemonGoApi\PogoAPI\Parser\PokemonGoImagesParser;

/**
 * @coversNothing
 */
class RaidBossListTest extends TestCase
{
    public function testRenderDummyRaidList(): void
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
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_006_52.png',
            ],
            [
                'dexNr' => 18,
                'pokemonID' => 'PIDGEOT',
                'formID' => 'PIDGEOT_MEGA',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets/pm18.fMEGA.icon.png',
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
    }
}
