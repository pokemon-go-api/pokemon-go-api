<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI;

use DateTimeImmutable;
use JsonException;
use PokemonGoApi\PogoAPI\IO\Directory;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\IO\RemoteFileLoader;
use Psr\Log\LoggerInterface;
use stdClass;

use function abs;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_values;
use function assert;
use function basename;
use function date;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function floor;
use function hash_file;
use function is_file;
use function json_encode;
use function pathinfo;
use function rtrim;
use function sleep;
use function sprintf;

use const DATE_ATOM;
use const PATHINFO_FILENAME;

class CacheLoader
{
    private const string CACHE_FILE = 'hashes.json';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const string GAME_MASTER_LATEST_FILE = 'https://api.github.com/repos/PokeMiners/game_masters/contents/latest';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const string LATEST_REMOTE_LANGUAGE_FILE = 'https://api.github.com/repos/PokeMiners/pogo_assets/contents/Texts/Latest%20Remote';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const string LATEST_APK_LANGUAGE_FILE = 'https://api.github.com/repos/PokeMiners/pogo_assets/contents/Texts/Latest%20APK';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const string IMAGES_CONTENT = 'https://api.github.com/repos/PokeMiners/pogo_assets/contents/Images';

    /** @var array<string, mixed> */
    private array $cachedData = [];
    /** @var array<string, mixed> */
    private array $originalCachedData = [];

    public function __construct(
        private readonly RemoteFileLoader $remoteFileLoader,
        private readonly DateTimeImmutable $clock,
        private readonly string $cacheDir,
        private readonly LoggerInterface $logger,
    ) {
        Directory::create($this->getCacheDir());

        if (! is_file($this->getCacheDir() . self::CACHE_FILE)) {
            return;
        }

        try {
            $this->originalCachedData = $this->cachedData = JsonParser::decodeToArray(
                file_get_contents($this->getCacheDir() . self::CACHE_FILE) ?: '[]',
            );
        } catch (JsonException) {
        }
    }

    public function __destruct()
    {
        file_put_contents($this->getCacheDir() . self::CACHE_FILE, json_encode($this->cachedData));
    }

    public function fetchGameMasterFile(): string
    {
        $cacheFile = $this->getCacheDir() . 'GAME_MASTER_LATEST.json';
        $cacheKey  = 'github/game_master';

        if (file_exists($cacheFile) && $this->wasRunningInThePastMinutes()) {
            return $cacheFile;
        }

        $gameMasterLatestResponse = $this->remoteFileLoader->load(self::GAME_MASTER_LATEST_FILE)->getContent() ?: '[]';

        /** @var array<string, stdClass> $gameMasterLatest */
        $gameMasterLatest = JsonParser::decodeToArray($gameMasterLatestResponse);
        $latestJsonFile   = array_values(array_filter(
            $gameMasterLatest,
            static fn (stdClass $fileMeta): bool => $fileMeta->name === 'latest.json',
        ))[0] ?? new stdClass();

        assert($latestJsonFile instanceof stdClass);

        if ($latestJsonFile->sha === ($this->cachedData[$cacheKey] ?? null)) {
            return $cacheFile;
        }

        $this->cachedData[$cacheKey] = $latestJsonFile->sha;
        //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
        $this->remoteFileLoader->load($latestJsonFile->download_url)->saveTo($cacheFile);
        $this->logger->debug(
            sprintf(
                '[CacheLoader] Missing cache entry for %s',
                $latestJsonFile->path,
            ),
            ['newHash' => $latestJsonFile->sha],
        );

        return $cacheFile;
    }

