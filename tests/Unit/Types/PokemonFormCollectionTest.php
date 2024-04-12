<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use PokemonGoApi\PogoAPI\Types\PokemonFormCollection;
use stdClass;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(PokemonFormCollection::class)]
#[CoversClass(PokemonForm::class)]
class PokemonFormCollectionTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster = file_get_contents(__DIR__ . '/Fixtures/FORMS_V0019_POKEMON_RATTATA.json') ?: '{}';
        $data       = json_decode($gameMaster, false, 512, JSON_THROW_ON_ERROR);
        $this->assertInstanceOf(stdClass::class, $data);
        $formCollection = PokemonFormCollection::createFromGameMaster($data->data);

        $this->assertSame('RATTATA', $formCollection->getPokemonId());
        $this->assertCount(4, $formCollection->getPokemonForms());
        $secondForm = $formCollection->getPokemonForms()[1];

        $this->assertSame('RATTATA_ALOLA', $secondForm->getId());
        $this->assertSame(61, $secondForm->getAssetBundleValue());
    }
}
