<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

/**
 * Generator file .xlsx ringan tanpa dependency eksternal.
 * Membangun dokumen OOXML Spreadsheet minimal dengan ZipArchive bawaan PHP.
 */
class SpreadsheetService
{
    public function download(string $filename, array $headers, array $rows): BinaryFileResponse
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'laporan_');
        unlink($tmpPath);
        $tmpPath .= '.xlsx';

        $this->build($tmpPath, $headers, $rows);

        return response()
            ->download($tmpPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function build(string $path, array $headers, array $rows): void
    {
        $sheetBody = $this->rowsXml($headers, $rows);

        $manifest = [
            '[Content_Types].xml' => $this->contentTypesXml(),
            '_rels/.rels' => $this->rootRelsXml(),
            'xl/workbook.xml' => $this->workbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->workbookRelsXml(),
            'xl/styles.xml' => $this->stylesXml(),
            'xl/worksheets/sheet1.xml' => $sheetBody,
        ];

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Tidak dapat membuat file laporan.');
        }

        foreach ($manifest as $manifestPath => $content) {
            $zip->addFromString($manifestPath, $content);
        }

        $zip->close();
    }

    private function rowsXml(array $headers, array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

        $xml .= $this->rowXml(1, $headers);

        foreach (array_values($rows) as $index => $row) {
            $xml .= $this->rowXml($index + 2, (array) $row);
        }

        return $xml.'</sheetData></worksheet>';
    }

    private function rowXml(int $rowNumber, array $cells): string
    {
        $xml = '<row r="'.$rowNumber.'">';

        foreach (array_values($cells) as $colIndex => $value) {
            $ref = $this->columnLetter($colIndex + 1).$rowNumber;
            $xml .= $this->cellXml($ref, $value);
        }

        return $xml.'</row>';
    }

    private function cellXml(string $ref, $value): string
    {
        if ($this->isNumeric($value)) {
            $number = is_string($value) ? (float) str_replace([',', ' '], '.', $value) : $value;

            return '<c r="'.$ref.'"><v>'.rtrim(rtrim(number_format($number, 6, '.', ''), '0'), '.').'</v></c>';
        }

        $escaped = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        if ($escaped === '') {
            return '<c r="'.$ref.'"/>';
        }

        return '<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'.$escaped.'</t></is></c>';
    }

    private function isNumeric($value): bool
    {
        if (is_int($value) || is_float($value)) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        if (preg_match('/^[+-]?\d+(\.\d+)?[eE][+-]?\d+$/', $value) === 1) {
            return false;
        }

        return is_numeric($value);
    }

    private function columnLetter(int $number): string
    {
        $letter = '';

        while ($number > 0) {
            $mod = ($number - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $number = intdiv($number - 1, 26);
        }

        return $letter;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<bookViews><workbookView/></bookViews>'
            .'<sheets><sheet name="Laporan Absensi" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            .'</styleSheet>';
    }
}