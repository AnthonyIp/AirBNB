<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    /**
     * @Route("/user/{slug}", name="user_show")
     * @param User $user
     * @return Response
     */
    public function index(User $user): Response
    {
        return $this->render('user/index.html.twig', [
            "user" => $user
        ]);
    }
}
