<?php

declare(strict_types=1);

namespace App\Domain\QueryExporter;

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

    private function setData(): void
    {
        $i = 2; // 1 contains the headers
        while($row = $this->extractor->current()) {
            $this->worksheet->fromArray($row, null, "A$i", true);
            $this->extractor->next();
            $i++;
        }
    }
}
