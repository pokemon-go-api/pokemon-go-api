<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Renderer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\TranslationCollection;
use PokemonGoApi\PogoAPI\Parser\CustomTranslations;
use PokemonGoApi\PogoAPI\Renderer\PokemonNameRenderer;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\TemporaryEvolution;

#[CoversClass(PokemonNameRenderer::class)]
#[UsesClass(Pokemon::class)]
#[UsesClass(TranslationCollection::class)]
#[UsesClass(PokemonType::class)]
#[UsesClass(TemporaryEvolution::class)]
#[UsesClass(PokemonForm::class)]
#[UsesClass(CustomTranslations::class)]
class PokemonNameRendererTest extends TestCase
{
    public function testRenderPokemonName(): void
    {
        $pokemon               = new Pokemon(1, 'test', 'normal', PokemonType::none(), null);
        $translationCollection = new TranslationCollection('dummylanguage');
        $translationCollection->addPokemonName(1, 'Testpokemon');
        $translationCollection->addRegionalForm(CustomTranslations::REGIONFORM_ALOLAN, '(ALOLA)-%s');
        $translationCollection->addRegionalForm(CustomTranslations::REGIONFORM_GALARIAN, '%s-(GALAR)');

        $this->assertSame(
            'Testpokemon',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection),
        );

        $pokemon = $pokemon->withPokemonForm(
            new PokemonForm('dummy', 'ALOLA', 1, null),
        );
        $this->assertSame(
            '(ALOLA)-Testpokemon',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection),
        );

        $pokemon = $pokemon->withPokemonForm(
            new PokemonForm('dummy', 'GALARIAN', 1, null),
        );
        $this->assertSame(
            'Testpokemon-(GALAR)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection),
        );
    }

    public function testRenderPokemonNameWithForm(): void
    {
        $pokemon               = new Pokemon(1, 'DEOXYS', 'DEOXYS', PokemonType::none(), null);
        $translationCollection = new TranslationCollection('dummylanguage');
        $translationCollection->addPokemonName(1, 'Testpokemon');
        $translationCollection->addPokemonFormName('attack', 'Angriffsform');
        $translationCollection->addPokemonFormName('defense', 'Verteidgungsf.');
        $translationCollection->addPokemonFormName('deoxys_normal', 'Normalform');
        $translationCollection->addPokemonFormName('speed', 'Initiativeform');

        $this->assertSame('Testpokemon', PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection));

        $pokemon = new Pokemon(1, 'DEOXYS', 'DEOXYS_NORMAL', PokemonType::none(), null);
        $this->assertSame(
            'Testpokemon (Normalform)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection),
        );

        $pokemon = new Pokemon(1, 'DEOXYS', 'DEOXYS_ATTACK', PokemonType::none(), null);
        $this->assertSame(
            'Testpokemon (Angriffsform)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection),
        );

        $pokemon = new Pokemon(1, 'DEOXYS', 'DEOXYS_DEFENSE', PokemonType::none(), null);
        $this->assertSame(
            'Testpokemon (Verteidgungsf.)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection),
        );

        $pokemon = new Pokemon(1, 'DEOXYS', 'DEOXYS_SPEED', PokemonType::none(), null);
        $this->assertSame(
            'Testpokemon (Initiativeform)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection),
        );
    }

    public function testRenderPokemonMegaName(): void
    {
        $pokemon = new Pokemon(1, 'CHARIZARD', 'CHARIZARD', PokemonType::none(), null);
        $pokemon = $pokemon->withAddedTemporaryEvolutions(
            new TemporaryEvolution('MEGA_X', PokemonType::none(), null),
            new TemporaryEvolution('MEGA_Y', PokemonType::none(), null),
        );

        $translationCollection = new TranslationCollection('dummylanguage');
        $translationCollection->addPokemonName(1, 'Testpokemon');
        $translationCollection->addPokemonMegaName(1, '001', 'Mega CHARIZARD X');
        $translationCollection->addPokemonMegaName(1, '002', 'Mega CHARIZARD Y');

        $this->assertSame(
            'Mega CHARIZARD X',
            PokemonNameRenderer::renderPokemonMegaName($pokemon, 'CHARIZARD_MEGA_X', $translationCollection),
        );
        $this->assertSame(
            'Mega CHARIZARD X',
            PokemonNameRenderer::renderPokemonMegaName($pokemon, 'MEGA_X', $translationCollection),
        );
        $this->assertSame(
            'Mega CHARIZARD Y',
            PokemonNameRenderer::renderPokemonMegaName($pokemon, 'CHARIZARD_MEGA_Y', $translationCollection),
        );
        $this->assertSame(
            'Mega CHARIZARD Y',
            PokemonNameRenderer::renderPokemonMegaName($pokemon, 'MEGA_Y', $translationCollection),
        );
    }
}
