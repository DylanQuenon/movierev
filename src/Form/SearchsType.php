<?php

// src/Form/SearchTeamType.php
namespace App\Form;

use App\Form\SearchsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SearchType;

class SearchsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', SearchType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Rechercher...'],
            ]);
            
    }
}
