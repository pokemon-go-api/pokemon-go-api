<?php

declare(strict_types=1);

namespace Tests\Unit\PokemonGoApi\PogoAPI\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PokemonGoApi\PogoAPI\Parser\CustomTranslations;
use PokemonGoApi\PogoAPI\Parser\TranslationParser;

use function count;

#[CoversClass(CustomTranslations::class)]
class CustomTranslationsTest extends TestCase
{
    public function testLoad(): void
    {
        $this->assertNotEmpty(CustomTranslations::load());
        $this->assertCount(count(TranslationParser::LANGUAGES), CustomTranslations::load());
    }
}
