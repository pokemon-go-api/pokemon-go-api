<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Types;

final class PokemonForm
{
    private string $id;
    private int $assetBundleValue;

    public function __construct(string $id, int $assetBundleValue)
    {
        $this->id               = $id;
        $this->assetBundleValue = $assetBundleValue;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAssetBundleValue(): int
    {
        return $this->assetBundleValue;
    }
}
