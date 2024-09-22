<?php

namespace App\Form;

use App\Entity\Actor;
use App\Entity\Casting;
use App\Entity\Media;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CastingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('actor', EntityType::class, [
            'class' => Actor::class,
            'choice_label' => 'fullName', // Assure-toi que 'name' est le bon champ
            'placeholder' => 'Sélectionnez un acteur',
            'attr' => ['class' => 'choices-actor mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50'],
        ])
        ->add('role')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Casting::class,
        ]);
    }
}
