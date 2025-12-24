<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use CuyZ\Valinor\Mapper\Object\Constructor;
use PokemonGoApi\PogoAPI\Types\PokemonForm;

use function in_array;
use function str_replace;

final class PokemonForms
{
    public function __construct(
        private readonly string $pokemonId,
        /** @var list<PokemonForm> */
        private array $pokemonForms = [],
    ) {
    }

    /** @param array{ pokemon: string, forms: list<array{ form: string, isCostume?: bool, assetBundleValue?: int, assetBundleSuffix?: string }> } $formSettings */
    #[Constructor]
    public static function fromArray(
        array $formSettings,
    ): self {
        $pokemonForms = [];
        foreach ($formSettings['forms'] as $formData) {
            $formOnlyId = str_replace($formSettings['pokemon'] . '_', '', $formData['form']);
            if (in_array($formOnlyId, ['PURIFIED', 'SHADOW', 'COPY'], true)) {
                continue;
            }

            $pokemonForms[] = new PokemonForm(
                $formData['form'],
                $formOnlyId,
                $formData['isCostume'] ?? false,
                $formData['assetBundleValue'] ?? null,
                null,
            );
        }

        return new self(
            $formSettings['pokemon'],
            $pokemonForms,
        );
    }

    /** @return list<PokemonForm> */
    public function getPokemonForms(): array
    {
        return $this->pokemonForms;
    }

    public function getPokemonId(): string
    {
        return $this->pokemonId;
    }
}
