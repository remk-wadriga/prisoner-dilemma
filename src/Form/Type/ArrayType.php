<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 30.04.2019
 * Time: 13:12
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ArrayType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'empty_data' => null,
            'multiple' => false,
            'compound' => false,
            'allow_extra_fields' => true,
            'constraints' => [
                new Assert\Type('array'),
            ],
        ]);
    }
}