    public function fetchPokemonImages(): string
    {
        $cacheFile = $this->getCacheDir() . 'pokemon_images.json';
        $cacheKey  = 'github/pokemon_images';

        if ($this->wasRunningInThePastMinutes()) {
            return $cacheFile;
        }

        $imagesFolderResponse = $this->remoteFileLoader->load(self::IMAGES_CONTENT)->getContent() ?: '[]';

        /** @var array<string, stdClass> $imagesFolderResult */
        $imagesFolderResult  = JsonParser::decodeToArray($imagesFolderResponse);
        $pokemonImagesFolder = array_values(array_filter(
            $imagesFolderResult,
            static fn (stdClass $meta): bool => $meta->name === 'Pokemon',
        ))[0] ?? null;

        if ($pokemonImagesFolder === null) {
            return $cacheFile;
        }

        if ($pokemonImagesFolder->sha === ($this->cachedData[$cacheKey] ?? null)) {
            return $cacheFile;
        }

        $this->cachedData[$cacheKey] = $pokemonImagesFolder->sha;
        //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
        $imagesFolderContent   = $this->remoteFileLoader->load($pokemonImagesFolder->git_url)->getContent() ?: '{}';
        $imagesFolderStructure = JsonParser::decodeToObject($imagesFolderContent);

        $cacheFileContent = [];
        foreach ($imagesFolderStructure->tree as $treeItem) {
            if ($treeItem->type === 'tree') {
                $subFolderContent   = $this->remoteFileLoader->load($treeItem->url)->getContent() ?: '{}';
                $subFolderStructure = JsonParser::decodeToObject($subFolderContent);
                foreach ($subFolderStructure->tree as $subFolderTreeItem) {
                    $cacheFileContent[] = $treeItem->path . '/' . $subFolderTreeItem->path;
                }
            } else {
                $cacheFileContent[] = $treeItem->path;
            }
        }

        file_put_contents($cacheFile, json_encode($cacheFileContent));

        $this->logger->debug(
            sprintf(
                '[CacheLoader] Missing cache entry for %s',
                $cacheKey,
            ),
            ['newHash' => $pokemonImagesFolder->sha],
        );

        return $cacheFile;
    }

    /** @return array<string, array<string, string>> */
    public function fetchLanguageFiles(): array
    {
        if ($this->wasRunningInThePastMinutes()) {
            /** @var array<string, array<string, string>> $response */
            $response = JsonParser::decodeToFullArray(json_encode($this->cachedData['languageFiles']) ?: '[]');

            return $response;
        }

        $fileApiResponse = $this->remoteFileLoader->load(self::LATEST_REMOTE_LANGUAGE_FILE)->getContent() ?: '[]';
        $latestTexts     = JsonParser::decodeToArray($fileApiResponse);

        $fileApiResponse = $this->remoteFileLoader->load(self::LATEST_APK_LANGUAGE_FILE)->getContent() ?: '[]';
        $apkTexts        = JsonParser::decodeToArray($fileApiResponse);

        $allTexts = [
            'remote' => $latestTexts,
            'apk'    => $apkTexts,
        ];

        $output = [];
        foreach ($allTexts as $textType => $files) {
            foreach ($files as $file) {
                assert($file instanceof stdClass);
                if ($file->type !== 'file') {
                    continue;
                }

                $cacheKey  = 'github/text_latest_' . $textType . '_' . $file->name;
                $cacheFile = $this->getCacheDir() . 'latest_' . $textType . '_' . $file->name;
                if ($file->sha !== ($this->cachedData[$cacheKey] ?? null)) {
                    $this->logger->debug(
                        sprintf(
                            '[CacheLoader] Missing cache entry for %s',
                            $cacheKey,
                        ),
                        ['newHash' => $file->sha],
                    );
                    $this->cachedData[$cacheKey] = $file->sha;
                    //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
                    $this->remoteFileLoader->load($file->download_url)->saveTo($cacheFile);
                }

                $output[$textType][pathinfo((string) $file->name, PATHINFO_FILENAME)] = $cacheFile;
            }
        }

        $this->cachedData['languageFiles'] = $output;

        return $output;
    }

