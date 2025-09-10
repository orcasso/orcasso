<?php

namespace App\Table;

use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Table;
use Kilik\TableBundle\Services\TableService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class TableExporter
{
    public function __construct(protected TableService $kilik, protected TranslatorInterface $translator)
    {
    }

    public function exportToSpreadsheet(Table $table, Request $request): Ods
    {
        $rows = $this->kilik->getRows($table, $request, false, false);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = [];
        foreach ($table->getColumns() as $column) {
            if ($column->getHidden()) {
                continue;
            }
            $columns[] = $column;
            if ('member' === $column->getName()) {
                $columns[] = (new Column())->setLabel('member.label.email')->setTranslateDomain('forms')->setName('m_email');
                $columns[] = (new Column())->setLabel('member.label.phone_number')->setTranslateDomain('forms')->setName('m_phoneNumber');
            }
        }

        $lastColumnIndex = 1;
        foreach ($columns as $index => $column) {
            $lastColumnIndex = $index + 1;
            $sheet->setCellValue([$index + 1, 1], $this->translator->trans($column->getLabel(), [], $column->getTranslateDomain()));
        }
        $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);

        $styleTitle = [
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => '007BFF']],
        ];

        $sheet->getStyle('A1:'.$lastColumn.'1')->applyFromArray($styleTitle);
        foreach (range('A', $lastColumn) as $column) {
            $spreadsheet->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
        }

        $rowNumber = 2;
        foreach ($rows as $row) {
            foreach ($columns as $index => $column) {
                $value = $column->getExportValue($row, $rows);
                $sheet->setCellValue([$index + 1, $rowNumber], $value);
            }

            ++$rowNumber;
        }
        $sheet->getStyle('A2:'.$lastColumn.($rowNumber - 1))->applyFromArray(
            ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]]
        );

        return new Ods($spreadsheet);
    }
}
