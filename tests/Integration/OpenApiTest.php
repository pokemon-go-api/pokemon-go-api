<?php

declare(strict_types=1);

namespace Tests\Integration\PokemonGoApi\PogoAPI;

use GuzzleHttp\Psr7\Response;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\BreadCrumb;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;
use function file_get_contents;
use function glob;
use function implode;
use function realpath;
use function sprintf;

#[CoversNothing]
final class OpenApiTest extends TestCase
{
    #[DataProvider('routesDataProvider')]
    public function testOpenAPISpecification(string $filePath, string $apiRoute): void
    {
        $this->expectNotToPerformAssertions();
        $openApiFile = __DIR__ . '/../../public/Pokedex.json';
        $validator   = (new ValidatorBuilder())->fromJsonFile($openApiFile)->getResponseValidator();

        $responseContent = file_get_contents($filePath);
        if ($responseContent === false) {
            self::fail(sprintf('Cant read file "%s"', $filePath));
        }

        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            $responseContent,
        );
        try {
            $validator->validate(
                new OperationAddress(
                    $apiRoute,
                    'get',
                ),
                $response,
            );
        } catch (ValidationFailed $validationFailed) {
            $previous = $validationFailed->getPrevious();

            if ($previous instanceof KeywordMismatch) {
                $failMessage = $previous->getMessage();

                if ($previous->dataBreadCrumb() instanceof BreadCrumb) {
                    $failMessage .= ' -> [' . implode('.', $previous->dataBreadCrumb()->buildChain()) . ']';
                }

                self::fail($failMessage);
            }

            throw $validationFailed;
        }
    }

    /** @return iterable<string, string[]> */
    public static function routesDataProvider(): iterable
    {
        $files = glob(__DIR__ . '/../../data/tmp/api/*.json') ?: [];
        foreach ($files as $file) {
            $filePath = realpath($file);
            if ($filePath === false) {
                continue;
            }

            $fileName = basename($filePath) ?: $filePath;

            yield $fileName => [$filePath, '/api/' . $fileName];
        }
    }
}
