<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    /**
     * Permet d'afficher et de gerer la connexion de l'utilisateur
     * @Route("/login", name="account_login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function index(AuthenticationUtils $utils): Response
    {
        // get the login error if there is one
        $error = $utils->getLastAuthenticationError();

        // last username entered by the user
        $username = $utils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
        ]);
    }

    /**
     * Permet de se deconnecter
     * @Route("/logout", name="account_logout")
     * */
    public function logout(): void
    {}

    /**
     * Permet d'afficher le formulaire d'inscription et de s'incrire au site
     * @Route("/register", name="account_register")
     * @param Request $request
     * @param entityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function register(Request $request, entityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getHash());
            $user->setHash($hash);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Votre compte a bien été crée! Vous pouvez maintenant vous connecter!');
            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/registration.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * Permet d'afficher et de traiter le formulaire de modification de profil
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function profile(Request $request, entityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
            $slugify = new Slugify();
            if ($slugify->slugify($user->getFirstName() . ' ' . $user->getLastName()) !== $user->getSlug()) {
                $user->setSlug($user->getFirstName() . ' ' . $user->getLastName());
            }
            $this->addFlash('success', 'Vos modifications ont bien été enregistrées!');
        }
        return $this->render('account/profile.html.twig', [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    /**
     * Permet de modifier le mot de passe du compte utilisateur
     * @Route("/account/password-update", name="account_password")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function updateAccount(Request $request, entityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getHash())) {
                $form->get('oldPassword')->addError(new FormError("Le mot de passe actuel saisie n'est pas correct."));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);
                $user->setHash($hash);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Votre nouveau mot de passe a été mis à jour!');
                return $this->redirectToRoute('homepage');
            }
        }
        return $this->render('account/password.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * Permet d'afficher le profil de l'utilisateur authentifié
     * @Route("/account", name="account_index")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function myAccount(): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * Permet d'afficher la liste des réservations faites par l'utilisateur
     *
     * @Route("/account/bookings", name="account_bookings")
     *
     * @return Response
     */
    public function bookings()
    {
        return $this->render('account/bookings.html.twig');
    }
}
