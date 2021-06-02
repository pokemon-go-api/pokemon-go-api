<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;

use function array_key_exists;
use function preg_match;
use function sprintf;

final class PokemonImage
{
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%s.png';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_URL_SHINY = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%s_shiny.png';

    private int $dexNr;
    private int $assetBundleValue;
    private bool $isShiny;
    private ?string $assetBundleSuffix;
    private ?int $costume;

    public function __construct(
        int $dexNr,
        int $assetBundleValue,
        bool $isShiny,
        ?string $assetBundleSuffix,
        ?int $costume
    ) {
        $this->dexNr             = $dexNr;
        $this->assetBundleValue  = $assetBundleValue;
        $this->isShiny           = $isShiny;
        $this->assetBundleSuffix = $assetBundleSuffix;
        $this->costume           = $costume;
    }

    public static function createFromFilePath(string $path): self
    {
        $matches = [];
        $result  = preg_match(
            <<<'REGEX'
            ~pokemon_icon_(
                (?<dexNr>\d{3})_(?<assetBundleValue>\d{2})(_(?<costume>\d{2}))?
                |
                (?<assetBundleSuffix>pm(?<dexNr2>\d{4})_(?<assetBundleValue2>\d{2})\w+?)
             )
             (?<isShiny>_shiny)?
             \.png$~x
            REGEX,
            $path,
            $matches
        );

        if ($result === false || (! isset($matches['dexNr']) && ! isset($matches['dexNr2']))) {
            throw new Exception('Path "' . $path . '" does not match Regex', 1617886414508);
        }

        return new self(
            (int) ($matches['dexNr'] ?: $matches['dexNr2']),
            (int) ($matches['assetBundleValue'] ?: $matches['assetBundleValue2']),
            array_key_exists('isShiny', $matches),
            isset($matches['assetBundleSuffix']) && $matches['assetBundleSuffix'] !== ''
                ? $matches['assetBundleSuffix']
                : null,
            isset($matches['costume']) && $matches['costume'] !== '' ? (int) $matches['costume'] : null,
        );
    }

    public function buildUrl(bool $shiny = false): string
    {
        $assetUrl = self::ASSETS_BASE_URL;
        if ($shiny) {
            $assetUrl = self::ASSETS_BASE_URL_SHINY;
        }

        if ($this->assetBundleSuffix !== null) {
            return sprintf(
                $assetUrl,
                $this->assetBundleSuffix
            );
        }

        if ($this->costume !== null) {
            return sprintf(
                $assetUrl,
                sprintf('%03d_%02d_%02d', $this->dexNr, $this->assetBundleValue, $this->costume)
            );
        }

        return sprintf(
            $assetUrl,
            sprintf('%03d_%02d', $this->dexNr, $this->assetBundleValue)
        );
    }

    public function getAssetBundleSuffix(): ?string
    {
        return $this->assetBundleSuffix;
    }

    public function getAssetBundleValue(): int
    {
        return $this->assetBundleValue;
    }

    public function getCostume(): ?int
    {
        return $this->costume;
    }

    public function getDexNr(): int
    {
        return $this->dexNr;
    }

    public function isShiny(): bool
    {
        return $this->isShiny;
    }
}
