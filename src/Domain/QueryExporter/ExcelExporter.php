<?php

declare(strict_types=1);

namespace App\Domain\QueryExporter;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter
{
    private Spreadsheet $spreadsheet;
    private Worksheet $worksheet;

    public function __construct(
        private readonly DataExtractor $extractor,
    ) {
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function generate(): string
    {
        $this->initializeSheet();
        $this->setHeaders();
        $this->setData();

        $writer = new Xlsx($this->spreadsheet);

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    private function initializeSheet(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()
            ->setCreator(__('Breeders Database'))
            ->setLastModifiedBy(__('Breeders Database'));

        $this->worksheet = $this->spreadsheet->getActiveSheet();
    }

    private function setHeaders(): void
    {
        $columns = $this->extractor->getHeaders();
        $this->worksheet->fromArray($columns, null, 'A1', true);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function setData(): void
    {
        $i = 2; // 1 contains the headers
        while ($row = $this->extractor->current()) {
            $this->addRow($i, $row);
            $this->extractor->next();
            $i++;
        }
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addRow(int $row, array $data): void
    {
        $col = 1;
        foreach ($data as $val) {
            $this->addCell($row, $col, $val);
            $col++;
        }
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addCell(int $row, int $col, mixed $val): void
    {
        if ($this->isDate($val)) {
            $excelTimestamp = Date::timestampToExcel($val->getTimestamp());
            $this->worksheet->setCellValueByColumnAndRow($col, $row, $excelTimestamp);
            $this->worksheet->getStyle([$col, $row, $col, $row])
                ->getNumberFormat()
                ->setBuiltInFormatCode(14);

            return;
        }

        $this->worksheet->setCellValueByColumnAndRow($col, $row, $val);

        if ($this->isLink($val)) {
            $this->worksheet->getCell([$col, $row])
                ->getHyperlink()
                ->setUrl($val);
        }
    }

    private function isDate(mixed $val): bool
    {
        return match (true) {
            $val instanceof \DateTime,
                $val instanceof \DateTimeImmutable,
                $val instanceof FrozenTime,
                $val instanceof FrozenDate => true,
            default => false,
        };
    }

    private function isLink(mixed $val): bool
    {
        if (!is_string($val)) {
            return false;
        }

        return 1 === preg_match('/^https?:\/\//', $val);
    }
}
