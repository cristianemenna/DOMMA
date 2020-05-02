<?php

namespace App\Form;

use App\Entity\Users;
use App\Service\PasswordManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $password = new PasswordManager();

        $builder
            ->add('username')
            ->add('password', HiddenType::class, [
                'required' => false,
                'empty_data' => $password->randomPassword(),])
            ->add('email')
            ->add('first_name')
            ->add('last_name')
            ->add('role', ChoiceType::class,
                ['choices' => ['Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN'],
                    'multiple' => false,
                    'label' => 'Type',
                    'row_attr' => [
                        'class' => 'field'
                    ],
                ])
            ->add('attempts', HiddenType::class, [
                'required'   => false,
                'empty_data' => 0,])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
