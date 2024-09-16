<?php

namespace App\Form;

use App\Entity\Genre;
use App\Entity\Media;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MediaEditType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $genres = [
            'Action' => 'Action',
            'Aventure' => 'Aventure',
            'Animation' => 'Animation',
            'Biopic (Film biographique)' => 'Biopic (Film biographique)',
            'Comédie' => 'Comédie',
            'Comédie dramatique' => 'Comédie dramatique',
            'Comédie romantique' => 'Comédie romantique',
            'Crime' => 'Crime',
            'Drame' => 'Drame',
            'Drame historique' => 'Drame historique',
            'Drame psychologique' => 'Drame psychologique',
            'Documentaire' => 'Documentaire',
            'Fantastique' => 'Fantastique',
            'Film noir' => 'Film noir',
            'Guerre' => 'Guerre',
            'Horreur' => 'Horreur',
            'Mélodrame' => 'Mélodrame',
            'Musical' => 'Musical',
            'Mystère' => 'Mystère',
            'Policier' => 'Policier',
            'Romance' => 'Romance',
            'Science-fiction' => 'Science-fiction',
            'Thriller' => 'Thriller',
            'Western' => 'Western',
            'Épouvante/Surnaturel' => 'Épouvante/Surnaturel',
            'Fantasy urbaine' => 'Fantasy urbaine',
            'Post-apocalyptique' => 'Post-apocalypique',
            'Super-héros' => 'Super-héros',
            'Espionnage' => 'Espionnage',
            'Satire' => 'Satire',
            'Animalier' => 'Animalier',
            'Biographique' => 'Biographique',
            'Historique' => 'Historique',
            'Nature et environnement' => 'Nature et environnement',
            'Science et technologie' => 'Science et technologie',
            'Social' => 'Social',
            'Politique' => 'Politique',
            'Économique' => 'Économique',
            'Culture et art' => 'Culture et art',
            'Sportif' => 'Sportif',
            'Voyage et découverte' => 'Voyage et découverte',
            'Criminel' => 'Criminel',
            'Guerre et conflits' => 'Guerre et conflits',
            'Santé' => 'Santé'
        ];

        $builder
            ->add('title',TextType::class,$this->getConfiguration('Titre','EX: Peaky Blinders, The Truman Show'))
            ->add('type', ChoiceType::class, [
                'label' => 'Type de Média',
                'choices' => [
                    'Film' => 'film',
                    'Série' => 'serie',
                    'Documentaire' => 'documentaire',
                ],
                'choice_label' => function($choice, $key, $value) {
                    // On peut personnaliser l'affichage des labels ici si nécessaire
                    return ucfirst($key);
                },
                'placeholder' => 'Choisissez un type',
                'required' => true, // Définit si ce champ est obligatoire ou non
                ])
            ->add('release_date', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => "Ex: 25/08/2000",
                ],
                'label'=>"Date de sortie",
                ])
            ->add('synopsis', TextareaType::class, $this->getConfiguration('Synopsis', 'EX:Titanic est un film épique de 1997 réalisé par James Cameron. L\'histoire se déroule en 1912 à bord du luxueux paquebot RMS Titanic, qui fait son voyage inaugural de Southampton à New York. Le film suit la romance tragique entre Jack Dawson, un artiste pauvre, et Rose DeWitt Bukater, une jeune femme de la haute société engagée à un homme riche et dédaigneux. Alors que le Titanic rencontre son destin fatal après avoir heurté un iceberg, Jack et Rose luttent pour survivre dans les eaux glaciales de l\'Atlantique Nord. La profondeur de leur amour contraste avec le désastre imminent, illustrant la lutte pour la survie et les choix déchirants qu\'ils doivent faire. Le film est une réflexion poignante sur l\'amour, le sacrifice et les classes sociales, le tout avec une reconstitution spectaculaire du naufrage.'))
            ->add('duration',TextType::class,$this->getConfiguration('Durée','EX: 6 saisons, 1h56'))
            ->add('genres', ChoiceType::class, [
                'choices' => $genres,
                'choice_value' => function ($choice) {
                    return $choice;
                },
                'multiple' => true,  // Permet la sélection multiple
                'expanded' => false, 
                'label' => 'Genres',
                'attr' => ['class' => 'choices-multiple mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50']
                
            ])
      
            ->add('producer',TextType::class,$this->getConfiguration('Réalisateur','Qui est le réalisateur'))
            ->add('trailer',UrlType::class,$this->getConfiguration('Url du trailer','URL YOUTUBE'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
