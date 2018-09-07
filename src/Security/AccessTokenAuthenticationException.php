<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 05:20
 */

namespace App\Security;

use MongoDB\Driver\Exception\AuthenticationException;

class AccessTokenAuthenticationException extends AuthenticationException
{
    const CODE_SYSTEM_ERROR = 1000;
    const CODE_INVALID_ACCESS_TOKEN = 1001;
    const CODE_ACCESS_TOKEN_EXPIRED = 1002;
}