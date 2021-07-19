<?php

namespace App\Controller;

use App\Entity\CartContent;
use App\Entity\Product;
use App\Form\CartContentType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
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
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
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

        return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
    }

    /** @Route("/cart/{id}/add", name="cart_add") */
    public function addProductToCart(Product $product, Request $request): Response
    {
        $cart = $this->getUser()->getCarts();
        foreach ($cart as $p) {
            if ($p->getEtat() == false) {
                $cart = $p;
            }
        }
        if ($cart == null) {
            $this->redirectToRoute('product_index');
        }

        $cartContent = $cart->getCartContent();
        if ($cartContent == null) {
            $cartContent = new CartContent;
            $cartContent->setCart($cart);
        }

        $form = $this->createForm(CartContentType::class, $cartContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cartContent->setProduct($product);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cartContent);
            $entityManager->flush();
        }

        return $this->renderForm('product/cartAdd.html.twig', [
            'product' => $product,
            'form' => $form,
            'cart' => $cart,
            'cartContent' => $cartContent,
        ]);
    }
}
