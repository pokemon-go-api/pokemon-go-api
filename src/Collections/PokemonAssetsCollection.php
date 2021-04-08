<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Collections;

use PokemonGoLingen\PogoAPI\Types\PokemonImage;
use stdClass;
use Throwable;

use function file_get_contents;
use function is_file;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class PokemonAssetsCollection
{
    /** @var array<string, PokemonImage[]> */
    private array $imagesByDexNr = [];

    public function __construct(string $gitSubtreeFilePath)
    {
        if (! is_file($gitSubtreeFilePath)) {
            return;
        }

        $fileContent = file_get_contents($gitSubtreeFilePath);
        $content     = json_decode($fileContent ?: 'false', false, 512, JSON_THROW_ON_ERROR);
        if (! $content instanceof stdClass) {
            return;
        }

        foreach ($content->tree as $fileMeta) {
            try {
                $image = PokemonImage::createFromFile($fileMeta->path);
            } catch (Throwable $err) {
                continue;
            }

            if (
                $image->isShiny() || (
                    $image->getCostume() === null &&
                    $image->getAssetBundleSuffix() === null &&
                    $image->getAssetBundleValue() === 0
                )
            ) {
                continue;
            }

            $this->imagesByDexNr['dex_' . $image->getDexNr()][] = $image;
        }
    }

    /**
     * @return array<int, PokemonImage>
     */
    public function getImages(int $dexNr): array
    {
        return $this->imagesByDexNr['dex_' . $dexNr] ?? [];
    }
}
