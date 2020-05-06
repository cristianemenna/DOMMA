<?php

namespace App\Form;

use App\Entity\Export;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileType', ChoiceType::class, [
                'choices' => [
                    'XLS' => 'xls',
                    'XLSX' => 'xlsx',
                    'CSV' => 'csv',
                ],
                'label' => false,
                'placeholder' => 'Choisir format du fichier',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Export::class,
        ]);
    }
}
