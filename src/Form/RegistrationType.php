<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class RegistrationType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, $this->getConfiguration("Prénom", "Votre prénom...", ['attr' => ['id' => 'myForm_firstName']]))
            ->add('lastName', TextType::class, $this->getConfiguration("Nom", "Votre nom...", ['attr' => ['id' => 'myForm_lastName']]))
            ->add('username', TextType::class, $this->getConfiguration("Nom d'utilisateur", "Votre nom d'utilisateur...", ['attr' => ['id' => 'myForm_username']]))
            ->add('email', EmailType::class, $this->getConfiguration("Email", "Votre adresse e-mail...", ['attr' => ['id' => 'myForm_email']]))
            ->add('password', PasswordType::class, $this->getConfiguration("Mot de passe", "Votre mot de passe", ['attr' => ['id' => 'myForm_password']]))
            ->add('passwordConfirm', PasswordType::class, $this->getConfiguration("Confirmation du mot de passe", "Veuillez confirmer votre mot de passe", ['attr' => ['id' => 'myForm_passwordConfirm']]))
            ->add('avatar', FileType::class, $this->getConfiguration("Avatar", "Choisir votre avatar...", ['attr' => ['id' => 'myForm_avatar']]))
            ->add('isPrivate', null, ['attr' => ['id' => 'myForm_isPrivate']])
            ->add('biography', TextType::class, $this->getConfiguration("Biographie", "Votre biographie...", ['attr' => ['id' => 'myForm_biography']]))
            ->add('description', TextareaType::class, $this->getConfiguration("Description", "Description du profil...", ['attr' => ['id' => 'myForm_description']]));    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
