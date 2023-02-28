<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\IO;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\IO\RemoteFileLoader;
use PokemonGoApi\PogoAPI\Logger\NoopLogger;

/**
 * @uses \PokemonGoApi\PogoAPI\IO\File
 * @uses \PokemonGoApi\PogoAPI\Logger\NoopLogger
 *
 * @covers \PokemonGoApi\PogoAPI\IO\RemoteFileLoader
 */
class RemoteFileLoaderTest extends TestCase
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
        self::assertSame('Dummy', $response->getContent());
    }
}
