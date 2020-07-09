<?php

namespace App\Service;

use Doctrine\Common\Persistence\ManagerRegistry;

class StatsService
{
    private $manager;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->manager = $managerRegistry->getManager();
    }

    public function getStats()
    {
        $users = $this->getUsersCount();
        $ads = $this->getAdsCount();
        $bookings = $this->getBookingsCount();
        $comments = $this->getCommentsCount();

        return compact('users', 'ads', 'bookings', 'comments');
    }

    public function getUsersCount()
    {
        return $this->manager->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult();
    }

    public function getAdsCount()
    {
        return $this->manager->createQuery('SELECT COUNT(a) FROM App\Entity\Ad a')->getSingleScalarResult();
    }

    public function getBookingsCount()
    {
        return $this->manager->createQuery('SELECT COUNT(b) FROM App\Entity\Booking b')->getSingleScalarResult();
    }

    public function getCommentsCount()
    {
        return $this->manager->createQuery('SELECT COUNT(c) FROM App\Entity\Comment c')->getSingleScalarResult();
    }

    public function getAdsStats($direction)
    {
        return $this->manager->createQuery('
            SELECT AVG(c.rating) as note, a.title, a.id, a.slug, u.firstName, u.lastName, u.picture
            FROM App\Entity\Comment c 
            JOIN c.ad a
            JOIN a.author u
            GROUP BY a
            ORDER BY note ' . $direction
        )
            ->setMaxResults(8)
            ->getResult();
    }

}
