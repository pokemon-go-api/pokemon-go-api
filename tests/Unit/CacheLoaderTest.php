<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\CacheLoader;
use PokemonGoApi\PogoAPI\IO\Directory;
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

#[CoversClass(CacheLoader::class)]
#[UsesClass(File::class)]
#[UsesClass(NoopLogger::class)]
#[UsesClass(Directory::class)]
class CacheLoaderTest extends TestCase
{
    private string $cacheDir;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = sprintf('%s/%s/', sys_get_temp_dir(), 'CacheLoaderTest');
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        array_map(
            fn (string $file): bool => @unlink($this->cacheDir . $file),
            array_filter(
                scandir($this->cacheDir) ?: [],
                fn (string $file): bool => is_file($this->cacheDir . $file),
            ),
        );
    }

    public function testDestruct(): void
    {
        $cacheFile = sprintf('%shashes.json', $this->cacheDir);

        $sut = new CacheLoader(
            $this->createStub(RemoteFileLoader::class),
            new DateTimeImmutable(),
            $this->cacheDir,
            new NoopLogger(),
        );
        $this->assertFileDoesNotExist($cacheFile);
        unset($sut);
        $this->assertFileExists($cacheFile);
    }

    public function testFetchRaidBosses(): void
    {
        $clock = new DateTimeImmutable('2020-06-01 12:00:00');

        $remoteFileLoaderMock = $this->createMock(RemoteFileLoader::class);
        $remoteFileLoaderMock->expects(self::once())->method('load')->willReturn(
            new File('testcontent'),
        );
        $remoteFileLoaderMock->method('receiveResponseHeader')->willReturn($clock->format(DATE_ATOM));

        $sut = new CacheLoader(
            $remoteFileLoaderMock,
            $clock,
            $this->cacheDir,
            new NoopLogger(),
        );
        // assert that three calls only load once
        $sut->fetchRaidBossesFromLeekduck();
        $sut->fetchRaidBossesFromLeekduck();
        $sut->fetchRaidBossesFromLeekduck();

        // Response Header changed date
        $remoteFileLoaderMock = $this->createMock(RemoteFileLoader::class);
        $remoteFileLoaderMock->expects(self::once())->method('load')->willReturn(
            new File('testcontent'),
        );
        $remoteFileLoaderMock->method('receiveResponseHeader')->willReturn(
            (new DateTimeImmutable())->format(DATE_ATOM),
        );
        $sut = new CacheLoader(
            $remoteFileLoaderMock,
            $clock,
            $this->cacheDir,
            new NoopLogger(),
        );
        $sut->fetchRaidBossesFromLeekduck();
    }
}
