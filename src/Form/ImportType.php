<?php

namespace App\Form;

use App\Entity\Import;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Choisir un fichier',
                'mapped' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        new File([
                            'mimeTypes' => [
                                'text/plain',
                                'text/csv',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel (official)',
                                'application/msexcel',
                                'application/x-msexcel',
                                'application/x-ms-excel',
                                'application/x-excel',
                                'application/x-dos_ms_excel',
                                'application/xls',
                                'application/x-xls',
                                'application/vnd-xls'
                            ],
                            'mimeTypesMessage' => 'Veuillez choisir un fichier en format XLS, XLSX ou CSV.'
                        ])
                    ])
                ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Import::class,
        ]);
    }
}
