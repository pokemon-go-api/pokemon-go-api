<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Types;

use PHPUnit\Framework\TestCase;
use PokemonGoLingen\PogoAPI\Types\PokemonFormCollection;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @covers \PokemonGoLingen\PogoAPI\Types\PokemonFormCollection
 * @covers \PokemonGoLingen\PogoAPI\Types\PokemonForm
 */
class PokemonFormCollectionTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster     = file_get_contents(__DIR__ . '/Fixtures/FORMS_V0019_POKEMON_RATTATA.json') ?: '{}';
        $data           = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        $formCollection = PokemonFormCollection::createFromGameMaster($data->data);

        self::assertSame('RATTATA', $formCollection->getPokemonId());
        self::assertCount(1, $formCollection->getPokemonForms());
        $firstForm = $formCollection->getPokemonForms()[0];

        self::assertSame('RATTATA_ALOLA', $firstForm->getId());
        self::assertSame(61, $firstForm->getAssetBundleValue());
    }
}
