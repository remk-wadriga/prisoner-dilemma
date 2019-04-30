<?php

namespace App\Form;

use App\Entity\Strategy;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\Type\ArrayType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Types\Enum\DecisionTypeEnum;

class StrategyForm extends AbstractType implements DataMapperInterface
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
            ->add('decisionsData', ArrayType::class, [
                'constraints' => [
                    new Assert\Collection([
                        'type' => new Assert\Choice(DecisionTypeEnum::getAvailableTypes()),
                        'children' => new Assert\Type('array'),
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Strategy::class,
        ]);
    }

    public function mapDataToForms($data, $forms)
    {
        if (!$data instanceof Strategy) {
            throw new UnexpectedTypeException($data, Strategy::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $forms['name']->setData($data->getName());
        $forms['description']->setData($data->getDescription());
        $forms['status']->setData($data->getStatus());
        $decisionsData = $forms['decisionsData']->getData();
        if (is_array($decisionsData)) {
            $forms['decisionsData']->setData($decisionsData);
        }
    }

    public function mapFormsToData($forms, &$data)
    {
        if (!$data instanceof Strategy) {
            throw new UnexpectedTypeException($data, Strategy::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $data->setName($forms['name']->getData());
        $data->setDescription($forms['description']->getData());
        $data->setStatus($forms['status']->getData());
        $decisionsData = $forms['decisionsData']->getData();
        dd($decisionsData);
        if (is_array($decisionsData)) {
            $data->setDecisionsData($decisionsData);
        }
    }


}
