<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
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
            ->setHash($this->encoder->encodePassword($adminUser, '123456789'))
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

            for ($j = 1, $jMax = mt_rand(2, 5); $j <= $jMax; $j++) {
                $image = new Image();
                $image->setUrl($faker->imageUrl())
                    ->setCaption($faker->sentence())
                    ->setAd($ad);
                $manager->persist($image);

            }

            /*Gestion des reservations*/

            for ($j = 1, $jMax = mt_rand(0, 10); $j <= $jMax; $j++) {
                $booking = new Booking();

                $createdAt = $faker->dateTimeBetween('-6 months');
                $startDate = $faker->dateTimeBetween('-3months');
                $duration = mt_rand(3, 10);
                $endDate = (clone $startDate)->modify("+$duration days");
                $amount = $ad->getPrice() * $duration;
                $comment = $faker->paragraph(1);

                $booker = $users[mt_rand(0, count($users) - 1)];
                $booking
                    ->setAd($ad)
                    ->setAmount($amount)
                    ->setBooker($booker)
                    ->setCreatedAt($createdAt)
                    ->setStartDate($startDate)
                    ->setEndDate($endDate)
                    ->setComment($comment);

                $manager->persist($booking);

                /*Gestion des commentaires*/
                if (mt_rand(0, 1)) {
                    $comment = new Comment();
                    $comment
                        ->setContent($faker->paragraph())
                        ->setRating(mt_rand(0, 5))
                        ->setAuthor($booker)
                        ->setAd($ad);
                    $manager->persist($comment);

                }
            }

            $manager->persist($ad);
        }
        $manager->flush();
    }
}