    public function fetchRaidBossesFromLeekduck(): string
    {
        $raidBossUrl = 'https://leekduck.com/boss/';
        $cacheFile   = $this->getCacheDir() . 'raidlist_leekduck.html';
        $cacheKey    = 'leekduck_LastFetched';
        $cacheEntry  = $this->cachedData[$cacheKey] ?? null;

        if ($this->wasRunningInThePastMinutes()) {
            return $cacheFile;
        }

        $lastModified = $this->remoteFileLoader->receiveResponseHeader(
            $raidBossUrl,
            'last-modified',
        ) ?: date(DATE_ATOM);

        if ($cacheEntry === $lastModified) {
            return $cacheFile;
        }

        $this->cachedData[$cacheKey] = $lastModified;
        $this->remoteFileLoader->load('https://leekduck.com/boss/')->saveTo($cacheFile);
        $this->logger->debug(
            sprintf(
                '[CacheLoader] Missing cache entry for %s',
                $cacheKey,
            ),
            ['lastModified' => $lastModified],
        );

        return $cacheFile;
    }

    public function fetchPokebattlerUrl(string $pokebattlerApiUrl, string $cacheKey): string
    {
        $cacheFile = $this->getCacheDir() . 'pokebattler_' . $cacheKey . '.json';
        $cacheKey  = 'pokebattler/' . $cacheKey;
        if (array_key_exists($cacheKey, $this->cachedData) && is_file($cacheFile)) {
            return $cacheFile;
        }

        $this->logger->debug(sprintf(
            '[CacheLoader] Missing cache entry for %s',
            $cacheKey,
        ));
        $this->remoteFileLoader->load($pokebattlerApiUrl)->saveTo($cacheFile);
        $this->cachedData[$cacheKey] = date(DATE_ATOM);
        sleep(1);

        return $cacheFile;
    }

    public function fetchRaidBossesFromSerebii(): string
    {
        $cacheFile = $this->getCacheDir() . 'raidlist_serebii.html';
        $cacheKey  = $this->clock->format('Y-m-d') . '_' . floor($this->clock->format('H') / 6);

        $cacheEntry = $this->cachedData[$cacheFile] ?? null;
        if ($cacheEntry !== $cacheKey) {
            $this->remoteFileLoader->load('https://www.serebii.net/pokemongo/raidbattles.shtml')->saveTo($cacheFile);
            $this->cachedData[$cacheFile] = $cacheKey;
        }

        return $cacheFile;
    }

    public function fetchRaidBossesFromPokefansNet(): string
    {
        $cacheFile = $this->getCacheDir() . 'raidlist_pokefansNet.html';
        $cacheKey  = $this->clock->format('Y-m-d') . '_' . floor($this->clock->format('H') / 6);

        $cacheEntry = $this->cachedData[$cacheFile] ?? null;
        if ($cacheEntry !== $cacheKey) {
            $this->remoteFileLoader->load('https://pokefans.net/spiele/pokemon-go/raid-bosse')->saveTo($cacheFile);
            $this->cachedData[$cacheFile] = $cacheKey;
        }

        return $cacheFile;
    }

    /**
     * @param array<int, string> $files
     *
     * @return array<string, array<string,string>>
     */
    public function updateCaches(array $files): array
    {
        $hashes = [];
        foreach ($files as $file) {
            $hashes[basename($file)] = hash_file('sha512', $file) ?: '';
        }

        $this->logger->debug('[CacheLoader] Update hashes.json', array_keys($hashes));

        $this->cachedData['hashes.json'] = json_encode($hashes) ?: '';

        return ['sha512' => $hashes];
    }

    public function hasChanges(): bool
    {
        return ($this->originalCachedData['hashes.json'] ?? null) !== ($this->cachedData['hashes.json'] ?? null);
    }

    private function wasRunningInThePastMinutes(): bool
    {
        if (! file_exists($this->getCacheDir() . self::CACHE_FILE)) {
            return false;
        }

        return abs($this->clock->getTimestamp() - filemtime($this->getCacheDir() . self::CACHE_FILE)) < 300;
    }

    private function getCacheDir(): string
    {
        return rtrim($this->cacheDir, '/') . '/';
    }
}
