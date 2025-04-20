<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use Exception;
use PokemonGoApi\PogoAPI\Collections\AttacksCollection;
use PokemonGoApi\PogoAPI\Collections\ItemsCollection;
use PokemonGoApi\PogoAPI\Collections\PokemonAssetsCollection;
use PokemonGoApi\PogoAPI\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Collections\QuestsCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Types\EvolutionQuest;
use PokemonGoApi\PogoAPI\Types\Item;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonCombatMove;
use PokemonGoApi\PogoAPI\Types\PokemonFormCollection;
use PokemonGoApi\PogoAPI\Types\PokemonMove;
use stdClass;

use function assert;
use function count;
use function file_get_contents;
use function preg_match;
use function str_contains;
use function str_starts_with;
use function substr;

class MasterDataParser
{
    private ItemsCollection $itemsCollection;
    private PokemonCollection $pokemonCollection;
    private AttacksCollection $attacksCollection;
    private QuestsCollection $questsCollection;

    public function __construct(private readonly PokemonAssetsCollection $pokemonAssetsCollection)
    {
        $this->itemsCollection   = new ItemsCollection();
        $this->pokemonCollection = new PokemonCollection();
        $this->attacksCollection = new AttacksCollection();
        $this->questsCollection  = new QuestsCollection();
    }

    public function parseFile(string $gameMasterFile): void
    {
        $fileContent = file_get_contents($gameMasterFile);
        if ($fileContent === false) {
            throw new Exception('file does not exists');
        }

        /** @var list<stdClass> $list */
        $list                    = JsonParser::decodeToArray($fileContent);
        $this->itemsCollection   = $this->parseItems($list);
        $this->attacksCollection = $this->parseMoves($list);
        $this->pokemonCollection = $this->parsePokemon($list);
        $this->questsCollection  = $this->parseQuests($list);

        $this->addCombatMoves($list, $this->attacksCollection);
        $this->addTemporaryEvolutions($list, $this->pokemonCollection);
        $this->addForms($list, $this->pokemonCollection);
    }

    public function getAttacksCollection(): AttacksCollection
    {
        return $this->attacksCollection;
    }

    public function getItemsCollection(): ItemsCollection
    {
        return $this->itemsCollection;
    }

    public function getPokemonCollection(): PokemonCollection
    {
        return $this->pokemonCollection;
    }

    public function getQuestsCollection(): QuestsCollection
    {
        return $this->questsCollection;
    }

    /** @param array<int, stdClass> $list */
    private function parseItems(array $list): ItemsCollection
    {
        $itemsCollection = new ItemsCollection();
        foreach ($list as $item) {
            if (! str_starts_with($item->templateId, 'ITEM_')) {
                continue;
            }

            assert($item->data instanceof stdClass);

            if (! isset($item->data->itemSettings->itemId)) {
                continue;
            }

            $itemsCollection->add(
                new Item(
                    $item->data->templateId,
                    $item->data->itemSettings->itemId,
                ),
            );
        }

        return $itemsCollection;
    }

    /** @param array<int, stdClass> $list */
    private function parsePokemon(array $list): PokemonCollection
    {
        $pokemonCollection = new PokemonCollection();
        foreach ($list as $item) {
            $matches = [];
            if (
                ! preg_match('~^v(?<DexNr>[0-9]{4})_POKEMON_.*~i', (string) $item->templateId, $matches)
                || ! isset($item->data->pokemonSettings)
            ) {
                continue;
            }

            assert($item->data instanceof stdClass);

            $pokemon = Pokemon::createFromGameMaster($item->data);
            $pokemon = $pokemon->withAddedImages(
                $this->pokemonAssetsCollection->getImages($pokemon->getDexNr()),
            );

            if (
                str_contains($pokemon->getFormId(), '_PURIFIED') ||
                str_contains($pokemon->getFormId(), '_SHADOW') ||
                str_contains($pokemon->getFormId(), '_NORMAL') ||
                str_contains($pokemon->getFormId(), '_COPY') ||
                preg_match('~_\d{4}$~', $pokemon->getFormId())
            ) {
                continue;
            }

            $basePokemon = $pokemonCollection->get($pokemon->getId());
            if ($basePokemon !== null) {
                $basePokemon = $basePokemon->withAddedPokemonRegionForm($pokemon);
            }

            $pokemonCollection->add($basePokemon ?? $pokemon);
        }

        return $pokemonCollection;
    }

    /** @param list<stdClass> $list */
    private function parseMoves(array $list): AttacksCollection
    {
        $attacksCollection = new AttacksCollection();
        foreach ($list as $item) {
            $matches = [];
            if (! preg_match('~^v(?<MoveId>[0-9]{4})_MOVE_(?<MoveName>.*)$~i', (string) $item->templateId, $matches)) {
                continue;
            }

            $attacksCollection->add(
                PokemonMove::createFromGameMaster($item->data),
            );
        }

        return $attacksCollection;
    }

