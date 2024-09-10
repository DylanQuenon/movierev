<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Media;
use Faker\Factory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer l'utilisateur admin
        $adminUser = new User();
        $adminUser->setFirstName('dylan')
                  ->setLastName('quenon')
                  ->setUsername('dylanqn')
                  ->setEmail('dylan@gmail.com')
                  ->setPrivate(false)
                  ->setAvatar("")
                  ->setPassword($this->passwordHasher->hashPassword($adminUser, 'password'))
                  ->setBiography('biographie')
                  ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                  ->setRoles(['ROLE_ADMIN']);
        $manager->persist($adminUser);

        // Créer des utilisateurs fictifs
        for ($u = 0; $u < 20; $u++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $user = new User();
            $user->setFirstName($firstName)
                ->setLastName($lastName)
                ->setAvatar("")
                ->setUsername(strtolower($firstName[0] . $lastName))
                ->setEmail($faker->email())
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                ->setPrivate(false)
                ->setBiography('biographie')
                ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>');
            $manager->persist($user);
        }

        // Créer des films fictifs
        for ($f = 0; $f < 20; $f++) {
            $media = new Media();
            $media->setTitle($faker->sentence(3))
                  ->setReleaseDate($faker->dateTimeBetween('-30 years', 'now'))
                  ->setType($faker->randomElement(['film', 'série']))
                  ->setDuration($faker->numberBetween(60, 180) . ' min')
                  ->setSynopsis($faker->paragraph(5))
                  ->setPoster("")
                  ->setCover("")
                  ->setProducer($faker->name())
                  ->setTrailer("https://www.youtube.com/watch?v=oVzVdvGIC7U");
            $manager->persist($media);
        }

        // Sauvegarder les changements dans la base de données
        $manager->flush();
    }
}
