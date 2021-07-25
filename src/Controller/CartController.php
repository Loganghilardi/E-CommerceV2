<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cart")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="cart_index", methods={"GET"})
     */
    public function index(): Response
    {
        $cart = null;

        // Parcours le CartContent afin de récupérer les informations pour l'affichage
        $carts = $this->getUser()->getCarts();
        foreach ($carts as $p) {
            if ($p->getStatus() == false) {
                $cart = $p;
            }
        }

        if ($cart == null) {
            $this->redirectToRoute('product_index');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    /** @Route("/pay/{id}", name="cart_pay") */
    public function payCart(Cart $cart, TranslatorInterface $t): Response
    {
        
        // On récupère l'utilisateur connecté
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        /**
         * Lors du paiement, on met à jour le status à true pour confirmer son achat
         * On indique la date de l'achat et on lui crée ensuite un nouveau panier qu'on lui donne
         */
        $cart->setStatus(true);
        $cart->setDateBuy(new \DateTime('now'));
        $newCart = new Cart();
        $newCart->setUser($user);

        $em->persist($cart);
        $em->persist($newCart);
        $em->flush();

        $this->addFlash('success', $t->trans('cart.pay'));

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/{id}", name="cart_show", methods={"GET"})
     */
    public function show(Cart $cart): Response
    {
        //On prend uniquement le panier panier 
        $entityManager = $this->getDoctrine()->getManager();
        return $this->render('cart/show.html.twig', [
            'cart' => $entityManager->getRepository(Cart::class)->findBy(['id'=>$cart->getId(),'status'=>true])[0],
        ]);
    }

    /**
     * @Route("/delete/{id}", name="cart_delete")
     */
    public function deleteProduct(CartContent $cartContent, TranslatorInterface $t): Response
    {
       
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($cartContent);
        $entityManager->flush();

        $this->addFlash('success', $t->trans('product.deleted'));

        return $this->redirectToRoute('cart_index');
    }
}
