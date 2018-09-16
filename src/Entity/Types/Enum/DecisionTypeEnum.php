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
    const TYPE_AGREE = 'agree';
    const TYPE_REFUSE = 'refuse';
    const TYPE_RANDOM = 'random';

    protected static $typeName = [
        self::TYPE_AGREE => 'Agree',
        self::TYPE_REFUSE => 'Refuse',
        self::TYPE_RANDOM => 'Random',
    ];

    public static function getTypeName(string $typeShortName): string
    {
        return isset(static::$typeName[$typeShortName]) ? static::$typeName[$typeShortName] : null;
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_AGREE, self::TYPE_REFUSE, self::TYPE_RANDOM
        ];
    }
}