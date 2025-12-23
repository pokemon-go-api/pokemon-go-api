<?php

declare(strict_types=1);

namespace PokemonGoApi\PogoAPI;

use DateTimeImmutable;
use JsonException;
use PokemonGoApi\PogoAPI\IO\Directory;
use PokemonGoApi\PogoAPI\IO\GithubLoader;
use PokemonGoApi\PogoAPI\IO\JsonParser;
use PokemonGoApi\PogoAPI\IO\RemoteFileLoader;
use Psr\Log\LoggerInterface;

use function abs;
use function array_key_exists;
use function array_keys;
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
use function rtrim;
use function sha1_file;
use function sleep;
use function sprintf;

use const DATE_ATOM;

/**
 * alternative sources:
 * - https://pokedex.sylvie.fyi/
 */
class CacheLoader
{
    private const string CACHE_FILE = 'hashes.json';

    /** @var array<string, mixed> */
    private array $cachedData = [];

    /** @var array<string, mixed> */
    private array $originalCachedData = [];

    public function __construct(
        private readonly RemoteFileLoader $remoteFileLoader,
        private readonly GithubLoader $githubLoader,
        private readonly DateTimeImmutable $clock,
        private readonly string $cacheDir,
        private readonly LoggerInterface $logger,
    ) {
        Directory::create($this->getCacheDir());

        if (! is_file($this->getCacheDir() . self::CACHE_FILE)) {
            return;
        }

        try {
            $this->originalCachedData = JsonParser::decodeToArray(
                file_get_contents($this->getCacheDir() . self::CACHE_FILE) ?: '[]',
            );
            $this->cachedData         = $this->originalCachedData;
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

        $latestJsonFile = $this->githubLoader->getLatestGameMasterFile();

        if ($latestJsonFile->sha === ($this->cachedData[$cacheKey] ?? null)) {
            return $cacheFile;
        }

        $this->cachedData[$cacheKey] = $latestJsonFile->sha;
        //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
        $this->remoteFileLoader->load($latestJsonFile->downloadUrl)->saveTo($cacheFile);
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

        $newSha = sha1_file('data/tmp/git-assets/.git/packed-refs');

        if ($newSha === ($this->cachedData[$cacheKey] ?? null)) {
            return $cacheFile;
        }

        $this->cachedData[$cacheKey] = $newSha;

        $cacheFileContent = $this->githubLoader->getImageList();

        file_put_contents($cacheFile, json_encode($cacheFileContent));

        $this->logger->debug(
            sprintf(
                '[CacheLoader] Missing cache entry for %s',
                $cacheKey,
            ),
            ['newHash' => $newSha],
        );

        return $cacheFile;
    }

    /** @return array<string, array<string, string>> */
    public function fetchLanguageFiles(): array
    {
        if ($this->wasRunningInThePastMinutes() && isset($this->cachedData['languageFiles'])) {
            /** @var array<string, array<string, string>> $response */
            $response = JsonParser::decodeToFullArray(json_encode($this->cachedData['languageFiles']) ?: '[]');

            return $response;
        }

        $allTexts = $this->githubLoader->getLatestTextFiles();

        $output = [];
        foreach ($allTexts as $textType => $files) {
            foreach ($files as $languageAlias => $file) {
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
                    $this->remoteFileLoader->load($file->downloadUrl)->saveTo($cacheFile);
                }

                $output[$textType][$languageAlias] = $cacheFile;
            }
        }

        $this->cachedData['languageFiles'] = $output;

        return $output;
    }

    public function fetchMaxBattlesFromSnacknap(): string
    {
        $url       = 'https://www.snacknap.com/max-battles';
        $cacheFile = $this->getCacheDir() . 'maxbattles_snacknap.html';

        if ($this->wasRunningInThePastMinutes() && file_exists($cacheFile)) {
            return $cacheFile;
        }

        $this->remoteFileLoader->load($url)->saveTo($cacheFile);
        $this->logger->debug('[CacheLoader] Load max-battles');

        return $cacheFile;
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

        return abs($this->clock->getTimestamp() - filemtime($this->getCacheDir() . self::CACHE_FILE)) < 900;
    }

    private function getCacheDir(): string
    {
        return rtrim($this->cacheDir, '/') . '/';
    }
}
