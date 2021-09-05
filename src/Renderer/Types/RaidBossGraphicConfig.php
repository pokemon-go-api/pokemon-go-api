<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer\Types;

use PokemonGoApi\PogoAPI\Renderer\RaidBossGraphicRenderer;

final class RaidBossGraphicConfig
{
    public const ORDER_MEGA_TO_LVL1 = 'megaToLvl1';
    public const ORDER_LVL1_TO_MEGA = 'lvl1ToMega';

    private string $order;
    private bool $useShinyImages;
    private string $templateFile;

    public function __construct(
        string $order = self::ORDER_MEGA_TO_LVL1,
        bool $useShinyImages = true,
        string $template = RaidBossGraphicRenderer::TEMPLATE_PATH . '/raidlist_default.phtml'
    ) {
        $this->order          = $order;
        $this->useShinyImages = $useShinyImages;
        $this->templateFile   = $template;
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
