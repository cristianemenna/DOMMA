<?php

namespace App\Form;

use App\Entity\Users;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('last_name', TextType::class, [
                'label' => 'Nom',])
            ->add('first_name', TextType::class, [
                'label' => 'Prénom',])
            ->add('email', TextType::class, [
                'label' => 'Email',])
            ->add('username', TextType::class, [
                'label' => 'Identifiant',])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
