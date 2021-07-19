<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Form\CartType;
use App\Repository\CartRepository;
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
    public function index(CartRepository $cartRepository): Response
    {
        $cart = null;

        // Parcours le CardContent afin de récupérer les informations pour l'affichage
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

    /**
     * @Route("/delete/{id}", name="cart_delete")
     */
    public function deleteProduct(CartContent $cartContent, TranslatorInterface $t): Response
    {
       
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cartContent);
            $entityManager->flush();

            $this->addFlash('success', $t->trans('produit.deleted'));

        return $this->redirectToRoute('cart_index');
    }
}
