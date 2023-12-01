<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', null , [ 'label' => '*Prénom'])
            ->add('lastname', null , [ 'label' => '*Nom'])
            ->add('email', null , [ 'label' => '*Courriel'])
            ->add('password', PasswordType::class, [ 'label' => '*Mot de passe'])
            ->add('picture', null , [ 'label' => '*Image']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
