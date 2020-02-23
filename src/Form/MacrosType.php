<?php

namespace App\Form;

use App\Entity\Macros;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MacrosType extends AbstractType
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
            ->add('code', TextType::class, [
                'label' => 'Code',
                'row_attr' => [
                    'class' => 'field'
                ],
            ])
            ->add('type', TextType::class, [
                'label' => 'Type',
                'row_attr' => [
                    'class' => 'field'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Macros::class,
        ]);
    }
}
