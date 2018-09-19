<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 19.09.2018
 * Time: 19:13
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * Class Json
 * @package App\Validator
 */
class Json extends Constraint
{
    const INVALID_JSON_ERROR = 'c1051bb4-d103-3f74-8987-acbcafc7fdc3';

    protected static $errorNames = array(
        self::INVALID_JSON_ERROR => 'JSON_IS_INCORRECT_ERROR',
    );

    public $message;
}