<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\IO;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\IO\File;
use PokemonGoApi\PogoAPI\IO\RemoteFileLoader;
use PokemonGoApi\PogoAPI\Logger\NoopLogger;

#[CoversClass(RemoteFileLoader::class)]
#[UsesClass(File::class)]
#[UsesClass(NoopLogger::class)]
final class RemoteFileLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $sut = new RemoteFileLoader(
            new NoopLogger(),
            new Client([
                'handler' => new MockHandler([new Response(200, [], 'Dummy')]),
            ]),
        );

        $response = $sut->load('foo');
        $this->assertSame('Dummy', $response->getContent());
    }
}
