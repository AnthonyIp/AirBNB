<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use App\Entity\Image;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    /**
     * AppFixtures constructor.
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setFirstName('Anthony')
            ->setLastName('Admin')
            ->setEmail('admin@admin.com')
            ->setHash($this->encoder->encodePassword($adminUser, 'password'))
            ->setPicture('https://i.pravatar.cc/300')
            ->setIntroduction($faker->sentence())
            ->setDescription('<p>' . implode('</p><p>', $faker->paragraphs(3)) . '</p>')
            ->addUserRole($adminRole);
        $manager->persist($adminUser);


        /*Gestion des user*/
        $users = [];
        $genres = ["male", "female"];

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();

            $genre = $faker->randomElement($genres);

            $picture = "https://randomuser.me/api/portraits/";
            $pictureId = $faker->numberBetween(1, 99) . '.jpg';
            $picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;

            $hash = $this->encoder->encodePassword($user, 'password');

            $user->setFirstName($faker->firstName($genre))
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setIntroduction($faker->sentence)
                ->setDescription('<p>' . implode('</p><p>', $faker->paragraphs(3)) . '</p>')
                ->setHash($hash)
                ->setPicture($picture);

            $manager->persist($user);
            $users[] = $user;
        }

        /*Gestion des annonces*/
        for ($i = 1; $i <= 30; $i++) {
            $ad = new Ad();

            $title = $faker->sentence();
            $coverImage = $faker->imageUrl(1000, 350);
            $introduction = $faker->paragraph(2);
            $content = '<p>' . implode('</p><p>', $faker->paragraphs(5)) . '</p>';
            $user = $users[random_int(0, count($users) - 1)];

            $ad->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(random_int(40, 200))
                ->setRooms(random_int(1, 5))
                ->setAuthor($user);

            for ($j = 1, $jMax = random_int(2, 5); $j <= $jMax; $j++) {
                $image = new Image();
                $image->setUrl($faker->imageUrl())
                    ->setCaption($faker->sentence())
                    ->setAd($ad);
                $manager->persist($image);

            }

            $manager->persist($ad);
        }
        $manager->flush();
    }
}
