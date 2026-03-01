<?php

namespace App\Utils;

use App\Entity\Configuration;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use App\Repository\ConfigurationRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReceiptPdfGenerator
{
    private const COLOR_PRIMARY = [41, 98, 152];
    private const COLOR_LIGHT_BG = [240, 245, 250];
    private const COLOR_WHITE = [255, 255, 255];
    private const COLOR_DARK_TEXT = [30, 30, 30];
    private const COLOR_GREY_TEXT = [100, 100, 100];

    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function generate(Order $order, string $outputMode = 'S'): string
    {
        $pdf = $this->createTcpdf();

        $pdf->AddPage();

        $this->renderHeader($pdf);
        $this->renderInvoiceMeta($pdf, $order);
        $this->renderMember($pdf, $order);
        $this->renderLinesTable($pdf, $order);
        $this->renderTotals($pdf, $order);
        $this->renderPayments($pdf, $order);
        $this->renderFooterNote($pdf);

        return $pdf->Output(
            $this->buildFilename($order),
            $outputMode,
        );
    }

    private function createTcpdf(): \TCPDF
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Metadata
        $pdf->SetCreator('OrcAsso');
        $pdf->SetAuthor($this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_NAME));
        $pdf->SetTitle('Reçu');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);

        return $pdf;
    }

    private function renderHeader(\TCPDF $pdf): void
    {
        $startY = $pdf->GetY();

        // Left header block
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(...self::COLOR_PRIMARY);
        $pdf->MultiCell(95, 7, $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_NAME), 0, 'L', false, 0, 15, $startY);

        // Right header block
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(...self::COLOR_GREY_TEXT);
        $addressBlock = implode(\PHP_EOL, array_filter([
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_FULL_ADDRESS),
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_EMAIL),
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_PHONE_NUMBER),
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_SIRET),
            $this->configurationRepository->getValue(Configuration::ITEM_ASSOCIATION_TYPE),
        ]));
        $pdf->MultiCell(80, 4.5, $addressBlock, 0, 'R', false, 1, 110, $startY);

        // Separating line
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
            'N° %s  |  Date : %s  |  %s : %s',
            $order->getIdentifier(),
            $order->getCreatedAt()->format('d/m/Y'),
            $this->translator->trans('order.label.status', domain: 'forms'),
            $this->translator->trans("order.choice.status.{$order->getStatus()}", domain: 'forms'),
        );
        $pdf->Cell(0, 6, $metaLine, 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(...self::COLOR_PRIMARY);
        // @todo translate and configure associative year
        $pdf->Cell(0, 5, 'Cotisation - Année associative 2025/2026', 0, 1, 'C');

        $pdf->Ln(4);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
    }

    private function renderMember(\TCPDF $pdf, Order $order): void
    {
        $member = $order->getMember();

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->Cell(80, 6, mb_strtoupper($this->translator->trans('order.label.member', domain: 'forms')), 0, 1, 'L', true);

        $pdf->SetFillColor(...self::COLOR_LIGHT_BG);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
        $pdf->SetFont('helvetica', '', 9);

        $pdf->Cell(80, 5.5, $member->getFullName(), 0, 1, 'L');
        $pdf->MultiCell(80, 5.5, $member->getFullAddress(), 0, 'L');

        $pdf->Ln(5);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
    }

    private function renderLinesTable(\TCPDF $pdf, Order $order): void
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->SetDrawColor(...self::COLOR_PRIMARY);
        $pdf->SetLineWidth(0.1);

        $pdf->Cell(10, 7, '#', 'B', 0, 'C', true);
        $pdf->Cell(120, 7, $this->translator->trans('order_line.label.label', domain: 'forms'), 'B', 0, 'L', true);
        $pdf->Cell(50, 7, $this->translator->trans('order_line.label.amount', domain: 'forms'), 'B', 1, 'R', true);
        $pdf->Ln(1);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
        $pdf->SetDrawColor(200, 200, 200);

        $fill = false;
        foreach ($order->getLines() as $line) {
            /* @var OrderLine $line */
            $pdf->SetFillColor(...($fill ? self::COLOR_LIGHT_BG : self::COLOR_WHITE));

            $position = $line->getPosition() + 1;
            $label = $line->getLabel();

            // Allowance
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

            $labelHeight = $this->estimateMultiCellHeight($label);

            $pdf->Cell(10, $labelHeight, (string) $position, 'B', 0, 'C', true);
            $pdf->MultiCell(120, $labelHeight, $label, 'B', 'L', true, 0);
            $pdf->Cell(50, $labelHeight, $this->formatCurrency($line->getAmount()), 'B', 1, 'R', true);

            $fill = !$fill;
        }

        $pdf->Ln(2);
    }

    private function renderTotals(\TCPDF $pdf, Order $order): void
    {
        $colLabel = 130;
        $colAmount = 50;

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->Cell($colLabel, 7, 'TOTAL', 0, 0, 'R', true);
        $pdf->Cell($colAmount, 7, $this->formatCurrency($order->getTotalAmount()), 0, 1, 'R', true);

        if ($order->getPaidAmount() > 0) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetFillColor(...self::COLOR_LIGHT_BG);
            $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
            $pdf->Cell($colLabel, 6, $this->translator->trans('order.label.paid_amount', domain: 'forms'), 0, 0, 'R', true);
            $pdf->Cell($colAmount, 6, $this->formatCurrency($order->getPaidAmount()), 0, 1, 'R', true);

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell($colLabel, 7, $this->translator->trans('order.label.due_amount', domain: 'forms'), 0, 0, 'R', true);
            $pdf->Cell($colAmount, 7, $this->formatCurrency($order->getDueAmount()), 0, 1, 'R', true);
        }

        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
        $pdf->Ln(6);
    }

    private function renderPayments(\TCPDF $pdf, Order $order): void
    {
        $activePayments = array_filter(
            $order->getPayments()->toArray(),
            fn (PaymentOrder $op) => Payment::STATUS_CANCELLED !== $op->getPayment()->getStatus(),
        );

        if (empty($activePayments)) {
            return;
        }

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->Cell(0, 6, mb_strtoupper($this->translator->trans('admin.order.export_receipt_pdf.payments')), 0, 1, 'L', true);

        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetTextColor(...self::COLOR_DARK_TEXT);
        $pdf->SetFillColor(...self::COLOR_LIGHT_BG);

        $fill = false;
        /** @var PaymentOrder $orderPayment */
        foreach ($activePayments as $orderPayment) {
            $payment = $orderPayment->getPayment();
            $pdf->SetFillColor(...($fill ? self::COLOR_LIGHT_BG : self::COLOR_WHITE));
            $pdf->Cell(
                130,
                5.5,
                \sprintf(
                    '%s - %s - %s : %s',
                    $this->translator->trans('payment_order.label.payment', domain: 'forms'),
                    $this->translator->trans("payment.choice.method.{$payment->getMethod()}", domain: 'forms'),
                    $this->translator->trans('payment.label.received_at', domain: 'forms'),
                    $payment->getReceivedAt() ? $payment->getReceivedAt()->format('d/m/Y') : '--',
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

    private function estimateMultiCellHeight(string $text): float
    {
        $lineCount = max(1, substr_count($text, "\n") + 1);

        return 6 + ($lineCount - 1) * 4.5;
    }
}
