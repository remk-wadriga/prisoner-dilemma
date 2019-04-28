<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 26.04.2019
 * Time: 12:09
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\TypeValidator as SystemTypeValidator;

class TypeValidator extends SystemTypeValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Type|Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);
    }

}