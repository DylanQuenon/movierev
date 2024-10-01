<?php

namespace App\Form;


use App\Entity\Genre;
use App\Entity\Media;
use App\Form\CastingType;
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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class MediaType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('title',TextType::class,$this->getConfiguration('Titre','EX: Peaky Blinders, The Truman Show'))
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Film' => 'film',
                    'Série' => 'serie',
                    'Documentaire' => 'documentaire',
                ],
                'attr' => ['id' => 'media-type']
            ])
            ->add('duration', TextType::class, [
                'label' => 'Durée',
               
            ])
            ->add('release_date', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => "Ex: 25/08/2000",
                ],
                'label'=>"Date de sortie",
            ])
            ->add('synopsis', TextareaType::class, $this->getConfiguration('Synopsis', 'EX:Titanic est un film épique de 1997 réalisé par James Cameron. L\'histoire se déroule en 1912 à bord du luxueux paquebot RMS Titanic, qui fait son voyage inaugural de Southampton à New York. Le film suit la romance tragique entre Jack Dawson, un artiste pauvre, et Rose DeWitt Bukater, une jeune femme de la haute société engagée à un homme riche et dédaigneux. Alors que le Titanic rencontre son destin fatal après avoir heurté un iceberg, Jack et Rose luttent pour survivre dans les eaux glaciales de l\'Atlantique Nord. La profondeur de leur amour contraste avec le désastre imminent, illustrant la lutte pour la survie et les choix déchirants qu\'ils doivent faire. Le film est une réflexion poignante sur l\'amour, le sacrifice et les classes sociales, le tout avec une reconstitution spectaculaire du naufrage.'))
            ->add('genres', EntityType::class, [
                'class' => Genre::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Genres',
                'attr' => ['class' => 'choices-multiple mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50']
            ])
           
            ->add('producer',TextType::class,$this->getConfiguration('Réalisateur','Qui est le réalisateur'))
            ->add('trailer',UrlType::class,$this->getConfiguration('Url du trailer','youtube -> partager -> intégrer -> copier le src de l\'iframe'))
            ->add('poster', FileType::class, $this->getConfiguration("Poster du film", "EX : PosterTitanic.jpg"))
            ->add('cover', FileType::class, $this->getConfiguration("Couverture du film", "EX : Titanic_qui_coule.png"))
            ->add('castings', CollectionType::class, [
                'entry_type' => CastingType::class,
                'allow_add' => true, // pour le data_prototype
                'allow_delete' => true,
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
