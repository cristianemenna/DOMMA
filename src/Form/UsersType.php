<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('password', PasswordType::class)
            ->add('email')
            ->add('first_name')
            ->add('last_name')
            ->add('roles', ChoiceType::class,
                ['choices' => ['Admin' => 'ROLE_ADMIN',
                    'Utilisateur' => 'ROLE_USER'],
                    'multiple' => true,])
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
