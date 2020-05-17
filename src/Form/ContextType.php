<?php

namespace App\Form;

use App\Entity\Context;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'field'
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'row_attr' => [
                    'class' => 'field'
                ],
            ])
            ->add('duration', NumberType::class, [
                'label' => 'Durée de vie (en jours)',
                'invalid_message' => 'La durée de vie doit être représentée en nombre de jours !',
                'row_attr' => [
                    'class' => 'field'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Context::class,
        ]);
    }
}
