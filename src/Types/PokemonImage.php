<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;

use function array_key_exists;
use function count;
use function preg_match;
use function sprintf;
use function str_replace;
use function strpos;

final class PokemonImage
{
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%s.png';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_NEW_BASE_URL = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/Addressable%20Assets';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const ASSETS_BASE_URL_SHINY = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_%s_shiny.png';

    private string $imageName;
    private int $dexNr;
    private ?int $assetBundleValue;
    private bool $isShiny;
    private ?string $assetBundleSuffix;
    private ?string $costume;
    private bool $isFemale;

    private function __construct(
        string $imageName,
        int $dexNr,
        ?int $assetBundleValue,
        bool $isShiny,
        ?string $assetBundleSuffix,
        ?string $costume,
        bool $isFemale
    ) {
        $this->dexNr             = $dexNr;
        $this->assetBundleValue  = $assetBundleValue;
        $this->isShiny           = $isShiny;
        $this->assetBundleSuffix = $assetBundleSuffix;
        $this->costume           = $costume;
        $this->imageName         = $imageName;
        $this->isFemale          = $isFemale;
    }

    public static function createFromFilePath(string $path): self
    {
        $matches = [];
        $result  = preg_match(
            <<<'REGEX'
            ~(pokemon_icon_(
                (?<dexNr>\d{1,4})_(?<assetBundleValue>\d{2})(_(?<costume>\d{2}))?
                |
                (?<assetBundleSuffix>pm(?<dexNr2>\d{1,4})_(?<assetBundleValue2>\d{2})\w+?)
             )
             (?<isShiny>_shiny)?)
             \.png$~x
            REGEX,
            $path,
            $matches
        );

        if ($result !== false && count($matches) > 0) {
            return new self(
                $matches[0],
                (int) ($matches['dexNr'] ?: $matches['dexNr2']),
                (int) ($matches['assetBundleValue'] ?: $matches['assetBundleValue2']),
                array_key_exists('isShiny', $matches),
                isset($matches['assetBundleSuffix']) && $matches['assetBundleSuffix'] !== ''
                    ? $matches['assetBundleSuffix']
                    : null,
                isset($matches['costume']) && $matches['costume'] !== '' ? $matches['costume'] : null,
                (int) ($matches['assetBundleValue'] ?: $matches['assetBundleValue2']) === 1
            );
        }

        $result = preg_match(
            <<<'REGEX'
                ~/pm(?<dexNr>\d{1,4})
                (\.f(?<form>[^\.]*))?
                (\.c(?<costume>[^\.]*))?
                (?<gender>\.g2)?
                (?<isShiny>\.s)?
                \.icon\.png~x
                REGEX,
            $path,
            $matches
        );

        if ($result === false || count($matches) === 0) {
            throw new Exception('Path "' . $path . '" does not match Regex', 1617886414508);
        }

        return new self(
            $matches[0],
            (int) ($matches['dexNr']),
            null,
            ($matches['isShiny'] ?? '') !== '',
            $matches['form'] ?? null,
            $matches['costume'] ?? null,
            ($matches['gender'] ?? '') !== ''
        );
    }

    public function buildUrl(bool $shiny = false): string
    {
        if (strpos($this->imageName, '/pm') === 0) {
            if ($shiny) {
                return self::ASSETS_NEW_BASE_URL . str_replace('.icon', '.s.icon', $this->imageName);
            }

            return self::ASSETS_NEW_BASE_URL . $this->imageName;
        }

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

    public function getAssetBundleValue(): ?int
    {
        return $this->assetBundleValue;
    }

    public function getCostume(): ?string
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

    public function isFemale(): bool
    {
        return $this->isFemale;
    }
}
