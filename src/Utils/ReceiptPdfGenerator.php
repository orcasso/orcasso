<?php

namespace App\Utils;

use App\Entity\Configuration;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Payment;
use App\Repository\ConfigurationRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use TCPDF;

class ReceiptPdfGenerator
{
    private const COLOR_PRIMARY = [41, 98, 152];   // bleu institutionnel
    private const COLOR_LIGHT_BG = [240, 245, 250]; // fond ligne paire
    private const COLOR_WHITE = [255, 255, 255];
    private const COLOR_DARK_TEXT = [30, 30, 30];
    private const COLOR_GREY_TEXT = [100, 100, 100];

    public function __construct(private readonly ConfigurationRepository $configurationRepository, private readonly TranslatorInterface $translator)
    {
    }

    public function generate(Order $order, string $outputMode = 'S'): string
    {
        $pdf = $this->createTcpdf();

        $pdf->AddPage();

        $this->renderHeader($pdf);
        $this->renderInvoiceMeta($pdf, $order);
        $this->renderBuyer($pdf, $order);
        $this->renderLinesTable($pdf, $order);
        $this->renderTotals($pdf, $order);
        $this->renderPayments($pdf, $order);
        $this->renderFooterNote($pdf);

        return $pdf->Output(
            $this->buildFilename($order),
            $outputMode,
        );
    }

    // -------------------------------------------------------------------------
    // Création et configuration de l'objet TCPDF
    // -------------------------------------------------------------------------

