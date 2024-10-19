<?php

namespace App\Form;

use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ResetPasswordType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('new_password', PasswordType::class, [
            'label' => 'Nouveau mot de passe',
            'attr' => ['autocomplete' => 'new-password'],
        ])
        ->add('confirm_password', PasswordType::class, [
            'label' => 'Confirmez le mot de passe',
            'attr' => ['autocomplete' => 'new-password'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}