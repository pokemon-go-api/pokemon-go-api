<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\PokemonAssetsCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Parser\PokemonGoImagesParser;
use PokemonGoApi\PogoAPI\Types\PokemonImage;

#[CoversClass(PokemonGoImagesParser::class)]
#[UsesClass(PokemonAssetsCollection::class)]
#[UsesClass(PokemonImage::class)]
#[UsesClass(JsonParser::class)]
class PokemonGoImagesParserTest extends TestCase
{
    public function testParseFile(): void
    {
        $sut        = new PokemonGoImagesParser();
        $collection = $sut->parseFile(__DIR__ . '/Fixtures/pokemon_images.json');

        $this->assertNotEmpty($collection->getImages(3));
    }
}
