<?php

namespace App\Controller\Admin;

use App\Entity\Ad;
use App\Form\AdType;
use App\Service\PaginationService;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminAdController extends AbstractController
{
	/**
	 * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
	 * La variable page demande un nombre (\d+) et optionnel (?) avec 1 par défaut
	 *
	 * @param int               $page
	 * @param PaginationService $pagination
	 * @return Response
	 */
	public function index($page, PaginationService $pagination): Response
	{
		$pagination
			->setEntityClass(Ad::class)
			->setPage($page);

		return $this->render('admin/ad/index.html.twig', [
			'pagination' => $pagination,
		]);

	}

	/**
	 * Permet d'afficher le formulaire d'edition
	 * @Route("admin/ads/{id}/edit", name="admin_ads_edit")
	 *
	 * @param Ad                     $ad
	 * @param Request                $request
	 * @param EntityManagerInterface $manager
	 * @return Response
	 */
	public function edit(Ad $ad, Request $request, EntityManagerInterface $manager): Response
	{
		$form = $this->createForm(AdType::class, $ad);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$slugify = new Slugify();
			if ($slugify->slugify($ad->getTitle()) !== $ad->getSlug()) {
				$ad->setSlug($slugify->slugify($ad->getTitle()));
			}
			$manager->persist($ad);
			$manager->flush();

			/* success peut être changé mais ici il correspond aux couleurs du Bootstrap */
			$this->addFlash('success', "Les modifications de <strong>{$ad->getTitle()}</strong> ont bien été enregistrées !");
			return $this->redirect($request->request->get('referer'));
		}
		return $this->render('admin/ad/edit.html.twig', [
			'ad'   => $ad,
			'form' => $form->createView(),
		]);
	}

	/**
	 * Permet de supprimer une annonce
	 * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
	 *
	 * @param Ad                     $ad
	 * @param EntityManagerInterface $manager
	 * @return Response
	 */
	public function delete(Ad $ad, EntityManagerInterface $manager): Response
	{
		// s'il y a des réservations, on ne peut pas supprimer
		if (count($ad->getBookings()) > 0) {
			/* success peut être changé mais ici il correspond aux couleurs du Bootstrap */
			$this->addFlash('warning', "L'annonce <strong>{$ad->getTitle()}</strong> a déjà des réservations !");
		} else {
			$manager->remove($ad);
			$manager->flush();
			$this->addFlash('success', "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !");
		}

		return $this->redirectToRoute('admin_ads_index');
	}
}
