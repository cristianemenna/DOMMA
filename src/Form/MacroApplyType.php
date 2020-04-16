<?php

namespace App\Form;

use App\Entity\Macro;
use App\Service\MacroApplyManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MacroApplyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('macro', EntityType::class, [
                'label' => false,
                'class' => Macro::class,
                'choices' => $options['macros'],
                'choice_label' => 'title',
            ])
        ;
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MacroApplyManager::class,
            'macros' => null,
        ]);
    }
}