    /** @param list<stdClass> $list */
    private function parseQuests(array $list): QuestsCollection
    {
        $questsCollection = new QuestsCollection();
        foreach ($list as $item) {
            $matches = [];
            if (! preg_match('~(?<Quest>.*)_EVOLUTION_QUEST$~i', (string) $item->templateId, $matches)) {
                continue;
            }

            $questsCollection->add(
                EvolutionQuest::createFromGameMaster($item->data),
            );
        }

        return $questsCollection;
    }

    /** @param list<stdClass> $list */
    private function addCombatMoves(array $list, AttacksCollection $attacksCollection): void
    {
        foreach ($list as $item) {
            $matches = [];
            if (
                ! preg_match(
                    '~^COMBAT_V(?<MoveId>[0-9]{4})_MOVE_(?<MoveName>.*)$~i',
                    (string) $item->templateId,
                    $matches,
                )
            ) {
                continue;
            }

            $moveId = (int) $matches['MoveId'];
            $move   = $attacksCollection->getById($moveId);
            if ($move === null) {
                continue;
            }

            assert($item->data instanceof stdClass);

            $move->setCombatMove(PokemonCombatMove::createFromGameMaster($item->data));
        }
    }

    /** @param list<stdClass> $list */
    private function addTemporaryEvolutions(array $list, PokemonCollection $pokemonCollection): void
    {
        foreach ($list as $item) {
            $matches = [];
            if (
                ! preg_match(
                    '~^TEMPORARY_EVOLUTION_V(?<DexNr>[0-9]{4})_POKEMON_(.*)$~i',
                    (string) $item->templateId,
                    $matches,
                )
            ) {
                continue;
            }

            $dexNr   = (int) $matches['DexNr'];
            $pokemon = $pokemonCollection->getByDexId($dexNr);
            if ($pokemon === null) {
                continue;
            }

            $temporaryEvolutions = $pokemon->getTemporaryEvolutions();
            foreach ($temporaryEvolutions as $temporaryEvolution) {
                $temporaryEvolutions[$temporaryEvolution->getId()] = $temporaryEvolution;
            }

            foreach ($item->data->temporaryEvolutionSettings->temporaryEvolutions as $temporaryEvolution) {
                $temporaryEvolutionId = $item->data->temporaryEvolutionSettings->pokemonId
                    . substr((string) $temporaryEvolution->temporaryEvolutionId, 14); // trim TEMP_EVOLUTION
                if (! isset($temporaryEvolutions[$temporaryEvolutionId])) {
                    continue;
                }

                $temporaryEvolutions[$temporaryEvolutionId]->setAssetBundleId(
                    $temporaryEvolution->assetBundleValue,
                );
            }
        }
    }

    /** @param list<stdClass> $list */
    private function addForms(array $list, PokemonCollection $pokemonCollection): void
    {
        foreach ($list as $item) {
            $matches = [];
            if (! preg_match('~^FORMS_V(?<DexNr>\d{4})_POKEMON_(?<name>.*)$~i', (string) $item->templateId, $matches)) {
                continue;
            }

            $dexNr   = (int) $matches['DexNr'];
            $pokemon = $pokemonCollection->getByDexId($dexNr);
            if ($pokemon === null) {
                continue;
            }

            $pokemonFormCollection = PokemonFormCollection::createFromGameMaster($item->data);

            $overwriteDefaultForms = [];

            foreach ($pokemonFormCollection->getPokemonForms() as $index => $pokemonForm) {
                if ($index === 0) {
                    $pokemon = $pokemon->withPokemonForm($pokemonForm);
                    $pokemonCollection->add($pokemon);
                    continue;
                }

                foreach ($pokemon->getPokemonRegionForms() as $pokemonRegionForm) {
                    if ($pokemonRegionForm->getFormId() !== $pokemonForm->getId()) {
                        continue;
                    }

                    $pokemonRegionForm = $pokemonRegionForm->withPokemonForm($pokemonForm);
                    $pokemon           = $pokemon->withAddedPokemonRegionForm($pokemonRegionForm);
                    $pokemonCollection->add($pokemon);

                    if (
                        $pokemon->getPokemonForm() !== null
                        || ! $pokemonRegionForm->isSameFormAsBasePokemon($pokemon)
                    ) {
                        continue;
                    }

                    $overwriteDefaultForms[] = $pokemonForm;
                }
            }

            if (count($overwriteDefaultForms) !== 1) {
                continue;
            }

            $pokemon->overwriteDefaultPokemonForm($overwriteDefaultForms[0]);
        }
    }
}
