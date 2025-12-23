<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonForms;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use function file_get_contents;

#[CoversClass(PokemonForms::class)]
#[CoversClass(PokemonForm::class)]
final class PokemonFormCollectionTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster  = file_get_contents(__DIR__ . '/Fixtures/FORMS_V0019_POKEMON_RATTATA.json') ?: '{}';
        $pokemonData = JsonParser::decodeGameMasterFileData($gameMaster);

        $formCollection = PokemonForms::createFromGameMaster($pokemonData);

        $this->assertSame('RATTATA', $formCollection->getPokemonId());
        $this->assertCount(4, $formCollection->getPokemonForms());
        $secondForm = $formCollection->getPokemonForms()[1];

        $this->assertSame('RATTATA_ALOLA', $secondForm->getId());
        $this->assertSame(61, $secondForm->getAssetBundleValue());
    }
}
