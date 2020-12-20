<?php

declare(strict_types=1);

use PokemonGoLingen\PogoAPI\CacheLoader;
use PokemonGoLingen\PogoAPI\Parser\LeekduckParser;
use PokemonGoLingen\PogoAPI\Parser\MasterDataParser;
use PokemonGoLingen\PogoAPI\Parser\TranslationParser;
use PokemonGoLingen\PogoAPI\Renderer\PokemonRenderer;
use PokemonGoLingen\PogoAPI\Util\GenerationDeterminer;

require __DIR__ . '/../vendor/autoload.php';

$cacheLoader = new CacheLoader(__DIR__ . '/../data/tmp/');

printf('[%s] %s' . PHP_EOL, date('H:i:s'), 'Parse Files');
$masterData = new MasterDataParser();
$masterData->parseFile($cacheLoader->fetchGameMasterFile());

$languageFiles     = $cacheLoader->fetchLanguageFiles();
$translationLoader = new TranslationParser();
$translations      = [];
foreach (TranslationParser::LANGUAGES as $languageName) {
    $translations[] = $translationLoader->loadLanguage(
        $languageName,
        $languageFiles['apk'][$languageName],
        $languageFiles['remote'][$languageName]
    );
}

$renderer = new PokemonRenderer($translations);
$files    = [];
foreach ($masterData->getPokemonCollection()->toArray() as $pokemon) {
    $renderedPokemon = $renderer->render($pokemon, $masterData->getAttacksCollection());

    $generation = GenerationDeterminer::fromDexNr($pokemon->getDexNr());

    $files['pokedex'][]                            = $renderedPokemon;
    $files['pokedex/generation/' . $generation][]  = $renderedPokemon;
    $files['pokedex/id/' . $pokemon->getDexNr()][] = $renderedPokemon;
    $files['pokedex/name/' . $pokemon->getId()][]  = $renderedPokemon;
    foreach ($pokemon->getTemporaryEvolutions() as $temporaryEvolution) {
        $files['pokedex/name/' . $temporaryEvolution->getId()][] = $renderedPokemon;
        $files['pokedex/mega'][]                                 = $renderedPokemon;
    }

    foreach ($pokemon->getPokemonRegionForms() as $regionForm) {
        if (strlen($regionForm->getFormId()) <= strlen($regionForm->getId()) + 1) {
            continue;
        }

        $regionName = strtolower(substr($regionForm->getFormId(), strlen($regionForm->getId()) + 1));
        if (! in_array($regionName, ['alola', 'galarian'])) {
            continue;
        }

        $files['pokedex/region/' . $regionName][] = $renderedPokemon;
    }
}

$apidir = __DIR__ . '/../public/api/';

printf('[%s] %s' . PHP_EOL, date('H:i:s'), 'Generate API');

foreach ($files as $file => $data) {
    @mkdir($apidir . dirname($file), 0777, true);
    if (count($data) === 1) {
        $data = reset($data);
    }

    file_put_contents($apidir . $file . '.json', json_encode($data));
}

$raidBossList   = $cacheLoader->fetchRaidBosses();
$leekduckParser = new LeekduckParser();
$raidBosses     = $leekduckParser->parseRaidBosses($raidBossList);
$raidBossHash   = hash('sha512', json_encode($raidBosses) ?: '');

if (! $cacheLoader->hasRaidBossCacheEntry($raidBossHash) || ! is_file($apidir . 'raidboss.json')) {
    $cacheLoader->persistRaidBossCacheEntry($raidBossHash);
    file_put_contents($apidir . 'raidboss.json', json_encode([
        'currentList'   => $raidBosses,
        'lastGenerated' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DATE_RFC3339_EXTENDED),
    ]));
}

$date   = new DateTimeImmutable('now', new DateTimeZone('UTC'));
$format = $date->format('Y-m-d H:i');
file_put_contents(
    __DIR__ . '/../public/version.css',
    <<<CSS
    #last-generated-display::before {
        content: "$format (UTC)";
    }
    CSS
);
