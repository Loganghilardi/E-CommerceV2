<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Form\PassWordFormType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'carts' => $entityManager->getRepository(Cart::class)->findBy(['user'=>$user,'status'=>true]),
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

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/edit/password", name="user_edit_password", methods={"GET","POST"})
     * Modifie uniquement le mot de passe de l'utilisateur
     */
    public function editPassword(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(PassWordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/password_edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    /**
     * @Route("/mod/{id}", name="user_mod")
     */
    public function modUser(User $user = null, TranslatorInterface $t)
    {
        // Si l(id utilisateur n'existe pas, on renvoit vers la liste des utilisateurs)
        if($user == null){
            $this->addFlash('danger', $t->trans('user.notFound'));
            return $this->redirectToRoute('user_index');
        }

        // On protège les utilisateurs ROLE_SUPER_ADMIN
        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())){

            $this->addFlash('danger', $t->trans('User ').$user->getEmail().' '.$t->trans('user.cantmod'));
            return $this->redirectToRoute('user_index');
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
