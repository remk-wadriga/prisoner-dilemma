<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.09.2018
 * Time: 14:45
 */

namespace App\Entity\Types\Enum;


class IsEnabledEnum
{
    const TYPE_ENABLED = 'enabled';
    const TYPE_DISABLED = 'disabled';

    protected static $typeName = [
        self::TYPE_ENABLED => 'Enabled',
        self::TYPE_DISABLED => 'Disabled',
    ];

    public static function getTypeName(string $typeShortName): string
    {
        return isset(static::$typeName[$typeShortName]) ? static::$typeName[$typeShortName] : null;
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_ENABLED, self::TYPE_DISABLED
        ];
    }
}