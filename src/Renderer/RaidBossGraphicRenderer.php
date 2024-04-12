<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer;

use PokemonGoApi\PogoAPI\Collections\RaidBossCollection;
use PokemonGoApi\PogoAPI\Collections\TranslationCollection;
use PokemonGoApi\PogoAPI\Renderer\Types\RaidBossGraphic;
use PokemonGoApi\PogoAPI\Renderer\Types\RaidBossGraphicConfig;
use PokemonGoApi\PogoAPI\Renderer\Types\RenderingRaidBoss;
use PokemonGoApi\PogoAPI\Types\RaidBoss;
use PokemonGoApi\PogoAPI\Util\TypeEffectivenessCalculator;
use PokemonGoApi\PogoAPI\Util\TypeWeatherCalculator;

use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function trim;

final class RaidBossGraphicRenderer
{
    public const string TEMPLATE_PATH = __DIR__ . '/templates';

    public function buildGraphic(
        RaidBossCollection $raidBossCollection,
        TranslationCollection $translationCollection,
        RaidBossGraphicConfig $raidBossGraphicConfig,
    ): RaidBossGraphic {
        $weatherCalculator = new TypeWeatherCalculator();
        $typeCalculator    = new TypeEffectivenessCalculator();
        $bosses            = [];
        $raidBosses        = [];

        $raidBossList = $raidBossCollection->toArray();
        foreach ($raidBossList as $raidBoss) {
            $bosses[] = new RenderingRaidBoss(
                $raidBoss,
                $this->getName($raidBoss, $translationCollection),
                new TypeEffectivenessCalculator(),
                new TypeWeatherCalculator(),
            );
        }

        $svgWidth = $svgHeight = 0;
        ob_start();
        include $raidBossGraphicConfig->getTemplateFile();
        $content = ob_get_contents();
        ob_end_clean();

        return new RaidBossGraphic(
            trim($content ?: ''),
            $svgWidth,
            $svgHeight,
        );
    }

    private function getName(RaidBoss $raidBoss, TranslationCollection $translationCollection): string
    {
        $pokemonName = PokemonNameRenderer::renderPokemonName($raidBoss->getPokemon(), $translationCollection);
        $temporary   = $raidBoss->getTemporaryEvolution();
        if ($temporary !== null) {
            $pokemonName = PokemonNameRenderer::renderPokemonMegaName(
                $raidBoss->getPokemon(),
                $temporary->getId(),
                $translationCollection,
            );
        }

        return $pokemonName ?? '';
    }
}
