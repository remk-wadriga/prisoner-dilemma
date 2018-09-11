<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 12:13
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserForm extends AbstractType
{
    const ACTION_CREATE = 'mode_create';
    const ACTION_UPDATE = 'mode_update';

    private static $actions = [self::ACTION_CREATE, self::ACTION_UPDATE];
    private $action = self::ACTION_CREATE;

    public function setAction($action)
    {
        if (in_array($action, self::$actions)) {
            $this->action = $action;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['action'])) {
            $this->setAction($options['action']);
        }

        $emailOptions = [];
        /** @var \App\Entity\User $user */
        $user = null;
        if ($builder->getData() instanceof User) {
            $user = $builder->getData();
        }

        if ($this->action === self::ACTION_UPDATE && $user !== null) {
            $emailOptions = [
                'required' => false,
                'empty_data' => $user->getEmail(),
            ];
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