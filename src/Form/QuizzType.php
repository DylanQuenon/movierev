<?php

namespace App\Form;

use App\Entity\Quizz;
use App\Form\QuestionType;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class QuizzType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class,$this->getConfiguration('Titre','Choisir un titre pour le quizz'))
            // ->add('questions', CollectionType::class, [
            //     'entry_type' => QuestionType::class,
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     'by_reference' => false,
            // ]);
     
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quizz::class,
        ]);
    }
}
