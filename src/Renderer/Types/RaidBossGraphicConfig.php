<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer\Types;

use PokemonGoApi\PogoAPI\Renderer\RaidBossGraphicRenderer;

final class RaidBossGraphicConfig
{
    public const ORDER_MEGA_TO_LVL1 = 'megaToLvl1';
    public const ORDER_LVL1_TO_MEGA = 'lvl1ToMega';

    private string $templateFile;

    public function __construct(
        private string $order = self::ORDER_MEGA_TO_LVL1,
        private bool $useShinyImages = true,
        string $template = RaidBossGraphicRenderer::TEMPLATE_PATH . '/raidlist_default.phtml',
    ) {
        $this->templateFile = $template;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function useShinyImages(): bool
    {
        return $this->useShinyImages;
    }

    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }
}
