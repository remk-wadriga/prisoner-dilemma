<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class GameForm extends AbstractType
{
    private $resultsData;

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
            ->add('resultsData', FormType::class , [
                'required' => false,
                'allow_extra_fields' => true,
                'inherit_data' => true,
            ])
        ;

        // @todo: find a normal way to fix this problem (the validation error)!
        $builder->get('resultsData')
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $this->resultsData = is_array($data) ? $data : null;
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($game) {
                if ($game !== null && $this->resultsData !== null) {
                    $game->setResultsData($this->resultsData);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
