<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('last_name', TextType::class, [
                'label' => 'Nom',
                'row_attr' => [
                    'class' => 'field'
                ],
                ])
            ->add('first_name', TextType::class, [
                'label' => 'Prénom',
                'row_attr' => [
                    'class' => 'field'
                ],
                ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'row_attr' => [
                    'class' => 'field',
                ],
                ])
            ->add('username', TextType::class, [
                'label' => 'Identifiant',
                'row_attr' => [
                    'class' => 'field'
                ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
