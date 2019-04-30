<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Form\Type\ArrayType;
use Symfony\Component\Validator\Constraints as Assert;

class GameForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var \App\Entity\Game $game */
        $game = null;
        if ($builder->getData() instanceof Game) {
            $game = $builder->getData();
        }

        $nameOptions = [];
        if ($this->action === self::ACTION_UPDATE && $game !== null) {
            $nameOptions['required'] = false;
            $nameOptions['empty_data'] = $game->getName();
        }

        $builder
            ->add('name', TextType::class, $nameOptions)
            ->add('description', TextType::class, [
                'required' => false,
                'empty_data' => $game !== null ? $game->getDescription() : '',
            ])
            ->add('rounds', IntegerType::class)
            ->add('balesForWin', IntegerType::class)
            ->add('balesForLoos', IntegerType::class)
            ->add('balesForCooperation', IntegerType::class)
            ->add('balesForDraw', IntegerType::class)
            ->add('resultsData', ArrayType::class , [
                'constraints' => [
                    new Assert\Collection([
                        'sum' => new Assert\Type('integer'),
                        'total' => new Assert\Type('array'),
                        'individual' => new Assert\Type('array'),
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
