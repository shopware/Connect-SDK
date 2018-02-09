<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect;

/**
 * Units and their symbols. Used for product details
 */
class Units
{
    /**
     * @var array
     */
    private static $units = [
        'b' => [
            'en' => 'Byte(s)',
            'de' => 'Byte',
        ],
        'kb' => [
            'en' => 'Kilobyte(s)',
            'de' => 'Kilobyte',
        ],
        'mb' => [
            'en' => 'Megabyte(s)',
            'de' => 'Megabyte',
        ],
        'gb' => [
            'en' => 'Gigabyte(s)',
            'de' => 'Gigabyte',
        ],
        'tb' => [
            'en' => 'Terabyte(s)',
            'de' => 'Terabyte',
        ],
        'g' => [
            'en' => 'Gram(s)',
            'de' => 'Gramm',
        ],
        'kg' => [
            'en' => 'Kilogram(s)',
            'de' => 'Kilogramm',
        ],
        'mg' => [
            'en' => 'Milligram(s)',
            'de' => 'Milligramm',
        ],
        'oz' => [
            'en' => 'Ounce(s)',
            'de' => 'Unze',
        ],
        'lb' => [
            'en' => 'Pound(s)',
            'de' => 'Pfund',
        ],
        't' => [
            'en' => 'Ton(s)',
            'de' => 'Tonne',
        ],
        'l' => [
            'en' => 'Litre(s)',
            'de' => 'Liter',
        ],
        'ft^3' => [
            'en' => 'Cubic foot/feet',
            'de' => 'Kubikfuß',
        ],
        'in^3' => [
            'en' => 'Cubic inch(es)',
            'de' => 'Kubikzoll',
        ],
        'm^3' => [
            'en' => 'cubic meter',
            'de' => 'Kubikmeter',
        ],
        'yd^3' => [
            'en' => 'cubic yard(s)',
            'de' => 'Kubikyard',
        ],
        'fl oz' => [
            'en' => 'fluid ounce(s)',
            'de' => 'Flüssigunze',
        ],
        'gal' => [
            'en' => 'Gallon(s)',
            'de' => 'Gallonen',
        ],
        'ml' => [
            'en' => 'Millilitre(s)',
            'de' => 'Milliliter',
        ],
        'qt' => [
            'en' => 'Quart(s)',
            'de' => 'Quart',
        ],
        'm' => [
            'en' => 'Metre(s)',
            'de' => 'Meter',
        ],
        'cm' => [
            'en' => 'Centimetre(s)',
            'de' => 'Zentimeter',
        ],
        'ft' => [
            'en' => 'Foot/feet',
            'de' => 'Fuß',
        ],
        'in' => [
            'en' => 'Inch(es)',
            'de' => 'Zoll',
        ],
        'km' => [
            'en' => 'Kilometre(s)',
            'de' => 'Kilometer',
        ],
        'mm' => [
            'en' => 'Millimetre(s)',
            'de' => 'Millimeter',
        ],
        'yd' => [
            'en' => 'yard(s)',
            'de' => 'Yard',
        ],
        'piece' => [
            'en' => 'Piece(s)',
            'de' => 'Stück',
        ],
        'bottle' => [
            'en' => 'Bottle(s)',
            'de' => 'Flasche',
        ],
        'crate' => [
            'en' => 'Crate(s)',
            'de' => 'Kiste',
        ],
        'can' => [
            'en' => 'Can(s)',
            'de' => 'Dose',
        ],
        'capsule' => [
            'en' => 'Capsule(s)',
            'de' => 'Kapsel',
        ],
        'box' => [
            'en' => 'Box(es)',
            'de' => 'Karton(s)',
        ],
        'glass' => [
            'en' => 'Glass(es)',
            'de' => 'Glas',
        ],
        'kit' => [
            'en' => 'Kit(s)',
        ],
        'pack' => [
            'en' => 'Pack(s)',
            'de' => 'Packung(en)',
        ],
        'package' => [
            'en' => 'Package(s)',
            'de' => 'Paket(e)',
        ],
        'pair' => [
            'en' => 'Pair(s)',
            'de' => 'Paar',
        ],
        'roll' => [
            'en' => 'Roll(s)',
            'de' => 'Rolle',
        ],
        'set' => [
            'en' => 'Set(s)',
        ],
        'sheet' => [
            'en' => 'Sheet(s)',
            'de' => 'Blatt',
        ],
        'ticket' => [
            'en' => 'Ticket(s)',
        ],
        'unit' => [
            'en' => 'Unit(s)',
            'de' => 'VKE',
        ],
        'second' => [
            'en' => 'Second(s)',
            'de' => 'Sekunde',
        ],
        'day' => [
            'en' => 'Day(s)',
            'de' => 'Tag',
        ],
        'hour' => [
            'en' => 'Hour(s)',
            'de' => 'Stunde',
        ],
        'minute' => [
            'en' => 'Minute(s)',
            'de' => 'Minute',
        ],
        'month' => [
            'en' => 'Month(s)',
            'de' => 'Monat(e)',
        ],
        'night' => [
            'en' => 'Night(s)',
            'de' => 'Nacht',
        ],
        'week' => [
            'en' => 'Week(s)',
            'de' => 'Woche',
        ],
        'year' => [
            'en' => 'Year(s)',
            'de' => 'Jahr(e)',
        ],
        'm^2' => [
            'en' => 'Square metre(s)',
            'de' => 'Quadratmeter',
        ],
        'cm^2' => [
            'en' => 'Square centimetre(s)',
            'de' => 'Quadratzentimeter',
        ],
        'ft^2' => [
            'en' => 'Square foot/feet',
            'de' => 'Quadratfuß',
        ],
        'in^2' => [
            'en' => 'Square inch(es)',
            'de' => 'Quadratzoll',
        ],
        'mm^2' => [
            'en' => 'Square milimetre(s)',
            'de' => 'Quadratmillimeter',
        ],
        'yd^2' => [
            'en' => 'Square yard(s)',
            'de' => 'Quadratyard',
        ],
        'lfm' => [
            'en' => 'Running metre',
            'de' => 'Laufender Meter'
        ]
    ];

    /**
     * List of all available unit symbols.
     */
    public static function getAvailableUnits()
    {
        return array_keys(self::$units);
    }

    /**
     * Key value pairs of unit symbols and their translated unit name.
     *
     * @return array
     */
    public static function getLocalizedUnits($locale = 'en')
    {
        return array_map(
            function ($labels) use ($locale) {
                return isset($labels[$locale]) ? $labels[$locale] : $labels['en'];
            },
            self::$units
        );
    }

    /**
     * Does this unit exist as a measurement in Shopware Connect
     *
     * @return bool
     */
    public static function exists($unitSymbol)
    {
        return isset(self::$units[strtolower($unitSymbol)]);
    }
}
