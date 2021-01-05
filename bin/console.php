<?php

declare(strict_types=1);

use PokemonGoLingen\PogoAPI\CacheLoader;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoLingen\PogoAPI\IO\RemoteFileLoader;
use PokemonGoLingen\PogoAPI\Parser\CustomTranslations;
use PokemonGoLingen\PogoAPI\Parser\LeekduckParser;
use PokemonGoLingen\PogoAPI\Parser\MasterDataParser;
use PokemonGoLingen\PogoAPI\Parser\TranslationParser;
use PokemonGoLingen\PogoAPI\Renderer\PokemonRenderer;
use PokemonGoLingen\PogoAPI\Renderer\RaidBossGraphicRenderer;
use PokemonGoLingen\PogoAPI\Renderer\RaidBossListRenderer;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;
use PokemonGoLingen\PogoAPI\Util\GenerationDeterminer;

require __DIR__ . '/../vendor/autoload.php';

$tmpDir      = __DIR__ . '/../data/tmp/';
$cacheLoader = new CacheLoader(new RemoteFileLoader(), new DateTimeImmutable(), $tmpDir);

printf('[%s] %s' . PHP_EOL, date('H:i:s'), 'Parse Files');
$masterData = new MasterDataParser();
$masterData->parseFile($cacheLoader->fetchGameMasterFile());

$languageFiles      = $cacheLoader->fetchLanguageFiles();
$translationLoader  = new TranslationParser();
$translations       = new TranslationCollectionCollection();
$customTranslations = CustomTranslations::load();
foreach (TranslationParser::LANGUAGES as $languageName) {
    if (! isset($languageFiles['apk'][$languageName])) {
        continue;
    }

    $translations->addTranslationCollection(
        $translationLoader->loadLanguage(
            $languageName,
            $languageFiles['apk'][$languageName],
            $languageFiles['remote'][$languageName],
            $customTranslations[$languageName]
        )
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

$apidir = $tmpDir . 'api/';

printf('[%s] %s' . PHP_EOL, date('H:i:s'), 'Generate API');

foreach ($files as $file => $data) {
    @mkdir($apidir . dirname($file), 0777, true);
    if (count($data) === 1) {
        $data = reset($data);
    }

    file_put_contents($apidir . $file . '.json', json_encode($data));
}

$raidBossHtmlList = $cacheLoader->fetchRaidBosses();
$leekduckParser   = new LeekduckParser($masterData->getPokemonCollection());
$raidBosses       = $leekduckParser->parseRaidBosses($raidBossHtmlList);

$raidOverwrites = json_decode(json_encode(
    (array) (simplexml_load_string(file_get_contents(__DIR__ . '/../data/raidOverwrites.xml') ?: '') ?: [])
) ?: '[]');
foreach ($raidOverwrites->raidboss as $raidOverwrite) {
    $start = new DateTimeImmutable(
        $raidOverwrite->startDate->date,
        isset($raidOverwrite->startDate->timezone) ? new DateTimeZone($raidOverwrite->startDate->timezone) : null
    );
    $end   = new DateTimeImmutable(
        $raidOverwrite->endDate->date,
        isset($raidOverwrite->endDate->timezone) ? new DateTimeZone($raidOverwrite->endDate->timezone) : null
    );
    $now   = new DateTimeImmutable();
    if ($now < $start || $now > $end) {
        continue;
    }

    $pokemon = $masterData->getPokemonCollection()->get($raidOverwrite->pokemon);
    if ($pokemon === null) {
        continue;
    }

    $raidBosses->add(
        new RaidBoss(
            $raidOverwrite->form ?? $raidOverwrite->pokemon,
            $raidOverwrite->shiny === 'true',
            $raidOverwrite->level,
            $pokemon,
            null
        )
    );
}

$raidBossImageRenderer = new RaidBossGraphicRenderer();
foreach ($translations->getCollections() as $translationName => $translationCollection) {
    $raidListDir = sprintf('%s/graphics/%s', $apidir, $translationName);
    if (! is_readable($raidListDir)) {
        @mkdir($raidListDir, 0777, true);
    }

    file_put_contents(
        sprintf('%s/raidlist.svg', $raidListDir),
        $raidBossImageRenderer->buildGraphic($raidBosses, $translationCollection)
    );
}

$raidListRenderer = new RaidBossListRenderer();
$raidBossesList   = $raidListRenderer->buildList($raidBosses, $translations);

file_put_contents($apidir . 'raidboss.json', json_encode([
    'currentList' => $raidBossesList,
    'graphics' => [
        'German' => [
            'svg' => '/api/graphics/German/raidlist.svg',
            'png' => '/api/graphics/German/raidlist.png',
            'sha512' => hash_file('sha512', $apidir . '/graphics/German/raidlist.svg'),
        ],
        'English' => [
            'svg' => '/api/graphics/English/raidlist.svg',
            'png' => '/api/graphics/English/raidlist.png',
            'sha512' => hash_file('sha512', $apidir . '/graphics/English/raidlist.svg'),
        ],
    ],
]));

$hashFiles = [
    $apidir . 'raidboss.json',
    $apidir . 'pokedex.json',
];

$hasChanges = $cacheLoader->hasChanges($hashFiles);

file_put_contents($apidir . 'hashes.json', json_encode(
    $cacheLoader->updateCaches($hashFiles),
    JSON_PRETTY_PRINT
));

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

echo sprintf('::set-output name=HAS_CHANGES::%s', $hasChanges ? 'true' : 'false');
