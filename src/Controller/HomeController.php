<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $prenoms = [
            "anthony" => 32,
            "stef" => 36,
            "celine" => 38
        ];
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'title' => 'Bonjour a tous',
            'age' => 21,
            'prenoms' => $prenoms
        ]);
    }
}
