<?php

declare(strict_types=1);

use PokemonGoLingen\PogoAPI\IO\RemoteFileLoader;
use PokemonGoLingen\PogoAPI\Parser\MasterDataParser;
use PokemonGoLingen\PogoAPI\Parser\TranslationParser;
use PokemonGoLingen\PogoAPI\Renderer\PokemonRenderer;
use PokemonGoLingen\PogoAPI\Util\GenerationDeterminer;

require __DIR__ . '/../vendor/autoload.php';

function writeLine(string $input): void {
    printf('[%s] %s'. PHP_EOL, date('H:i:s'), $input);
}

$remoteFileLoader = new RemoteFileLoader();

writeLine(sprintf('Downloading file %s', 'GAME_MASTER_LATEST'));
$remoteFileLoader->load('https://raw.githubusercontent.com/PokeMiners/game_masters/master/latest/latest.json')
    ->saveTo(__DIR__ . '/../data/tmp/GAME_MASTER_LATEST.json');


foreach (TranslationParser::LANGUAGES as $language) {
    writeLine(sprintf('Downloading file %s', $language));
    $remoteFileLoader->load(sprintf(
        'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Texts/Latest%%20APK/%s.txt',
        $language
    ))->saveTo(sprintf(
        '%s/../data/tmp/latest_apk_%s.txt',
        __DIR__,
        $language
    ));

    $remoteFileLoader->load(sprintf(
        'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Texts/Latest%%20Remote/%s.txt',
        $language
    ))->saveTo(sprintf(
        '%s/../data/tmp/latest_remote_%s.txt',
        __DIR__,
        $language
    ));
}

writeLine(sprintf('Parse Files'));

$masterData = new MasterDataParser();
$masterData->parseFile(__DIR__ . '/../data/tmp/GAME_MASTER_LATEST.json');

$translationLoader = new TranslationParser(__DIR__ . '/../data/tmp/');
$translations      = [];
foreach (TranslationParser::LANGUAGES as $languageName) {
    $translations[] = $translationLoader->loadLanguage($languageName);
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

        $files['pokedex/name/' . $regionForm->getFormId()][] = $renderedPokemon;
        $files['pokedex/region/' . $regionName][]            = $renderedPokemon;
    }
}

$apidir = __DIR__ . '/../public/api/';


writeLine(sprintf('Generate API'));

foreach ($files as $file => $data) {
    @mkdir($apidir . dirname($file), 0777, true);
    if (count($data) === 1) {
        $data = reset($data);
    }

    file_put_contents($apidir . $file . '.json', json_encode($data, JSON_PRETTY_PRINT));
}

$date = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
$format = $date->format('Y-m-d H:i');
file_put_contents(
    __DIR__ . '/../public/version.css',
    <<<CSS
    #last-generated-display::before {
        content: "$format (UTC)";
    }
    CSS
);