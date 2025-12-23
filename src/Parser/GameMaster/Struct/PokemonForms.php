<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser\GameMaster\Struct;

use Exception;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use stdClass;
use function assert;
use function get_object_vars;
use function is_int;
use function is_iterable;
use function is_object;
use function is_string;
use function preg_match;
use function str_replace;

final class PokemonForms
{
    private readonly string $pokemonId;
    /** @var PokemonForm[] */
    private array $pokemonForms = [];

    /**
     * @param array{
     *   pokemon: string,
     *   forms: list<array{
     *       form: string,
     *       isCostume?: bool,
 *           assetBundleValue?: int,
 *           assetBundleSuffix?: string
     *   }>,
     * } $formSettings
     */
    public function __construct(
        string $templateId,
        array $formSettings
    )
    {
        $this->pokemonId = $formSettings['pokemon'];
        foreach ($formSettings['forms'] as $formData) {
            if (count($formData) === 1) {
                continue;
            }
            $formOnlyId       = str_replace($this->pokemonId . '_', '', $formData['form']);
            if (
                str_contains($formOnlyId, '_PURIFIED') ||
                str_contains($formOnlyId, '_SHADOW') ||
                str_contains($formOnlyId, '_NORMAL') ||
                str_contains($formOnlyId, '_COPY') ||
                preg_match('~_\d{4}$~', $formOnlyId)
            ) {
                continue;
            }
            
            $this->pokemonForms[] = new PokemonForm(
                $formData['form'],
                $formOnlyId,
                $formData['isCostume'] ?? false,
                null,
                null,
            );
        }
    }

    /** @return PokemonForm[] */
    public function getPokemonForms(): array
    {
        return $this->pokemonForms;
    }

    public function getPokemonId(): string
    {
        return $this->pokemonId;
    }
}
