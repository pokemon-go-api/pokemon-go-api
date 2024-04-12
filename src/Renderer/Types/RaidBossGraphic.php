<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Renderer\Types;

use function preg_replace;
use function sprintf;

final readonly class RaidBossGraphic
{
    public function __construct(private string $imageContent, private int $imageWidth, private int $imageHeight)
    {
    }

    public function getImageContent(): string
    {
        return sprintf(
            '%s<!-- CONVERT_IMAGE_SIZE=%s -->',
            preg_replace('~\s+~', ' ', $this->imageContent),
            $this->getWindowSize(),
        );
    }

    public function getWindowSize(): string
    {
        return sprintf('%d,%d', $this->imageWidth, $this->imageHeight);
    }
}
