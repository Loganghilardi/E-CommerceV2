<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findToday(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/user/mod/{id}", name="user_mod")
     */
    public function modUser(User $user = null, TranslatorInterface $t)
    {
        // Si l(id utilisateur n'existe pas, on renvoit vers la liste des utilisateurs)
        if($user == null){
            $this->addFlash('danger', $t->trans('user.notFound'));
            $this->redirectToRoute('user_index');
        }

        // On protège les utilisateurs ROLE_SUPER_ADMIN
        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())){

            $this->addFlash('danger', $t->trans('User ').$user->getEmail().' '.$t->trans('user.cantmod'));
            $this->redirectToRoute('user_index');
        }

        $em = $this->getDoctrine()->getManager();

        // On donne/retire le role ROLE_ADMIN
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            $user->setRoles(['ROLE_ADMIN']);
            $this->addFlash('success', $t->trans('User ').$user->getEmail().' '.$t->trans('user.mod').' ROLE_ADMIN');
        } else {
            $user->setRoles([]);
            $this->addFlash('success', $t->trans('User ').$user->getEmail().' '.$t->trans('user.unmod').' ROLE_ADMIN');
        }

        $em->persist($user);
        $em->flush();

        // On renvoit vers la liste des utilisateurs
        return $this->redirectToRoute('user_index');
    }
}
