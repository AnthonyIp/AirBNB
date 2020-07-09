<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\AdminCommentType;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/users/{page<\d+>?1}", name="admin_users_index")
     * @param $page
     * @param PaginationService $pagination
     * @return Response
     */
    public function index($page, PaginationService $pagination): Response
    {
        $pagination
            ->setEntityClass(User::class)
            ->setPage($page);

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'edition
     * @Route("admin/users/{id}/edit", name="admin_users_edit")
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "Les modifications de l'utilisateur ont bien été enregistrées !");
            return $this->redirect($request->request->get('referer'));
        }
        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet de supprimer un utilisateur
     * @Route("/admin/users/{id}/delete", name="admin_users_delete")
     *
     * @param User $user
     * @param EntityManagerInterface $manager
     */
    public function delete(User $user, EntityManagerInterface $manager)
    {
        if (count($user->getBookings()) > 0) {
            /* success peut être changé mais ici il correspond aux couleurs du Bootstrap */
            $this->addFlash('warning', "Le profile <strong>{$user->getFullName()}</strong> ne peut pas etre supprimé car il a des reservations.");
        } else {
            $manager->remove($user);
            $manager->flush();
            $this->addFlash('success', "Le profile <strong>{$user->getFullName()}</strong> a bien été supprimée !");
            return $this->redirectToRoute('admin_users_index');
        }
        return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
    }

}
