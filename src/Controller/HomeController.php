<?php

namespace App\Controller;

use App\Repository\OrderFormRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'homepage')]
    public function index(OrderFormRepository $repository): \Symfony\Component\HttpFoundation\Response
    {
        $forms = $repository->findBy(['enabled' => true]);

        return $this->render('home/index.html.twig', [
            'order_forms' => $forms,
        ]);
    }
}
