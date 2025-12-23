<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer\Types;

use PokemonGoApi\PogoAPI\Renderer\RaidBossGraphicRenderer;

final readonly class RaidBossGraphicConfig
{
    public const string ORDER_MEGA_TO_LVL1 = 'megaToLvl1';

    public const string ORDER_LVL1_TO_MEGA = 'lvl1ToMega';

    public function __construct(
        private string $order = self::ORDER_MEGA_TO_LVL1,
        private bool $useShinyImages = true,
        private string $templateFile = RaidBossGraphicRenderer::TEMPLATE_PATH . '/raidlist_default.phtml',
    ) {
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
