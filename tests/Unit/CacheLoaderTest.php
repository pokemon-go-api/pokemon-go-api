<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\CacheLoader;
use PokemonGoApi\PogoAPI\IO\File;
use PokemonGoApi\PogoAPI\IO\RemoteFileLoader;
use PokemonGoApi\PogoAPI\Logger\NoopLogger;

use function array_filter;
use function array_map;
use function is_file;
use function scandir;
use function sprintf;
use function sys_get_temp_dir;
use function unlink;

use const DATE_ATOM;

/**
 * @uses \PokemonGoApi\PogoAPI\IO\File
 * @uses \PokemonGoApi\PogoAPI\Logger\NoopLogger
 * @uses \PokemonGoLingen\PogoAPI\IO\Directory
 *
 * @covers \PokemonGoApi\PogoAPI\CacheLoader
 */
class CacheLoaderTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheDir = sprintf('%s/%s/', sys_get_temp_dir(), 'CacheLoaderTest');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        array_map(
            fn (string $file): bool => @unlink($this->cacheDir . $file),
            array_filter(
                scandir($this->cacheDir) ?: [],
                fn (string $file): bool => is_file($this->cacheDir . $file)
            )
        );
    }

    public function testDestruct(): void
    {
        $cacheFile = sprintf('%shashes.json', $this->cacheDir);

        $sut = new CacheLoader(
            $this->createStub(RemoteFileLoader::class),
            new DateTimeImmutable(),
            $this->cacheDir,
            new NoopLogger()
        );
        self::assertFileDoesNotExist($cacheFile);
        unset($sut);
        self::assertFileExists($cacheFile);
    }

    public function testFetchRaidBosses(): void
    {
        $clock = new DateTimeImmutable('2020-06-01 12:00:00');

        $remoteFileLoaderMock = $this->createMock(RemoteFileLoader::class);
        $remoteFileLoaderMock->expects(self::once())->method('load')->willReturn(
            new File('testcontent')
        );
        $remoteFileLoaderMock->method('receiveResponseHeader')->willReturn($clock->format(DATE_ATOM));

        $sut = new CacheLoader(
            $remoteFileLoaderMock,
            $clock,
            $this->cacheDir,
            new NoopLogger()
        );
        // assert that three calls only load once
        $sut->fetchRaidBossesFromLeekduck();
        $sut->fetchRaidBossesFromLeekduck();
        $sut->fetchRaidBossesFromLeekduck();

        // Response Header changed date
        $remoteFileLoaderMock = $this->createMock(RemoteFileLoader::class);
        $remoteFileLoaderMock->expects(self::once())->method('load')->willReturn(
            new File('testcontent')
        );
        $remoteFileLoaderMock->method('receiveResponseHeader')->willReturn(
            (new DateTimeImmutable())->format(DATE_ATOM)
        );
        $sut = new CacheLoader(
            $remoteFileLoaderMock,
            $clock,
            $this->cacheDir,
            new NoopLogger()
        );
        $sut->fetchRaidBossesFromLeekduck();
    }
}
