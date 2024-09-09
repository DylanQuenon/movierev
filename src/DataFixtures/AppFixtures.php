<?php

namespace App\DataFixtures;

use App\Entity\User;
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

          // admin 
          $adminUser = new User();
          $adminUser->setFirstName('dylan')
                  ->setLastName('quenon')
                  ->setUsername('dylanqn')
                  ->setEmail('dylan@gmail.com')
                  ->setPrivate(false)
                  ->setAvatar("https://thispersondoesnotexist.com/")
                  ->setPassword($this->passwordHasher->hashPassword($adminUser,'password'))
                  ->setBiography('biographie')
                  ->setDescription('<p>'.join('</p><p>',$faker->paragraphs(3)).'</p>')
                  ->setRoles(['ROLE_ADMIN']);
          $manager->persist($adminUser);

        $manager->flush();

        for($u=0;$u<20;$u++)
        {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $user = new User();
            $user->setFirstName($firstName)
                ->setLastName($lastName)
                ->setAvatar("https://thispersondoesnotexist.com/")
                ->setUsername(strtolower($firstName[0] . $lastName))
                ->setEmail($faker->email())
                ->setPassword($this->passwordHasher->hashPassword($user,'password'))
                ->setPrivate(false)
                ->setBiography('biographie')
                ->setDescription('<p>'.join('</p><p>',$faker->paragraphs(3)).'</p>');
            $manager->persist($user);
            $manager->flush();
          
        }
    }
}
