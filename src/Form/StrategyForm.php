<?php

namespace App\Form;

use App\Entity\Strategy;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class StrategyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var \App\Entity\Strategy $strategy */
        $strategy = null;
        if ($builder->getData() instanceof Strategy) {
            $strategy = $builder->getData();
        }

        $nameOptions = [];
        $statusOptions = self::getOptionsForEnabledEnumType();
        if ($this->action === self::ACTION_UPDATE && $strategy !== null) {
            // Name is not required for "update" action - we can use current name
            $nameOptions['required'] = false;
            $nameOptions['empty_data'] = $strategy->getName();
            // Status is not required for "update" action - we can use current status
            $statusOptions['required'] = false;
            $statusOptions['empty_data'] = $strategy->getStatus();
        }

        $builder
            ->add('name', TextType::class, $nameOptions)
            ->add('description', TextType::class, [
                'required' => false,
                'empty_data' => $strategy !== null ? $strategy->getDescription() : '',
            ])
            ->add('status', ChoiceType::class, $statusOptions)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Strategy::class,
        ]);
    }
}
