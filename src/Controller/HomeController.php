<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Repository\ConfigurationRepository;
use App\Repository\OrderFormRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'homepage')]
    public function index(OrderFormRepository $repository, ConfigurationRepository $configurationRepository): Response
    {
        $forms = $repository->findBy(['enabled' => true]);

        return $this->render('home/index.html.twig', [
            'introduction' => $configurationRepository->getValue(Configuration::ITEM_HOMEPAGE_INTRODUCTION),
            'order_forms' => $forms,
        ]);
    }
}
