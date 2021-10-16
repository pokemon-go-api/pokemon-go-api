<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonFormCollection;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @covers \PokemonGoApi\PogoAPI\Types\PokemonFormCollection
 * @covers \PokemonGoApi\PogoAPI\Types\PokemonForm
 */
class PokemonFormCollectionTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster     = file_get_contents(__DIR__ . '/Fixtures/FORMS_V0019_POKEMON_RATTATA.json') ?: '{}';
        $data           = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        $formCollection = PokemonFormCollection::createFromGameMaster($data->data);

        self::assertSame('RATTATA', $formCollection->getPokemonId());
        self::assertCount(4, $formCollection->getPokemonForms());
        $secondForm = $formCollection->getPokemonForms()[1];

        self::assertSame('RATTATA_ALOLA', $secondForm->getId());
        self::assertSame(61, $secondForm->getAssetBundleValue());
    }
}
