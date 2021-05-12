<?php

declare(strict_types=1);

use PokemonGoLingen\PogoAPI\CacheLoader;
use PokemonGoLingen\PogoAPI\Collections\TranslationCollectionCollection;
use PokemonGoLingen\PogoAPI\IO\RemoteFileLoader;
use PokemonGoLingen\PogoAPI\Logger\PrintLogger;
use PokemonGoLingen\PogoAPI\Parser\CustomTranslations;
use PokemonGoLingen\PogoAPI\Parser\LeekduckParser;
use PokemonGoLingen\PogoAPI\Parser\MasterDataParser;
use PokemonGoLingen\PogoAPI\Parser\PokebattlerParser;
use PokemonGoLingen\PogoAPI\Parser\PokemonGoImagesParser;
use PokemonGoLingen\PogoAPI\Parser\SilphRoadResearchTaskParser;
use PokemonGoLingen\PogoAPI\Parser\TranslationParser;
use PokemonGoLingen\PogoAPI\RaidOverwrite\RaidBossOverwrite;
use PokemonGoLingen\PogoAPI\Renderer\PokemonRenderer;
use PokemonGoLingen\PogoAPI\Renderer\RaidBossGraphicRenderer;
use PokemonGoLingen\PogoAPI\Renderer\RaidBossListRenderer;
use PokemonGoLingen\PogoAPI\Renderer\ResearchTasksRenderer;
use PokemonGoLingen\PogoAPI\Renderer\Types\RaidBossGraphicConfig;
use PokemonGoLingen\PogoAPI\Types\BattleConfiguration;
use PokemonGoLingen\PogoAPI\Types\RaidBoss;
use PokemonGoLingen\PogoAPI\Util\GenerationDeterminer;

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('UTC');

$logger = new PrintLogger();

$tmpDir      = __DIR__ . '/../data/tmp/';
$cacheLoader = new CacheLoader(
    new RemoteFileLoader($logger),
    new DateTimeImmutable(),
    $tmpDir,
    $logger
);

$logger->debug('Parse Files');
$masterData = new MasterDataParser();
$masterData->parseFile($cacheLoader->fetchGameMasterFile());

$pokemonImagesParser     = new PokemonGoImagesParser();
$pokemonAssetsCollection = $pokemonImagesParser->parseFile($cacheLoader->fetchPokemonImages());

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

$pokemonRenderer = new PokemonRenderer($translations, $pokemonAssetsCollection);
$files           = [];

$logger->debug('Generate Pokemon');
foreach ($masterData->getPokemonCollection()->toArray() as $pokemon) {
    $renderedPokemon = $pokemonRenderer->render($pokemon, $masterData->getAttacksCollection());

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

$logger->debug('Generate Tasks');
$taskParser    = new SilphRoadResearchTaskParser(
    $masterData->getPokemonCollection(),
    $translations->getCollection('English')
);
$tasks         = $taskParser->parseTasks(
    $cacheLoader->fetchTasksFromSilphroad()
);
$tasksRenderer = new ResearchTasksRenderer($translations, $masterData->getPokemonCollection());

$files['quests'] = $tasksRenderer->render(...$tasks);

$apidir = $tmpDir . 'api/';
foreach ($files as $file => $data) {
    @mkdir($apidir . dirname($file), 0777, true);
    if (count($data) === 1) {
        $data = reset($data);
    }

    file_put_contents($apidir . $file . '.json', json_encode($data, JSON_PRETTY_PRINT));
}

$logger->debug('Generate Raidbosses');
$raidBossHtmlList = $cacheLoader->fetchRaidBossesFromLeekduck();
$leekduckParser   = new LeekduckParser($masterData->getPokemonCollection());
$raidBosses       = $leekduckParser->parseRaidBosses($raidBossHtmlList);

$logger->debug(
    sprintf('Got %d remote raid bosses', count($raidBosses->toArray())),
    array_map(
        static fn (RaidBoss $raidBoss): string => $raidBoss->getPokemonId(),
        $raidBosses->toArray()
    )
);
$xmlData           = (array) (simplexml_load_string(
    file_get_contents(__DIR__ . '/../data/raidOverwrites.xml') ?: ''
) ?: []);
$raidBossOverwrite = new RaidBossOverwrite(
    json_decode(json_encode($xmlData['raidboss'] ?? []) ?: '[]'),
    $masterData->getPokemonCollection(),
    $logger
);
$raidBossOverwrite->overwrite($raidBosses);

$logger->debug(
    sprintf('Got %d raid bosses to render', count($raidBosses->toArray())),
    array_map(
        static fn (RaidBoss $raidBoss): string => $raidBoss->getPokemonId(),
        $raidBosses->toArray()
    )
);

$pokebattlerParser        = new PokebattlerParser(
    $cacheLoader,
    BattleConfiguration::easy(),
    BattleConfiguration::normal(),
    BattleConfiguration::hard()
);
$raidBossesWithDifficulty = $pokebattlerParser->appendResults($raidBosses);

$logger->debug('Generate Images');

$windowSize            = '0,0';
$raidBossImageRenderer = new RaidBossGraphicRenderer();
foreach ($translations->getCollections() as $translationName => $translationCollection) {
    $raidListDir = sprintf('%s/graphics/%s', $apidir, $translationName);
    if (! is_readable($raidListDir)) {
        @mkdir($raidListDir, 0777, true);
    }

    $raidGraphic = $raidBossImageRenderer->buildGraphic(
        $raidBossesWithDifficulty,
        $translationCollection,
        new RaidBossGraphicConfig()
    );

    file_put_contents(
        sprintf('%s/raidlist.svg', $raidListDir),
        $raidGraphic->getImageContent()
    );

    $raidGraphicB = $raidBossImageRenderer->buildGraphic(
        $raidBossesWithDifficulty,
        $translationCollection,
        new RaidBossGraphicConfig(RaidBossGraphicConfig::ORDER_LOW_TO_HIGH, false)
    );

    file_put_contents(
        sprintf('%s/raidlist_b.svg', $raidListDir),
        $raidGraphicB->getImageContent()
    );


    $windowSize = $raidGraphic->getWindowSize();
}

$logger->debug('Generate Raidboss.json');
$raidListRenderer = new RaidBossListRenderer();
$raidBossesList   = $raidListRenderer->buildList($raidBossesWithDifficulty, $translations);

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
], JSON_PRETTY_PRINT));

$hashFiles = [
    $apidir . 'raidboss.json',
    $apidir . 'pokedex.json',
    $apidir . 'quests.json',
];

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
$hasChanges = $cacheLoader->hasChanges();
$logger->debug(sprintf('CACHE_STATUS=%s', $hasChanges ? 'HAS_CHANGES' : 'NO_CHANGES'));
echo sprintf('::set-output name=CACHE_STATUS::%s' . PHP_EOL, $hasChanges ? 'HAS_CHANGES' : 'NO_CHANGES');
echo sprintf('::set-output name=WINDOW_SIZE::%s' . PHP_EOL, $windowSize);
