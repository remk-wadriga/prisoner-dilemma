<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 18:49
 */

namespace App\Exception;


class GameException extends ServiceException
{
    const CODE_STRATEGIES_NOT_FOUND = 3100;
    const CODE_GAME_IMPOSSIBLE = 3200;
}