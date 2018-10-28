<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
            ->add('resultsData')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
