<?php

declare(strict_types=1);

namespace Tests\Integration\PokemonGoLingen\PogoAPI;

use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function implode;
use function is_array;
use function json_decode;

/**
 * @coversNothing
 */
class PokemonFormRenderingTest extends TestCase
{
    /**
     * @param array<string, mixed[]> $expected
     *
     * @dataProvider specialPokemonDataProvider
     */
    public function testRenderingPokemonWithForms(int $dexNr, array $expected): void
    {
        $pokedexEntry = json_decode(
            file_get_contents(__DIR__ . '/../../data/tmp/api/pokedex/id/' . $dexNr . '.json') ?: '[]',
            true
        );

        $this->validateSubset($expected, $pokedexEntry);
    }

    /**
     * @return array<string, mixed[]>
     */
    public function specialPokemonDataProvider(): iterable
    {
        yield 'pikachu' => [
            'dexNr'    => 25,
            'expected' => [
                'id'          => 'PIKACHU',
                'formId'      => 'PIKACHU',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'assets'      => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_025_00.png'],
                'regionForms' => [
                    'PIKACHU_FLYING_5TH_ANNIV' => [],
                    'PIKACHU_KARIYUSHI'        => [],
                    'PIKACHU_POP_STAR'         => [],
                    'PIKACHU_ROCK_STAR'        => [],
                ],
            ],
        ];

//        yield 'burmi' => [
//            'dexNr'    => 412,
//            'expected' => [
//                'id'     => 'BURMY',
//                'formId' => 'BURMY_PLANT',
//                'assets' => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_412_11.png'],
//                'regionForms' => [
//                    'BURMY_SANDY' => [],
//                    'BURMY_TRASH' => [],
//                ],
//            ],
//        ];
//
//        yield 'cherrim' => [
//            'dexNr'    => 421,
//            'expected' => [
//                'id'     => 'CHERRIM',
//                'formId' => 'CHERRIM_OVERCAST',
//                'assets' => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_421_11.png'],
//                'regionForms' => [
//                    'CHERRIM_SUNNY' => [],
//                ],
//            ],
//        ];
//
//        yield 'keldeo' => [
//            'dexNr'    => 647,
//            'expected' => [
//                'id'     => 'KELDEO',
//                'formId' => 'KELDEO_ORDINARY',
//                'assets' => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_647_11.png'],
//            ],
//        ];

        yield 'kyurem' => [
            'dexNr'    => 646,
            'expected' => [
                'id'          => 'KYUREM',
                'formId'      => 'KYUREM',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'assets'      => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_646_11.png'],
                'regionForms' => [
                    'KYUREM_BLACK' => [],
                    'KYUREM_WHITE' => [],
                ],
            ],
        ];

        yield 'deoxys' => [
            'dexNr'    => 386,
            'expected' => [
                'id'          => 'DEOXYS',
                'formId'      => 'DEOXYS',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'assets'      => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_386_11.png'],
                'regionForms' => [
                    'DEOXYS_ATTACK'  => [],
                    'DEOXYS_DEFENSE' => [],
                    'DEOXYS_SPEED'   => [],
                ],
            ],
        ];

        yield 'thundurus' => [
            'dexNr'    => 642,
            'expected' => [
                'id'          => 'THUNDURUS',
                'formId'      => 'THUNDURUS_INCARNATE',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'assets'      => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_642_11.png'],
                'regionForms' => [
                    'THUNDURUS_THERIAN' => [],
                ],
            ],
        ];

        yield 'genesect' => [
            'dexNr'    => 649,
            'expected' => [
                'id'          => 'GENESECT',
                'formId'      => 'GENESECT',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'assets'      => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_649_11.png'],
                'regionForms' => [
                    'GENESECT_BURN'  => [],
                    'GENESECT_CHILL' => [],
                    'GENESECT_DOUSE' => [],
                    'GENESECT_SHOCK' => [],
                ],
            ],
        ];

        yield 'meowth' => [
            'dexNr'    => 52,
            'expected' => [
                'id'            => 'MEOWTH',
                'formId'        => 'MEOWTH',
                //phpcs:ignore Generic.Files.LineLength.TooLong
                'assets'        => ['image' => 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_052_00.png'],
                'secondaryType' => null,
                'regionForms'   => [
                    'MEOWTH_ALOLA'    => [],
                    'MEOWTH_GALARIAN' => [],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $current
     * @param array<string>        $keyChain
     */
    private function validateSubset(array $expected, array $current, array $keyChain = []): void
    {
        foreach ($expected as $key => $expectedValue) {
            $currentKeyChain = [...$keyChain, $key];
            if (is_array($expectedValue)) {
                $this->validateSubset($expectedValue, $current[$key], $currentKeyChain);

                return;
            }

            self::assertSame($expectedValue, $current[$key], 'Expected field: ' . implode('.', $currentKeyChain));
        }
    }
}
