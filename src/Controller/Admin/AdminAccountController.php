<?php

namespace App\Controller\Admin;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;

class AdminAccountController extends AbstractController
{
	/**
	 * Permet d'afficher et de gérer la connexion Administrateur
	 * @Route("/admin/login", name="admin_account_login")
	 *
	 * @param AuthenticationUtils $utils
	 * @return Response
	 */
	public function login(AuthenticationUtils $utils): Response
	{
		$error    = $utils->getLastAuthenticationError();
		$username = $utils->getLastUsername();
		return $this->render('admin/account/login.html.twig', [
			'hasError' => $error !== null,
			'username' => $username,
		]);
	}

	/**
	 * Permet de se déconnecter
	 * @Route("/admin/logout", name="admin_account_logout")
	 */
	public function logout()
	{
	}
}
