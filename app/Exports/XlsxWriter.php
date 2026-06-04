<?php

namespace App\Exports;

/**
 * Excel XML Spreadsheet (SpreadsheetML) writer.
 *
 * Generates a single XML file that Excel / LibreOffice opens natively as .xls.
 * ✔ Zero dependencies — no ZipArchive, no ext-gd, no Composer packages.
 * ✔ Multiple sheets supported.
 * ✔ Header row colour + bold styling supported.
 * ✔ Works on any PHP 7.4+ / 8.x hosting.
 */
class XlsxWriter
{
    private array $sheets = [];

    /**
     * Add a sheet.
     *
     * @param string $name        Tab name shown in Excel.
     * @param array  $rows        Array of rows; row[0] = heading row.
     * @param array  $colWidths   Column widths in Excel character units (same scale as xlsx).
     * @param string $headerColor 6-char hex for header background (no #). Default: blue.
     */
    public function addSheet(
        string $name,
        array  $rows,
        array  $colWidths   = [],
        string $headerColor = '1e40af'
    ): static {
        $this->sheets[] = compact('name', 'rows', 'colWidths', 'headerColor');
        return $this;
    }

    /**
     * Stream the file as a browser download.
     * The filename extension is automatically normalised to .xls.
     */
    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Ensure .xls extension (Excel XML format)
        $filename = preg_replace('/\.(xlsx?)$/i', '.xls', $filename) ?: $filename;
        if (!str_ends_with(strtolower($filename), '.xls')) {
            $filename .= '.xls';
        }

        $xml = $this->buildXml();

        return response()->streamDownload(
            function () use ($xml) { echo $xml; },
            $filename,
            [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
                'Cache-Control'       => 'max-age=0',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function buildXml(): string
    {
        // Collect unique header colours so we can define one Style per colour
        $colors = [];
        foreach ($this->sheets as $sh) {
            $c = strtoupper($sh['headerColor']);
            if (!in_array($c, $colors, true)) {
                $colors[] = $c;
            }
        }

        $x  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $x .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $x .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $x .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $x .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $x .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $x .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

        // ── Styles ────────────────────────────────────────────────────────────
        $x .= '<Styles>' . "\n";

        // Default (required)
        $x .= '<Style ss:ID="Default" ss:Name="Normal">'
            . '<Alignment ss:Vertical="Bottom"/>'
            . '</Style>' . "\n";

        // Normal data cell — light border
        $x .= '<Style ss:ID="Normal">'
            . '<Alignment ss:Vertical="Center"/>'
            . '<Borders>'
            . '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D1D5DB"/>'
            . '<Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D1D5DB"/>'
            . '<Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D1D5DB"/>'
            . '<Border ss:Position="Top"    ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D1D5DB"/>'
            . '</Borders>'
            . '</Style>' . "\n";

        // One header style per unique colour
        foreach ($colors as $color) {
            $x .= '<Style ss:ID="H' . $color . '">'
                . '<Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/>'
                . '<Interior ss:Color="#' . $color . '" ss:Pattern="Solid"/>'
                . '<Alignment ss:Vertical="Center" ss:Horizontal="Left"/>'
                . '<Borders>'
                . '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>'
                . '<Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>'
                . '<Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>'
                . '<Border ss:Position="Top"    ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FFFFFF"/>'
                . '</Borders>'
                . '</Style>' . "\n";
        }

        $x .= '</Styles>' . "\n";

        // ── Worksheets ────────────────────────────────────────────────────────
        foreach ($this->sheets as $shIdx => $sh) {
            $tabName     = htmlspecialchars($sh['name'], ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $headerStyle = 'H' . strtoupper($sh['headerColor']);
            $isFirst     = $shIdx === 0;

            $x .= '<Worksheet ss:Name="' . $tabName . '">' . "\n";
            $x .= '<Table>' . "\n";

            // Column widths (convert xlsx char-units → points; ~6 pt per unit)
            foreach ($sh['colWidths'] as $w) {
                $pts  = max(30, round($w * 6.5));
                $x   .= '<Column ss:Width="' . $pts . '"/>' . "\n";
            }

            // Rows
            foreach ($sh['rows'] as $ri => $row) {
                $rowStyle = ($ri === 0) ? $headerStyle : 'Normal';
                $rowH     = ($ri === 0) ? ' ss:Height="22"' : '';
                $x .= '<Row' . $rowH . '>' . "\n";
                foreach ((array) $row as $cell) {
                    $type  = (is_int($cell) || is_float($cell)) ? 'Number' : 'String';
                    $value = htmlspecialchars((string) $cell, ENT_XML1 | ENT_COMPAT, 'UTF-8');
                    $x    .= '  <Cell ss:StyleID="' . $rowStyle . '">'
                           .  '<Data ss:Type="' . $type . '">' . $value . '</Data>'
                           .  '</Cell>' . "\n";
                }
                $x .= '</Row>' . "\n";
            }

            $x .= '</Table>' . "\n";

            // Freeze first row
            $x .= '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">' . "\n";
            if ($isFirst) {
                $x .= '<Selected/>' . "\n";
            }
            $x .= '<FreezePanes/>' . "\n"
                . '<FrozenNoSplit/>' . "\n"
                . '<SplitHorizontal>1</SplitHorizontal>' . "\n"
                . '<TopRowBottomPane>1</TopRowBottomPane>' . "\n"
                . '<ActivePane>2</ActivePane>' . "\n"
                . '</WorksheetOptions>' . "\n";

            $x .= '</Worksheet>' . "\n";
        }

        $x .= '</Workbook>';

        return $x;
    }
}
