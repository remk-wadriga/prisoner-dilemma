<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 10.09.2018
 * Time: 19:25
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * Class NotEmpty
 * @package App\Validator
 */
class NotEmpty extends NotBlank
{
    public $skipEmptyOn = null;
}