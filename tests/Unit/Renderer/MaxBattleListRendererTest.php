<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Renderer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\MaxBattleCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoApi\PogoAPI\IO\GithubLoader;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Pokemon;
use PokemonGoApi\PogoAPI\Renderer\MaxBattleListRenderer;
use PokemonGoApi\PogoAPI\Types\MaxBattle;
use PokemonGoApi\PogoAPI\Types\MaxBattleLevel;
use PokemonGoApi\PogoAPI\Types\PokemonImage;
use PokemonGoApi\PogoAPI\Types\PokemonType;

#[CoversClass(MaxBattleListRenderer::class)]
final class MaxBattleListRendererTest extends TestCase
{
    public function testBuildList(): void
    {
        $butterfree = new Pokemon(12, 'BUTTERFREE', 'BUTTERFREE', PokemonType::none(), PokemonType::none(), pokemonImages: [
            PokemonImage::createFromFilePath('pm12.icon.png'),
            PokemonImage::createFromFilePath('pm12.fGIGANTAMAX.icon.png'),
        ]);
        $sut        = new MaxBattleListRenderer();
        $collection = new MaxBattleCollection();
        $collection->add(new MaxBattle(
            $butterfree,
            false,
            MaxBattleLevel::LEVEL_1,
        ));
        $collection->add(new MaxBattle(
            $butterfree,
            true,
            MaxBattleLevel::LEVEL_6,
        ));
        $list = $sut->buildList($collection, new TranslationCollectionCollection());

        $this->assertSame([
            'tier_1' => [
                [
                    'id' => 'BUTTERFREE',
                    'assets' => [
                        'image' => GithubLoader::ASSETS_BASE_URL . 'pm12.icon.png',
                        'shinyImage' => GithubLoader::ASSETS_BASE_URL . 'pm12.s.icon.png',
                    ],
                    'level' => 1,
                    'names' => [],
                    'shiny' => false,
                    'types' => [],
                    'cpRange' => [0 => 3,1 => 8],
                ],
            ],
            'tier_6' => [
                [
                    'id' => 'BUTTERFREE',
                    'assets' => [
                        'image' => GithubLoader::ASSETS_BASE_URL . 'pm12.fGIGANTAMAX.icon.png',
                        'shinyImage' => GithubLoader::ASSETS_BASE_URL . 'pm12.fGIGANTAMAX.s.icon.png',
                    ],
                    'level' => 6,
                    'names' => [],
                    'shiny' => true,
                    'types' => [],
                    'cpRange' => [0 => 3,1 => 8],
                ],
            ],
        ], $list);
    }
}
