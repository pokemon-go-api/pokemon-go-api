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
final class PokemonAssetsCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $sut = new PokemonAssetsCollection(
            'pm0003.fcopy2019.icon.png',
            'pm0003.fcopy2019.g2.icon.png',
            'invalid_file.icon.png',
            'pm3.c00.icon.png',
            'pm3.c01.icon.png',
            'pm3.c51.icon.png',
            'pm3.c00.s.icon.png',
            'pm3.c01.s.icon.png',
            'pm3.c51.s.icon.png',
        );
        $this->assertCount(5, $sut->getImages(3));
    }
}
