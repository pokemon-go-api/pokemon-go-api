<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI;

use JsonException;
use PokemonGoLingen\PogoAPI\IO\RemoteFileLoader;
use stdClass;

use function array_filter;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use function json_decode;
use function json_encode;
use function pathinfo;
use function printf;

use const JSON_THROW_ON_ERROR;
use const PATHINFO_FILENAME;

class CacheLoader
{
    private const CACHE_FILE              = 'hashes.json';
    private const GAME_MASTER_LATEST_FILE = 'https://api.github.com/repos/PokeMiners/game_masters/contents/latest';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const LATEST_REMOTE_LANGUAGE_FILE = 'https://api.github.com/repos/PokeMiners/pogo_assets/contents/Texts/Latest%20Remote';
    //phpcs:ignore Generic.Files.LineLength.TooLong
    private const LATEST_APK_LANGUAGE_FILE = 'https://api.github.com/repos/PokeMiners/pogo_assets/contents/Texts/Latest%20APK';

    private string $cacheDir;
    /** @var array<string, string> */
    private array $cachedData = [];
    private RemoteFileLoader $remoteFileLoader;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;

        $this->remoteFileLoader = new RemoteFileLoader();

        if (! is_file($this->cacheDir . self::CACHE_FILE)) {
            return;
        }

        try {
            $this->cachedData = json_decode(
                file_get_contents($this->cacheDir . self::CACHE_FILE) ?: '[]',
                true,
                512,
                JSON_THROW_ON_ERROR
            ) ?: [];
        } catch (JsonException $jsonException) {
        }
    }

    public function __destruct()
    {
        file_put_contents($this->cacheDir . self::CACHE_FILE, json_encode($this->cachedData));
    }

    public function fetchGameMasterFile(): string
    {
        $gameMasterLatestResponse = $this->remoteFileLoader->load(self::GAME_MASTER_LATEST_FILE)->getContent() ?: '[]';

        $gameMasterLatest = json_decode($gameMasterLatestResponse, false, 512, JSON_THROW_ON_ERROR) ?: [];
        $latestJsonFile   = array_filter(
            $gameMasterLatest,
            static fn (stdClass $fileMeta): bool => $fileMeta->name === 'latest.json'
        )[0] ?? [];

        $cacheFile = $this->cacheDir . 'GAME_MASTER_LATEST.json';

        if (
            ! isset($this->cachedData[$latestJsonFile->path])
            || $latestJsonFile->sha !== $this->cachedData[$latestJsonFile->path]
        ) {
            $this->cachedData[$latestJsonFile->path] = $latestJsonFile->sha;
            //phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
            $this->remoteFileLoader->load($latestJsonFile->download_url)->saveTo($cacheFile);
            printf("put %s in cache\n", $latestJsonFile->path);
        }

        return $cacheFile;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function fetchLanguageFiles(): array
    {
        $fileApiResponse = $this->remoteFileLoader->load(self::LATEST_REMOTE_LANGUAGE_FILE)->getContent() ?: '[]';
        $latestTexts     = json_decode($fileApiResponse, false, 512, JSON_THROW_ON_ERROR) ?: [];

        $fileApiResponse = $this->remoteFileLoader->load(self::LATEST_APK_LANGUAGE_FILE)->getContent() ?: '[]';
        $apkTexts        = json_decode($fileApiResponse, false, 512, JSON_THROW_ON_ERROR) ?: [];

        $allTexts = [
            'remote' => $latestTexts,
            'apk'    => $apkTexts,
        ];

        $output = [];
        foreach ($allTexts as $id => $files) {
            foreach ($files as $file) {
                if ($file->type !== 'file') {
                    continue;
                }

                $cacheFile = $this->cacheDir . 'latest_' . $id . '_' . $file->name;
                if (! isset($this->cachedData[$file->path]) || $file->sha !== $this->cachedData[$file->path]) {
                    printf("put %s in cache\n", $file->path);
                    $this->cachedData[$file->path] = $file->sha;
                    //phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
                    $this->remoteFileLoader->load($file->download_url)->saveTo($cacheFile);
                }

                $output[$id][pathinfo($file->name, PATHINFO_FILENAME)] = $cacheFile;
            }
        }

        return $output;
    }

    public function fetchRaidBosses(): string
    {
        $cacheFile = $this->cacheDir . 'raidlist.html';
        $this->remoteFileLoader->load('https://leekduck.com/boss/')->saveTo($cacheFile);

        return $cacheFile;
    }

    public function hasRaidBossCacheEntry(string $raidBossHash): bool
    {
        return ($this->cachedData['raidbosses.json'] ?? null) === $raidBossHash;
    }

    public function persistRaidBossCacheEntry(string $raidBossHash): void
    {
        $this->cachedData['raidbosses.json'] = $raidBossHash;
    }
}
