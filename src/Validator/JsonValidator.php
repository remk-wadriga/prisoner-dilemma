<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 19.09.2018
 * Time: 19:13
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JsonValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {

    }
}