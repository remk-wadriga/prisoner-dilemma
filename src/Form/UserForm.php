<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 12:13
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var \App\Entity\User $user */
        $user = null;
        if ($builder->getData() instanceof User) {
            $user = $builder->getData();
        }

        $emailOptions = [];
        if ($this->action === self::ACTION_UPDATE && $user !== null) {
            // Email is not required for "update" action - we can use current email
            $emailOptions['required'] = false;
            $emailOptions['empty_data'] = $user->getEmail();
        }

        $builder
            ->add('email', EmailType::class, $emailOptions)
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'invalid_message' => 'The password fields are not match.',
            ])
            ->add('firstName', TextType::class, [
                'required' => false,
                'empty_data' => $user !== null ? $user->getFirstName() : '',
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'empty_data' => $user !== null ? $user->getLastName() : '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}