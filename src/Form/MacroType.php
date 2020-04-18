<?php

namespace App\Form;

use App\Entity\Macro;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MacroType extends AbstractType
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
            ->add('type', ChoiceType::class, [
                'choices' => ['Empty' => '',
                    'Select' => 'select',
                    'Insert' => 'insert',
                    'Update' => 'update',
                    'Delete' => 'delete',
                    'Remplace tiret par espace vice' => 'tiret-par-espace',
                ],
                'label' => 'Type',
                'row_attr' => [
                    'class' => 'field'
                ],
            ])
            ->add('code', TextareaType::class, [
                'label' => 'Code',
                'row_attr' => [
                    'class' => 'field'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Macro::class,
        ]);
    }
}
