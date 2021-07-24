<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use App\Entity\User;
use App\Form\CartContentType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(): Response
    {   
        // fonction a supprimer
        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);

    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request, TranslatorInterface $t): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $product->setImage($newFilename);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', $t->trans('produit.added'));

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product, Request $request): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $product->setImage($newFilename);
            }

            $this->getDoctrine()->getManager()->flush();
            

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }

    /** @Route("/cart/{id}/add", name="cart_add") */
    public function addProductToCart(Product $product, Request $request, TranslatorInterface $t): Response
    {
        $cartContent = null;
        $cart = null;

        $carts = $this->getUser()->getCarts();
        foreach ($carts as $p) {
            // Si l'état du panier est false, on prend le panier actuel pour l'ajout du produit
            if ($p->getStatus() == false) {
                $cart = $p;
            }
        }
        
        if ($cart == null) {
            $this->redirectToRoute('product_index');
        }

        /** On récupère le contenu du panier
         * On mets à jour le produit SI et seulement si le produit est déja présent dans le panier
         */
        $cartContents = $cart->getCartContents();
        foreach($cartContents as $c) {
            if ($c->getProduct()->getId() == $product->getId()) {
                $cartContent = $c;
            }
        }

        // Si le contenu du panier est vide on le crée pour l'utilsateur et on remplit le contenu
        if ($cartContent == null) {
            $cartContent = new CartContent;
            $cartContent->setCart($cart);
            $cartContent->setProduct($product);
        }

        $form = $this->createForm(CartContentType::class, $cartContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cartContent);
            $entityManager->flush();

            $this->addFlash('success', $t->trans('product.added'));

            return $this->redirectToRoute('product_index');
        }

        return $this->renderForm('product/cartAdd.html.twig', [
            'product' => $product,
            'form' => $form,
            'cart' => $cart,
            'carts' => $carts,
            'cartContent' => $cartContent,
        ]);
    }
}
