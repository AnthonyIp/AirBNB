<?php

namespace App\Controller;

use App\Repository\AdRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
	/**
	 * @Route("/", name="homepage")
	 * @param AdRepository   $adRepo
	 * @param UserRepository $userRepo
	 * @return Response
	 */
	public function index(AdRepository $adRepo, UserRepository $userRepo)
	{
		return $this->render('home/index.html.twig',
							 [
								 'ads'   => $adRepo->findBestAds(3),
								 'users' => $userRepo->findBestUsers(3),
							 ]
		);
	}
}
