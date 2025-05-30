<?php

namespace App\Controller\Admin;

use App\Entity\Configuration;
use App\Entity\User;
use App\Form\ConfigurationsType;
use App\Repository\ConfigurationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/configuration')]
#[IsGranted(User::ROLE_ADMIN_CONFIGURATION_EDIT)]
final class ConfigurationController extends AbstractController
{
    public function __construct(protected ConfigurationRepository $repository)
    {
    }

    #[Route('/', name: 'admin_configuration_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $items = array_map(fn (string $item) => $this->repository->get($item), array_keys(Configuration::ITEMS_FORM_TYPES));
        $form = $this->createForm(ConfigurationsType::class, $items);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($items as $item) {
                $this->repository->update($item);
            }
            $this->addFlash('success', 'success.configuration.updated');

            return $this->redirectToRoute('admin_configuration_edit');
        }

        return $this->render('admin/configuration/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
