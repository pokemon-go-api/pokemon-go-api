<?php

declare(strict_types=1);

namespace PokemonGoLingen\PogoAPI\Parser;

class CustomTranslations
{
    public const REGIONAL_PREFIX     = 'regional_form_';
    public const REGIONFORM_ALOLAN   = 'alola';
    public const REGIONFORM_GALARIAN = 'galar';

    /**
     * @return array<string, array<string, string>>
     */
    public static function load(): array
    {
        return [
            TranslationParser::ENGLISH  => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => 'Alolan %s',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => 'Galarian %s',
                'raidgraphic_header_level' => 'Level',
                'raidgraphic_header_boss' => 'Raidboss',
                'raidgraphic_header_type' => 'Types',
                'raidgraphic_header_weather' => 'Weather',
                'raidgraphic_header_cprange' => 'CP-Range',
                'raidgraphic_header_counter' => 'Counters',
            ],
            TranslationParser::GERMAN   => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => 'Alola-%s',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => 'Galar-%s',
                'raidgraphic_header_level' => 'Level',
                'raidgraphic_header_boss' => 'Raidboss',
                'raidgraphic_header_type' => 'Typen',
                'raidgraphic_header_weather' => 'Wetter',
                'raidgraphic_header_cprange' => 'WP-Spektrum',
                'raidgraphic_header_counter' => 'Konter-Typen',
            ],
            TranslationParser::FRENCH   => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => '%s d\'Alola',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => '%s de Galar',
                'raidgraphic_header_level' => 'Level',
                'raidgraphic_header_boss' => 'Raidboss',
                'raidgraphic_header_type' => 'Types',
                'raidgraphic_header_weather' => 'Weather',
                'raidgraphic_header_cprange' => 'CP-Range',
                'raidgraphic_header_counter' => 'Counters',
            ],
            TranslationParser::ITALIAN  => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => '%s di Alola',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => '%s di Galar',
                'raidgraphic_header_level' => 'Level',
                'raidgraphic_header_boss' => 'Raidboss',
                'raidgraphic_header_type' => 'Types',
                'raidgraphic_header_weather' => 'Weather',
                'raidgraphic_header_cprange' => 'CP-Range',
                'raidgraphic_header_counter' => 'Counters',
            ],
            TranslationParser::JAPANESE => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => 'アローラ %s',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => 'ガラル %s',
                'raidgraphic_header_level' => 'Level',
                'raidgraphic_header_boss' => 'Raidboss',
                'raidgraphic_header_type' => 'Types',
                'raidgraphic_header_weather' => 'Weather',
                'raidgraphic_header_cprange' => 'CP-Range',
                'raidgraphic_header_counter' => 'Counters',
            ],
            TranslationParser::KOREAN   => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => '알로라 %s',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => '가라르 %s',
                'raidgraphic_header_level' => 'Level',
                'raidgraphic_header_boss' => 'Raidboss',
                'raidgraphic_header_type' => 'Types',
                'raidgraphic_header_weather' => 'Weather',
                'raidgraphic_header_cprange' => 'CP-Range',
                'raidgraphic_header_counter' => 'Counters',
            ],
            TranslationParser::SPANISH  => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => '%s de Alola',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => '%s de Galar',
                'raidgraphic_header_level' => 'Level',
                'raidgraphic_header_boss' => 'Raidboss',
                'raidgraphic_header_type' => 'Types',
                'raidgraphic_header_weather' => 'Weather',
                'raidgraphic_header_cprange' => 'CP-Range',
                'raidgraphic_header_counter' => 'Counters',
            ],
        ];
    }
}
