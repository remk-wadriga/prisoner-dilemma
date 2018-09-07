<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 06.09.2018
 * Time: 13:51
 */

namespace App\Helpers;

use Faker\Factory;

class AccessTokenHelper
{
    const ACCESS_TOKEN_LIFE_TIME = '3 month';

    public static function generateAccessToken(AccessTokenEntityInterface $entity): string
    {
        $faker = Factory::create();
        $string = sprintf('_%s:%s:%s-%s=', $entity->getSalt(), $entity->serialize(), $faker->uuid,  microtime(true));
        return hash('sha256', $string);
    }

    public static function getAccessTokenExpiredAt(): \DateTimeInterface
    {
        $faker = Factory::create();
        $lifeTimeWithPlus = sprintf('+%s', self::ACCESS_TOKEN_LIFE_TIME);
        return $faker->dateTimeBetween($lifeTimeWithPlus, $lifeTimeWithPlus);
    }
}