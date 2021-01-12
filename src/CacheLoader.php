<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI;

use DateTimeImmutable;
use JsonException;
use PokemonGoLingen\PogoAPI\IO\RemoteFileLoader;
use RuntimeException;
use stdClass;

use function array_filter;
use function basename;
use function file_get_contents;
use function file_put_contents;
use function floor;
use function hash_file;
use function is_dir;
use function is_file;
use function json_decode;
use function json_encode;
use function mkdir;
use function pathinfo;
use function printf;
use function sprintf;

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
    /** @var array<string, string> */
    private array $originalCachedData = [];
    private RemoteFileLoader $remoteFileLoader;
    private DateTimeImmutable $clock;

    public function __construct(
        RemoteFileLoader $remoteFileLoader,
        DateTimeImmutable $clock,
        string $cacheDir
    ) {
        $this->remoteFileLoader = $remoteFileLoader;
        $this->clock            = $clock;
        $this->cacheDir         = $cacheDir;

        if (! is_dir($this->cacheDir) && ! mkdir($this->cacheDir, 0777, true)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->cacheDir));
        }

        if (! is_file($this->cacheDir . self::CACHE_FILE)) {
            return;
        }

        try {
            $this->originalCachedData = $this->cachedData = json_decode(
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
        $cacheFile = $this->cacheDir . 'GAME_MASTER_LATEST.json';

        $gameMasterLatestResponse = $this->remoteFileLoader->load(self::GAME_MASTER_LATEST_FILE)->getContent() ?: '[]';

        $gameMasterLatest = json_decode($gameMasterLatestResponse, false, 512, JSON_THROW_ON_ERROR) ?: [];
        $latestJsonFile   = array_filter(
            $gameMasterLatest,
            static fn (stdClass $fileMeta): bool => $fileMeta->name === 'latest.json'
        )[0] ?? [];

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
        foreach ($allTexts as $textType => $files) {
            foreach ($files as $file) {
                if ($file->type !== 'file') {
                    continue;
                }

                $cacheFile = $this->cacheDir . 'latest_' . $textType . '_' . $file->name;
                if (! isset($this->cachedData[$file->path]) || $file->sha !== $this->cachedData[$file->path]) {
                    printf("put %s in cache\n", $file->path);
                    $this->cachedData[$file->path] = $file->sha;
                    //phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
                    $this->remoteFileLoader->load($file->download_url)->saveTo($cacheFile);
                }

                $output[$textType][pathinfo($file->name, PATHINFO_FILENAME)] = $cacheFile;
            }
        }

        return $output;
    }

    public function fetchRaidBosses(): string
    {
        $cacheFile = $this->cacheDir . 'raidlist.html';
        $cacheKey  = $this->clock->format('Y-m-d') . '_' . floor($this->clock->format('H') / 6);

        $cacheEntry = $this->cachedData[$cacheFile] ?? null;
        if ($cacheEntry !== $cacheKey) {
            $this->remoteFileLoader->load('https://leekduck.com/boss/')->saveTo($cacheFile);
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

        $this->cachedData['hashes.json'] = json_encode($hashes) ?: '';

        return ['sha512' => $hashes];
    }

    public function hasChanges(): bool
    {
        return $this->originalCachedData !== $this->cachedData;
    }

    public function dumpCache(): void
    {
        echo 'Cached Data' . PHP_EOL;
        foreach ($this->cachedData as $key => $value) {
            echo sprintf(" %s = %s\n", $key, json_encode($value) ?: '-error-');
        }

        echo 'Original Cached Data' . PHP_EOL;
        foreach ($this->originalCachedData as $key => $value) {
            echo sprintf(" %s = %s\n", $key, json_encode($value) ?: '-error-');
        }

        echo 'Diff Cached Data' . PHP_EOL;
        foreach (array_diff($this->originalCachedData, $this->cachedData) as $key => $value) {
            echo sprintf(" %s = %s\n", $key, json_encode($value) ?: '-error-');
        }
    }
}
