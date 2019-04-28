<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 26.04.2019
 * Time: 12:07
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Type as SystemType;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * Class NotEmpty
 * @package App\Validator
 */
class Type extends SystemType
{

}