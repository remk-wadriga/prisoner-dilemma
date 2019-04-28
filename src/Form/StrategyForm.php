<?php

namespace App\Form;

use App\Entity\Strategy;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class StrategyForm extends AbstractType
{
    private $decisionsData;

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
            //->add('decisionsData')
            ->add('decisionsData', FormType::class, [
                'required' => false,
                'allow_extra_fields' => true,
                'inherit_data' => true,
            ])
        ;

        // @todo: find a normal way to fix this problem (the validation error)!
        $builder->get('decisionsData')
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $this->decisionsData = is_array($data) ? $data : null;
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($strategy) {
                if ($strategy !== null && $this->decisionsData !== null) {
                    $strategy->setDecisionsData($this->decisionsData);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Strategy::class,
        ]);
    }
}