    private function createTcpdf(): \TCPDF
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Métadonnées
        $pdf->SetCreator('OrcAsso');
        $pdf->SetAuthor($this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_NAME));
        $pdf->SetTitle('Reçu');

        // Désactive l'en-tête et le pied de page par défaut de TCPDF
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Marges (gauche, haut, droite)
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);

        // Police par défaut
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);

        return $pdf;
    }

    private function renderHeader(\TCPDF $pdf): void
    {
        $startY = $pdf->GetY();

        // --- Bloc gauche : nom de l'association ---
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(...self::COLOR_PRIMARY);
        $pdf->MultiCell(95, 7, $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_NAME), 0, 'L', false, 0, 15, $startY);

        // --- Bloc droit : adresse ---
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(...self::COLOR_GREY_TEXT);
        $addressBlock = implode(\PHP_EOL, array_filter([
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_NAME),
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_FULL_ADDRESS),
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_EMAIL),
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_PHONE_NUMBER),
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_SIRET),
            'Association loi 1901 – Non assujettie à la TVA',
        ]));
        $pdf->MultiCell(80, 4.5, $addressBlock, 0, 'R', false, 1, 110, $startY);

        // Ligne de séparation
        $pdf->SetDrawColor(...self::COLOR_PRIMARY);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, $pdf->GetY() + 3, 195, $pdf->GetY() + 3);
        $pdf->Ln(6);

        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
    }

    private function renderInvoiceMeta(\TCPDF $pdf, Order $order): void
    {
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(...self::COLOR_PRIMARY);
        $pdf->Cell(0, 10, 'REÇU', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(...self::COLOR_GREY_TEXT);

        $metaLine = \sprintf(
            'N° %s  |  Date : %s  |  Statut : %s',
            $order->getIdentifier(),
            $order->getCreatedAt()->format('d/m/Y'),
            $this->translator->trans("order.choice.status.{$order->getStatus()}", domain: 'forms'),
        );
        $pdf->Cell(0, 6, $metaLine, 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(...self::COLOR_PRIMARY);
        $pdf->Cell(0, 5, 'Cotisation - Année associative '.$this->getAssociativeYear($order), 0, 1, 'C');

        $pdf->Ln(4);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
    }

    private function renderBuyer(\TCPDF $pdf, Order $order): void
    {
        $member = $order->getMember();

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->Cell(80, 6, strtoupper($this->translator->trans('order.label.member', domain: 'forms')), 0, 1, 'L', true);

        $pdf->SetFillColor(...self::COLOR_LIGHT_BG);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
        $pdf->SetFont('helvetica', '', 9);

        $pdf->Cell(80, 5.5, $member->getFullName(), 0, 1, 'L');
        $pdf->MultiCell(80, 5.5, $member->getFullAddress(), 0, 'L');

        if ($order->getNotes()) {
            $pdf->Ln(2);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(...self::COLOR_GREY_TEXT);
            $pdf->MultiCell(160, 4.5, 'Note : '.$order->getNotes(), 0, 'L');
        }

        $pdf->Ln(5);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
    }

    private function renderLinesTable(\TCPDF $pdf, Order $order): void
    {
        // En-tête du tableau
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->SetDrawColor(...self::COLOR_PRIMARY);
        $pdf->SetLineWidth(0.1);

        $pdf->Cell(10, 7, '#', 'B', 0, 'C', true);
        $pdf->Cell(120, 7, 'Désignation', 'B', 0, 'L', true);
        $pdf->Cell(50, 7, 'Montant (€)', 'B', 1, 'R', true);
        $pdf->Ln(1);

        // Lignes
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
        $pdf->SetDrawColor(200, 200, 200);

        $fill = false;
        foreach ($order->getLines() as $line) {
            /* @var OrderLine $line */
            $pdf->SetFillColor(...($fill ? self::COLOR_LIGHT_BG : self::COLOR_WHITE));

            $position = $line->getPosition() + 1;
            $label = $line->getLabel();

            // Pour les lignes de type remise, on précise le calcul dans le libellé
            if (
                OrderLine::TYPE_ALLOWANCE === $line->getType()
                && $line->getAllowancePercentage()
                && $line->getAllowanceBaseAmount()
            ) {
                $label .= \sprintf(
                    \PHP_EOL.'%s%% × %s',
                    $line->getAllowancePercentage(),
                    $this->formatCurrency($line->getAllowanceBaseAmount()),
                );
            }

            $labelHeight = $this->estimateMultiCellHeight($pdf, 120, $label);

            $pdf->Cell(10, $labelHeight, (string) $position, 'B', 0, 'C', true);
            $pdf->MultiCell(120, $labelHeight, $label, 'B', 'L', true, 0);
            $pdf->Cell(50, $labelHeight, $this->formatCurrency($line->getAmount()), 'B', 1, 'R', true);

            $fill = !$fill;
        }

        $pdf->Ln(2);
    }

    /**
     * Bloc des totaux (total, payé, reste dû).
     */
    private function renderTotals(\TCPDF $pdf, Order $order): void
    {
        $colLabel = 130;
        $colAmount = 50;

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->Cell($colLabel, 7, 'TOTAL', 0, 0, 'R', true);
        $pdf->Cell($colAmount, 7, $this->formatCurrency($order->getTotalAmount()), 0, 1, 'R', true);

        // Payé
        if ($order->getPaidAmount() > 0) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetFillColor(...self::COLOR_LIGHT_BG);
            $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
            $pdf->Cell($colLabel, 6, 'Déjà réglé', 0, 0, 'R', true);
            $pdf->Cell($colAmount, 6, $this->formatCurrency($order->getPaidAmount()), 0, 1, 'R', true);

            // Reste dû
            $pdf->SetFont('helvetica', 'B', 10);
            $dueColor = $order->getDueAmount() > 0 ? [180, 0, 0] : [0, 130, 60];
            $pdf->SetTextColor(...$dueColor);
            $pdf->SetFillColor(250, 250, 250);
            $pdf->Cell($colLabel, 7, 'RESTE À RÉGLER', 0, 0, 'R', true);
            $pdf->Cell($colAmount, 7, $this->formatCurrency($order->getDueAmount()), 0, 1, 'R', true);
        }

        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
        $pdf->Ln(6);
    }

    /**
     * Détail des paiements enregistrés.
     */
    private function renderPayments(\TCPDF $pdf, Order $order): void
    {
        $activePayments = array_filter(
            $order->getPayments()->toArray(),
            fn ($op) => Payment::STATUS_CANCELLED !== $op->getPayment()->getStatus(),
        );

        if (empty($activePayments)) {
            return;
        }

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->Cell(0, 6, 'DÉTAIL DES RÈGLEMENTS', 0, 1, 'L', true);

        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
        $pdf->SetFillColor(...self::COLOR_LIGHT_BG);

        $fill = false;
        foreach ($activePayments as $orderPayment) {
            $payment = $orderPayment->getPayment();
            $pdf->SetFillColor(...($fill ? self::COLOR_LIGHT_BG : self::COLOR_WHITE));
            $pdf->Cell(
                130,
                5.5,
                \sprintf(
                    'Règlement du %s',
                    $payment->getCreatedAt()->format('d/m/Y'),
                ),
                0, 0, 'L', true,
            );
            $pdf->Cell(
                50,
                5.5,
                $this->formatCurrency($orderPayment->getAmount()),
                0, 1, 'R', true,
            );
            $fill = !$fill;
        }

        $pdf->Ln(5);
    }

    private function renderFooterNote(\TCPDF $pdf): void
    {
        $pdf->SetFont('helvetica', 'I', 7.5);
        $pdf->SetTextColor(...self::COLOR_GREY_TEXT);

        $pdf->SetDrawColor(...self::COLOR_PRIMARY);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(3);

        $notes = [
            'TVA non applicable, article 293 B du CGI',
            'En cas de retard de paiement, indemnité forfaitaire de recouvrement : 40 €',
        ];
        $pdf->MultiCell(0, 4, implode(\PHP_EOL, $notes), 0, 'C');
    }

    private function formatCurrency(float|int $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ').' €';
    }

    private function buildFilename(Order $order): string
    {
        return \sprintf('receipt-%s.pdf', $order->getIdentifier());
    }

    private function estimateMultiCellHeight(\TCPDF $pdf, float $width, string $text): float
    {
        $lineCount = max(1, substr_count($text, "\n") + 1);

        // On ajoute 0.5 ligne de marge par retour à la ligne supplémentaire
        return 6 + ($lineCount - 1) * 4.5;
    }

    /**
     * @todo store current exercice in order
     */
    private function getAssociativeYear(Order $order): string
    {
        $date = $order->getCreatedAt();
        $month = (int) $date->format('n');
        $year  = (int) $date->format('Y');
        $start = $month < 9 ? $year-1 : $year;

        return \sprintf('%d / %d', $start, $start + 1);
    }
}
