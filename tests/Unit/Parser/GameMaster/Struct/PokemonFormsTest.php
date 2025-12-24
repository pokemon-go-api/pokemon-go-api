<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonForms;
use PokemonGoApi\PogoAPI\Parser\JsonMapper;
use PokemonGoApi\PogoAPI\Types\PokemonForm;

use function file_get_contents;

#[CoversClass(PokemonForms::class)]
#[CoversClass(PokemonForm::class)]
final class PokemonFormsTest extends TestCase
{
    public function testCreateFromGameMaster(): void
    {
        $gameMaster     = file_get_contents(__DIR__ . '/Fixtures/FORMS_V0019_POKEMON_RATTATA.json') ?: '{}';
        $pokemonData    = JsonParser::decodeGameMasterFileData($gameMaster);
        $formCollection = JsonMapper::map(PokemonForms::class, $pokemonData);

        $this->assertSame('RATTATA', $formCollection->getPokemonId());
        $this->assertCount(2, $formCollection->getPokemonForms());
        $firstForm = $formCollection->getPokemonForms()[1];

        $this->assertSame('RATTATA_ALOLA', $firstForm->getId());
        $this->assertSame(61, $firstForm->getAssetBundleValue());
    }
}
