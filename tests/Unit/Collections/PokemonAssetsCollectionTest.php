<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Collections;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Collections\PokemonAssetsCollection;

/**
 * @covers PokemonAssetsCollection
 */
class PokemonAssetsCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $sut = new PokemonAssetsCollection(
            'pokemon_icon_pm0003_00_pgo_copy2019.png',
            'pokemon_icon_pm0003_01_pgo_copy2019.png',
            'pokemon_icon_003_00.png',
            'pokemon_icon_003_01.png',
            'pokemon_icon_003_51.png',
            'pokemon_icon_003_00_shiny.png',
            'pokemon_icon_003_01_shiny.png',
            'pokemon_icon_003_51_shiny.png',
        );

        // allowed:
        // - pokemon_icon_pm0003_00_pgo_copy2019
        // - pokemon_icon_pm0003_01_pgo_copy2019
        // - pokemon_icon_003_01
        // - pokemon_icon_003_51
        self::assertCount(4, $sut->getImages(3));
    }
}
