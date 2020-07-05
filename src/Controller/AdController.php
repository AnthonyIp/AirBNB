<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Repository\AdRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     * @param AdRepository $repo
     * @return Response
     */
    public function index(AdRepository $repo): Response
    {
        $ads = $repo->findAll();
        return $this->render('ad/index.html.twig', [
            'controller_name' => 'AdController',
            'ads' => $ads
        ]);
    }

    /**
     * Permet de créer une annonce
     *
     * @Route("/ads/new", name="ads_create")
     * @Security("is_granted('ROLE_USER')")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function create(Request $request, entityManagerInterface $manager): Response
    {
        $ad = new Ad();

        $form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }
            $ad->setAuthor($this->getUser());
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash('success', "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !");

            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render('ad/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'edition
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier!")
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Ad $ad, Request $request, entityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }
            $slugify = new Slugify();
            if ($slugify->slugify($ad->getTitle()) !== $ad->getSlug()) {
                $ad->setSlug($slugify->slugify($ad->getTitle()));
            }
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash('success', "Les modifications de l'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !");

            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render('ad/edit.html.twig', [
            'form' => $form->createView(),
            "ad" => $ad,
        ]);
    }

    /**
     * Permet d'afficher une seule annonce
     * @Route("/ads/{slug}", name="ads_show")
     * @param Ad $ad
     * @return Response
     */
    public function show(Ad $ad): Response
    {
        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }

    /**
     * Permet de supprimer une annonce
     *
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la supprimer!")
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     */
    public function delete(Ad $ad, EntityManagerInterface $manager)
    {
        $manager->remove($ad);
        $manager->flush();
        $this->addFlash('success', "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !");

        return $this->redirectToRoute("account_index");
    }
}
