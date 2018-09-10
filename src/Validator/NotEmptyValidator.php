<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 10.09.2018
 * Time: 19:36
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraints\NotBlankValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class NotEmptyValidator extends NotBlankValidator
{
    const IS_NEW = 'isNew';
    const IS_NOT_NEW = 'isNotNew';

    /**
     * Initializes the constraint validator.
     *
     * @param ExecutionContextInterface $context The current validation context
     */
    public function initialize(ExecutionContextInterface $context)
    {
        parent::initialize($context);
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param NotEmpty|Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value) && $constraint->skipEmptyOn !== null) {
            switch ($constraint->skipEmptyOn) {
                case self::IS_NOT_NEW:
                    $entity = $this->context->getObject();
                    if (method_exists($entity, 'getIsNew') && $entity->getIsNew() === false) {
                        return;
                    }
                    break;
                default:
                    break;
            }
        }
        parent::validate($value, $constraint);
    }
}