<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Collections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonAssetsCollection;
use PokemonGoApi\PogoAPI\Types\PokemonImage;

#[CoversClass(PokemonAssetsCollection::class)]
#[UsesClass(PokemonImage::class)]
class PokemonAssetsCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $sut = new PokemonAssetsCollection(
            'pokemon_icon_pm0003_00_pgo_copy2019.png',
            'pokemon_icon_pm0003_01_pgo_copy2019.png',
            'invalid_file.png',
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
        // - pokemon_icon_003_00
        // - pokemon_icon_003_01
        // - pokemon_icon_003_51
        $this->assertCount(5, $sut->getImages(3));
    }
}
