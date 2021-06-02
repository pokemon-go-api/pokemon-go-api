<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoLingen\PogoAPI\Parser;

use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Parser\CustomTranslations;
use PokemonGoApi\PogoAPI\Parser\TranslationParser;

use function count;

/**
 * @covers \PokemonGoApi\PogoAPI\Parser\CustomTranslations
 */
class CustomTranslationsTest extends TestCase
{
    public function testLoad(): void
    {
        self::assertNotEmpty(CustomTranslations::load());
        self::assertCount(
            count(TranslationParser::LANGUAGES),
            CustomTranslations::load()
        );
    }
}
