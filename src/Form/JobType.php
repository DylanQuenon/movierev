<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Application;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class JobType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('role', ChoiceType::class, [
            'choices' => [
                'Modérateur' => 'moderator',
                'Rédacteur' => 'redactor',
            ],
            'label' => 'Postuler pour :'
        ])
            ->add('motivation', TextareaType::class, $this->getConfiguration('Motivation', 'Votre motivation'))
          
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
