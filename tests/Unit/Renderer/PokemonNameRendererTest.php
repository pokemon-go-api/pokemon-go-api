<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Renderer;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Collections\TranslationCollection;
use PokemonGoApi\PogoAPI\Parser\CustomTranslations;
use PokemonGoApi\PogoAPI\Renderer\PokemonNameRenderer;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use PokemonGoApi\PogoAPI\Types\PokemonType;
use PokemonGoApi\PogoAPI\Types\TemporaryEvolution;

/**
 * @uses   \PokemonGoApi\PogoAPI\Types\Pokemon
 * @uses   \PokemonGoApi\PogoAPI\Collections\TranslationCollection
 * @uses   \PokemonGoApi\PogoAPI\Types\PokemonType
 * @uses   \PokemonGoApi\PogoAPI\Types\TemporaryEvolution
 * @uses   \PokemonGoApi\PogoAPI\Types\PokemonForm
 * @uses   \PokemonGoApi\PogoAPI\Parser\CustomTranslations
 *
 * @covers \PokemonGoApi\PogoAPI\Renderer\PokemonNameRenderer
 */
class PokemonNameRendererTest extends TestCase
{
    public function testRenderPokemonName(): void
    {
        $pokemon               = new Pokemon(1, 'test', 'normal', PokemonType::none(), null);
        $translationCollection = new TranslationCollection('dummylanguage');
        $translationCollection->addPokemonName(1, 'Testpokemon');
        $translationCollection->addRegionalForm(CustomTranslations::REGIONFORM_ALOLAN, '(ALOLA)-%s');
        $translationCollection->addRegionalForm(CustomTranslations::REGIONFORM_GALARIAN, '%s-(GALAR)');

        self::assertSame('Testpokemon', PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection));

        $pokemon->setPokemonForm(
            new PokemonForm('dummy', 'ALOLA', 1, null)
        );
        self::assertSame(
            '(ALOLA)-Testpokemon',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection)
        );

        $pokemon->setPokemonForm(
            new PokemonForm('dummy', 'GALARIAN', 1, null)
        );
        self::assertSame(
            'Testpokemon-(GALAR)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection)
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

        self::assertSame('Testpokemon', PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection));

        $pokemon = new Pokemon(1, 'DEOXYS', 'DEOXYS_NORMAL', PokemonType::none(), null);
        self::assertSame(
            'Testpokemon (Normalform)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection)
        );

        $pokemon = new Pokemon(1, 'DEOXYS', 'DEOXYS_ATTACK', PokemonType::none(), null);
        self::assertSame(
            'Testpokemon (Angriffsform)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection)
        );

        $pokemon = new Pokemon(1, 'DEOXYS', 'DEOXYS_DEFENSE', PokemonType::none(), null);
        self::assertSame(
            'Testpokemon (Verteidgungsf.)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection)
        );

        $pokemon = new Pokemon(1, 'DEOXYS', 'DEOXYS_SPEED', PokemonType::none(), null);
        self::assertSame(
            'Testpokemon (Initiativeform)',
            PokemonNameRenderer::renderPokemonName($pokemon, $translationCollection)
        );
    }

    public function testRenderPokemonMegaName(): void
    {
        $pokemon = new Pokemon(1, 'CHARIZARD', 'CHARIZARD', PokemonType::none(), null);
        $pokemon->addTemporaryEvolutions(new TemporaryEvolution('MEGA_X', PokemonType::none(), null));
        $pokemon->addTemporaryEvolutions(new TemporaryEvolution('MEGA_Y', PokemonType::none(), null));

        $translationCollection = new TranslationCollection('dummylanguage');
        $translationCollection->addPokemonName(1, 'Testpokemon');
        $translationCollection->addPokemonMegaName(1, '001', 'Mega CHARIZARD X');
        $translationCollection->addPokemonMegaName(1, '002', 'Mega CHARIZARD Y');

        self::assertSame(
            'Mega CHARIZARD X',
            PokemonNameRenderer::renderPokemonMegaName($pokemon, 'CHARIZARD_MEGA_X', $translationCollection)
        );
        self::assertSame(
            'Mega CHARIZARD X',
            PokemonNameRenderer::renderPokemonMegaName($pokemon, 'MEGA_X', $translationCollection)
        );
        self::assertSame(
            'Mega CHARIZARD Y',
            PokemonNameRenderer::renderPokemonMegaName($pokemon, 'CHARIZARD_MEGA_Y', $translationCollection)
        );
        self::assertSame(
            'Mega CHARIZARD Y',
            PokemonNameRenderer::renderPokemonMegaName($pokemon, 'MEGA_Y', $translationCollection)
        );
    }
}
