<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI\Parser;

use Exception;
use PokemonGoApi\PogoAPI\Collections\PokemonAssetsCollection;
use PokemonGoApi\PogoAPI\Collections\QuestsCollection;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\AttacksCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\ItemsCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Collections\PokemonCollection;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\EvolutionQuest;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\Item;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonCombatMove;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonForms;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\PokemonMove;
use PokemonGoApi\PogoAPI\Parser\GameMaster\Struct\TemporaryEvolutionSettings;
use PokemonGoApi\PogoAPI\Types\Pokemon;
use PokemonGoApi\PogoAPI\Types\PokemonForm;
use Psr\Log\LoggerInterface;
use Throwable;
use function count;
use function file_get_contents;
use function preg_match;
use function str_contains;
use function str_starts_with;

class MasterDataParser
{
    private ItemsCollection $itemsCollection;

    private PokemonCollection $pokemonCollection;

    private AttacksCollection $attacksCollection;

    private QuestsCollection $questsCollection;

    public function __construct(
        private readonly PokemonAssetsCollection $pokemonAssetsCollection,
        private readonly LoggerInterface $logger,
    ) {
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

        /** @var list<array> $list */
        $list                  = JsonParser::decodeToFullArray($fileContent);
        $this->itemsCollection = $this->parseItems($list);

        $this->attacksCollection = $this->parseMoves($list);
        $this->addCombatMoves($list, $this->attacksCollection);

        $this->pokemonCollection = $this->parsePokemon($list);
        $this->questsCollection  = $this->parseQuests($list);

        $this->addTemporaryEvolutions($list, $this->pokemonCollection);
//        $this->addForms($list, $this->pokemonCollection);
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

    /** @param array<int, array> $list */
    private function parseItems(array $list): ItemsCollection
    {
        $itemsCollection = new ItemsCollection();
        foreach ($list as $item) {
            if (! str_starts_with($item['templateId'], 'ITEM_')) {
                continue;
            }

            try {
                $itemObj = GameMasterMapper::map(Item::class, $item);
            } catch (Throwable $e) {
                $this->logger->warning('Failed to parse game master data: ' . $e->getMessage());
                continue;
            }

            $itemsCollection->add($itemObj);
        }

        return $itemsCollection;
    }

    /** @param array<int, array> $list */
    private function parsePokemon(array $list): PokemonCollection
    {
        $pokemonCollection = new PokemonCollection();
        foreach ($list as $item) {
            $matches = [];
            if (
                ! preg_match('~^v(?<DexNr>[0-9]{4})_POKEMON_.*~i', $item['templateId'], $matches)
                || ! isset($item['data']['pokemonSettings'])
            ) {
                continue;
            }

            try {
                $pokemon = GameMasterMapper::map(Pokemon::class, $item['data']);
            } catch (Throwable $e) {
                $this->logger->warning('Failed to parse game master data for '. $item['templateId'] .' : ' . $e->getMessage());
                continue;
            }

            $pokemonCollection->add($pokemon);

            $pokemon = $pokemon->withAddedImages(
                $this->pokemonAssetsCollection->getImages($pokemon->getDexNr()),
            );

            $pokemonCollection->add($pokemon);
        }

        return $pokemonCollection;
    }

    /** @param list<array> $list */
    private function parseMoves(array $list): AttacksCollection
    {
        $attacksCollection = new AttacksCollection();
        foreach ($list as $item) {
            if (! preg_match('~^V\d{4}_MOVE~i', $item['templateId'])) {
                continue;
            }

            try {
                $itemObj = GameMasterMapper::map(PokemonMove::class, $item);
            } catch (Throwable $e) {
                $this->logger->warning('Failed to parse game master data: ' . $e->getMessage());
                continue;
            }

            $attacksCollection->add($itemObj);
        }

        return $attacksCollection;
    }

    /** @param list<array> $list */
    private function parseQuests(array $list): QuestsCollection
    {
        $questsCollection = new QuestsCollection();
        foreach ($list as $item) {
            $matches = [];
            if (! preg_match('~(?<Quest>.*)_EVOLUTION_QUEST$~i', $item['templateId'], $matches)) {
                continue;
            }

            try {
                $itemObj = GameMasterMapper::map(EvolutionQuest::class, $item['data']);
            } catch (Throwable $e) {
                $this->logger->warning('Failed to parse game master data: ' . $e->getMessage());
                continue;
            }

            $questsCollection->add($itemObj);
        }

        return $questsCollection;
    }

    /** @param list<array> $list */
    private function addCombatMoves(array $list, AttacksCollection $attacksCollection): void
    {
        foreach ($list as $item) {
            if (
                ! preg_match(
                    '~^COMBAT_V(?<MoveId>[0-9]{4})_MOVE_(?<MoveName>.*)$~i',
                    $item['templateId'],
                )
            ) {
                continue;
            }

            try {
                $itemObj = GameMasterMapper::map(PokemonCombatMove::class, $item);
            } catch (Throwable $e) {
                $this->logger->warning('Failed to parse game master data: ' . $e->getMessage());
                continue;
            }

            $move = $attacksCollection->getById($itemObj->getMoveId());
            $move?->setCombatMove($itemObj);
        }
    }

    /** @param list<array> $list */
    private function addTemporaryEvolutions(array $list, PokemonCollection $pokemonCollection): void
    {
        foreach ($list as $item) {
            $matches = [];
            if (
                ! preg_match(
                    '~^TEMPORARY_EVOLUTION_V(?<DexNr>[0-9]{4})_POKEMON_(.*)$~i',
                    $item['templateId'],
                    $matches,
                )
            ) {
                continue;
            }

            try {
                $itemObj = GameMasterMapper::map(TemporaryEvolutionSettings::class, $item['data']);
            } catch (Throwable $e) {
                $this->logger->warning('Failed to parse game master data: ' . $e->getMessage());
                continue;
            }

            $dexNr   = (int) $matches['DexNr'];
            $pokemon = $pokemonCollection->getByDexId($dexNr);
            if (! $pokemon instanceof Pokemon) {
                continue;
            }

            $temporaryEvolutions = $pokemon->getTemporaryEvolutions();
            foreach ($temporaryEvolutions as $temporaryEvolution) {
                $temporaryEvolutions[$temporaryEvolution->getTempEvoId()] = $temporaryEvolution;
            }
            assert($itemObj instanceof TemporaryEvolutionSettings);

            foreach ($itemObj->evolutions as $temporaryEvolution) {
                $temporaryEvolutionId = $temporaryEvolution['evoId'];
                if (! isset($temporaryEvolutions[$temporaryEvolutionId])) {
                    continue;
                }

                $temporaryEvolutions[$temporaryEvolutionId]->setAssetBundleId(
                    $temporaryEvolution['bundleId'],
                );
            }
        }
    }

    /** @param list<array> $list */
    private function addForms(array $list, PokemonCollection $pokemonCollection): void
    {
        foreach ($list as $item) {
            $matches = [];
            if (! preg_match('~^FORMS_V(?<DexNr>\d{4})_POKEMON_(?<name>.*)$~i', $item['templateId'], $matches)) {
                continue;
            }

            $dexNr   = (int) $matches['DexNr'];
            $pokemon = $pokemonCollection->getByDexId($dexNr);
            if (! $pokemon instanceof Pokemon) {
                continue;
            }
            
            try {
                $itemObj = GameMasterMapper::map(PokemonForms::class, $item['data']);
                assert($itemObj instanceof PokemonForms);
            } catch (Throwable $e) {
                $this->logger->warning('Failed to parse game master data: ' . $e->getMessage());
                continue;
            }

            $overwriteDefaultForms = [];

            foreach ($itemObj->getPokemonForms() as $pokemonForm) {
                foreach ($pokemon->getPokemonRegionForms() as $pokemonRegionForm) {
                    if ($pokemonRegionForm->getFormId() !== $pokemonForm->getId()) {
                        continue;
                    }

                    $pokemonRegionForm = $pokemonRegionForm->withPokemonForm($pokemonForm);
                    $pokemon           = $pokemon->withAddedPokemonRegionForm($pokemonRegionForm);
                    $pokemonCollection->add($pokemon);

                    if (
                        $pokemon->getPokemonForm() instanceof PokemonForm
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
