<?php

namespace App\Controller\Admin;

use App\Entity\FiscalPeriod;
use App\Entity\User;
use App\Form\FiscalPeriodType;
use App\Repository\FiscalPeriodRepository;
use App\Table\FiscalPeriodTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/fiscal-periods')]
#[IsGranted(User::ROLE_ADMIN_FISCAL_PERIOD_EDIT)]
final class FiscalPeriodController extends AbstractController
{
    public function __construct(private readonly FiscalPeriodRepository $repository)
    {
    }

    #[Route('/', name: 'admin_fiscal_period_list')]
    public function list(TableService $kilik, FiscalPeriodTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('admin/fiscal_period/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'admin_fiscal_period_list_ajax')]
    public function _list(Request $request, TableService $kilik, FiscalPeriodTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'admin_fiscal_period_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $fiscalPeriod = new FiscalPeriod();
        $form = $this->createForm(FiscalPeriodType::class, $fiscalPeriod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($fiscalPeriod);
            $this->addFlash('success', 'success.fiscal_period.created');

            return $this->redirectToRoute('admin_fiscal_period_edit', ['fiscalPeriod' => $fiscalPeriod->getId()]);
        }

        return $this->render('admin/fiscal_period/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{fiscalPeriod}', name: 'admin_fiscal_period_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FiscalPeriod $fiscalPeriod): Response
    {
        $form = $this->createForm(FiscalPeriodType::class, $fiscalPeriod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($fiscalPeriod);
            $this->addFlash('success', 'success.fiscal_period.updated');

            return $this->redirectToRoute('admin_fiscal_period_edit', ['fiscalPeriod' => $fiscalPeriod->getId()]);
        }

        return $this->render('admin/fiscal_period/edit.html.twig', [
            'fiscal_period' => $fiscalPeriod,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{fiscalPeriod}/set-current', name: 'admin_fiscal_period_set_current', methods: ['GET', 'POST'])]
    public function setCurrent(Request $request, FiscalPeriod $fiscalPeriod): Response
    {
        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->setCurrent($fiscalPeriod);
            $this->addFlash('success', 'success.fiscal_period.set_current');

            return $this->redirectToRoute('admin_fiscal_period_edit', ['fiscalPeriod' => $fiscalPeriod->getId()]);
        }

        return $this->render('admin/fiscal_period/set_current.html.twig', [
            'fiscal_period' => $fiscalPeriod,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{fiscalPeriod}/delete', name: 'admin_fiscal_period_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, FiscalPeriod $fiscalPeriod): Response
    {
        if (!$this->repository->isRemovable($fiscalPeriod)) {
            $this->addFlash('warning', 'warning.fiscal_period.not_removable');

            return $this->redirectToRoute('admin_fiscal_period_edit', ['fiscalPeriod' => $fiscalPeriod->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($fiscalPeriod);
            $this->addFlash('success', 'success.fiscal_period.deleted');

            return $this->redirectToRoute('admin_fiscal_period_list');
        }

        return $this->render('admin/fiscal_period/delete.html.twig', [
            'fiscal_period' => $fiscalPeriod,
            'form' => $form->createView(),
        ]);
    }
}
