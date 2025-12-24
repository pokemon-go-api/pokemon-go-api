<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Collections;

use PokemonGoApi\PogoAPI\Types\PokemonImage;
use Throwable;

use function usort;

final class PokemonAssetsCollection
{
    /** @var array<string, list<PokemonImage>> */
    private array $imagesByDexNr = [];

    public function __construct(string ...$images)
    {
        foreach ($images as $imageName) {
            try {
                $image = PokemonImage::createFromFilePath($imageName);
            } catch (Throwable) {
                continue;
            }

            if ($image->isShiny()) {
                continue;
            }

            $this->imagesByDexNr['dex_' . $image->getDexNr()][] = $image;
        }
    }

    /** @return list<PokemonImage> */
    public function getImages(int $dexNr): array
    {
        $images = $this->imagesByDexNr['dex_' . $dexNr] ?? [];
        usort($images, static fn (PokemonImage $a, PokemonImage $b): int => $a->isFemale() <=> $b->isFemale());

        return $images;
    }
}
