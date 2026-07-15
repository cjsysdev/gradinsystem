<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Minimal .xlsx writer — builds the OOXML package by hand with ZipArchive so
 * the project keeps its "no composer vendor dir" setup.
 *
 * Every cell is written as an inline string, so values that look numeric
 * (contact numbers like "09171234567") keep their leading zero instead of
 * being coerced to a number by Excel.
 */
class Xlsx_writer
{
    private $sheet_name = 'Sheet1';
    private $col_widths = [];
    private $rows       = [];

    public function set_sheet_name($name)
    {
        // Excel rejects these characters in a sheet name, and caps it at 31.
        $name = str_replace(['\\', '/', '?', '*', '[', ']', ':'], '-', (string) $name);
        $name = trim($name);
        $this->sheet_name = ($name === '') ? 'Sheet1' : $this->mb_cut($name, 31);
        return $this;
    }

    /** @param int[] $widths Column widths in character units, left to right. */
    public function set_columns(array $widths)
    {
        $this->col_widths = $widths;
        return $this;
    }

    public function add_row(array $cells, $bold = FALSE)
    {
        $this->rows[] = ['cells' => array_values($cells), 'bold' => (bool) $bold];
        return $this;
    }

    /** Streams the workbook as a download and ends the request. */
    public function download($filename)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $this->save($tmp);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmp));
        header('Cache-Control: max-age=0');
        readfile($tmp);
        unlink($tmp);
        exit;
    }

    public function save($path)
    {
        if (!class_exists('ZipArchive')) {
            show_error('The PHP zip extension is required to export Excel files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            show_error('Could not create the Excel file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->content_types_xml());
        $zip->addFromString('_rels/.rels', $this->root_rels_xml());
        $zip->addFromString('xl/workbook.xml', $this->workbook_xml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbook_rels_xml());
        $zip->addFromString('xl/styles.xml', $this->styles_xml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet_xml());
        $zip->close();

        return $path;
    }

    private function content_types_xml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function root_rels_xml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook_xml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->esc($this->sheet_name) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbook_rels_xml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    // Two cell formats: 0 = default, 1 = bold (used for the header row).
    private function styles_xml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    private function sheet_xml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        if ($this->col_widths) {
            $xml .= '<cols>';
            foreach ($this->col_widths as $i => $width) {
                $n = $i + 1;
                $xml .= '<col min="' . $n . '" max="' . $n . '" width="' . (float) $width . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        foreach ($this->rows as $r => $row) {
            $row_no = $r + 1;
            $xml .= '<row r="' . $row_no . '">';
            foreach ($row['cells'] as $c => $value) {
                $ref   = $this->col_letter($c) . $row_no;
                $style = $row['bold'] ? ' s="1"' : '';
                $xml .= '<c r="' . $ref . '"' . $style . ' t="inlineStr"><is><t xml:space="preserve">'
                    . $this->esc($value) . '</t></is></c>';
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    /** 0 => A, 25 => Z, 26 => AA ... */
    private function col_letter($index)
    {
        $letter = '';
        $index++;
        while ($index > 0) {
            $rem    = ($index - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $index  = (int) (($index - $rem - 1) / 26);
        }
        return $letter;
    }

    private function esc($value)
    {
        // Control characters other than tab/LF/CR are illegal in XML 1.0 and
        // make Excel report the whole workbook as corrupt.
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string) $value);
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function mb_cut($string, $length)
    {
        return function_exists('mb_substr')
            ? mb_substr($string, 0, $length, 'UTF-8')
            : substr($string, 0, $length);
    }
}
