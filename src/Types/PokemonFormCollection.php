<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Types;

use Exception;
use stdClass;

use function assert;
use function preg_match;
use function str_replace;

final class PokemonFormCollection
{
    /** @var PokemonForm[] */
    private array $pokemonForms;
    private string $pokemonId;

    public function __construct(string $pokemonId, PokemonForm ...$pokemonForms)
    {
        $this->pokemonForms = $pokemonForms;
        $this->pokemonId    = $pokemonId;
    }

    public static function createFromGameMaster(stdClass $gameMasterData): self
    {
        $templateIdParts = [];
        $pregMatchResult = preg_match(
            '~^FORMS_V(?<id>\d{4})_POKEMON_(?<name>.*)$~i',
            $gameMasterData->templateId ?? '',
            $templateIdParts
        );
        if ($pregMatchResult < 1) {
            throw new Exception('Invalid input data provided', 1608128086204);
        }

        $forms = [];
        foreach ($gameMasterData->formSettings->forms ?? [] as $formData) {
            assert($formData instanceof stdClass);

            $formOnlyId = str_replace($templateIdParts['name'] . '_', '', $formData->form);

            $forms[] = new PokemonForm(
                $formData->form,
                $formOnlyId,
                $formData->assetBundleValue ?? null,
                $formData->assetBundleSuffix ?? null
            );
        }

        return new self(
            $gameMasterData->formSettings->pokemon,
            ...$forms
        );
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
