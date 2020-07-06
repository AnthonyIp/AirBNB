<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Form\BookingType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @Security("is_granted('ROLE_USER')")
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    public function book(Ad $ad, EntityManagerInterface $manager, Request $request): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $booking
                ->setBooker($user)
                ->setAd($ad);

            /*Si les dates ne sont pas disponibles, message d'erreur*/

            if (!$booking->isBookableDates()) {
                $this->addFlash('warning', 'Les dates que vous avez choisi ne peuvent pas etre reservées, elles sont déjà prises');
            } else {
                /*Sinon enregistrement et redirection*/

                $manager->persist($booking);
                $manager->flush();

                return $this->redirectToRoute('booking_show', [
                    'id' => $booking->getId(),
                    'withAlert' => true
                ]);
            }
        }

        return $this->render('booking/book.html.twig', [
            'ad' => $ad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet d'afficher la page d'une reservation
     * @Route("/booking/{id}", name="booking_show")
     * @param Booking $booking
     * @return Response
     */
    public function show(Booking $booking)
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }
}
