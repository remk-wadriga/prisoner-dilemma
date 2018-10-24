<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 16.09.2018
 * Time: 23:38
 */

namespace App\Entity\Types\Enum;


class DecisionTypeEnum
{
    const TYPE_ACCEPT = 'accept';
    const TYPE_REFUSE = 'refuse';
    const TYPE_RANDOM = 'random';
    const TYPE_COPY = 'copy';

    protected static $typeName = [
        self::TYPE_ACCEPT => 'Accept',
        self::TYPE_REFUSE => 'Refuse',
        self::TYPE_RANDOM => 'Random',
        self::TYPE_COPY => 'Copy'
    ];

    public static function getTypeName(string $typeShortName): string
    {
        return isset(static::$typeName[$typeShortName]) ? static::$typeName[$typeShortName] : null;
    }

    public static function getTypeByName(string $typeName): ?string
    {
        $result = array_search($typeName, self::getAvailableTypesNames());
        return $result !== false ? $result : null;
    }

    public static function getAvailableTypesNames()
    {
        return array_filter(self::$typeName, function ($key) {
            return in_array($key, self::getAvailableTypes());
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_ACCEPT, self::TYPE_REFUSE, self::TYPE_RANDOM, self::TYPE_COPY
        ];
    }
}