<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 12.09.2018
 * Time: 18:03
 */

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException as BaseHttpException;
use Symfony\Component\HttpFoundation\Response;

class HttpException extends BaseHttpException
{
    const CODE_NOT_FOUND = 1404;
    const CODE_ACCESS_DENIED = 1403;
    const CODE_BAD_REQUEST = 1400;
    const CODE_SYSTEM_ERROR = 1500;

    public $message = 'Something went wrong!';

    public function __construct(?string $message = null, int $code, \Exception $previous = null, array $headers = [])
    {

        if ($previous !== null && $previous instanceof StrategyException) {
            if ($code === 0) {
                $code = $previous->getCode();
            }
            if (!empty($previous->getMessage())) {
                if ($message !== null) {
                    $message .= ': ';
                }
                $message .= $previous->getMessage();
            }
        }

        if ($message === null) {
            $message = '';
        }

        switch ($code) {
            case self::CODE_NOT_FOUND:
                $statusCode = Response::HTTP_NOT_FOUND;
                break;
            case self::CODE_BAD_REQUEST:
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            case self::CODE_ACCESS_DENIED:
                $statusCode = Response::HTTP_FORBIDDEN;
                break;
            case StrategyException::CODE_INVALID_PARAMS:
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            default:
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                break;
        }

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}