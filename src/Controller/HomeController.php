<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="noLocale")
     */
    public function NoLocale():Response
    {
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{_locale}/", name="home")
     */
    public function index():Response
    {
        return $this->render('home/show.html.twig');
    }

}
