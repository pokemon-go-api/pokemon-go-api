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
            ],
            TranslationParser::GERMAN   => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => 'Alola-%s',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => 'Galar-%s',
            ],
            TranslationParser::FRENCH   => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => '%s d\'Alola',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => '%s de Galar',
            ],
            TranslationParser::ITALIAN  => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => '%s di Alola',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => '%s di Galar',
            ],
            TranslationParser::JAPANESE => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => 'アローラ %s',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => 'ガラル %s',
            ],
            TranslationParser::KOREAN   => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => '알로라 %s',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => '가라르 %s',
            ],
            TranslationParser::SPANISH  => [
                self::REGIONAL_PREFIX . self::REGIONFORM_ALOLAN   => '%s de Alola',
                self::REGIONAL_PREFIX . self::REGIONFORM_GALARIAN => '%s de Galar',
            ],
        ];
    }
}
