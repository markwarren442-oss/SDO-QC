<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportController extends Controller
{
    /**
     * Export Attendance Report — S.P. Form A layout
     */
    public function attendanceReport(Request $request)
    {
        $db    = DB::connection();
        $year  = (int) ($request->input('year',  date('Y')));
        $month = (int) ($request->input('month', date('m')));
        $station = $request->input('station', 'All');
        $statusFilter = strtolower($request->input('status', 'all'));
        
        // ... Signatories handled in applySignatories() ...

        /* ── Holidays ── */
        $holidays = [];
        foreach ($db->select(
            "SELECT date_str FROM special_days WHERE date_str LIKE ?",
            ["$year-" . sprintf('%02d', $month) . "-%"]
        ) as $r) {
            $holidays[(int) date('j', strtotime($r->date_str))] = true;
        }

        /* ── Employees (with status filter) ── */
        $ym = sprintf("%04d-%02d", $year, $month);
        $sql = "SELECT e.* FROM employees e";
        $params = [];
        
        if ($statusFilter !== 'all') {
            $sql .= " INNER JOIN (SELECT employee_id FROM daily_attendance WHERE `year_month` = ? AND `status` = ? GROUP BY employee_id) d ON e.id = d.employee_id";
            $params[] = $ym;
            $params[] = $statusFilter;
        }
        
        $sql .= " WHERE 1=1";
        if ($station && $station !== 'All') {
            $sql .= " AND e.station = ?";
            $params[] = $station;
        }
        $empList = $db->select($sql . " ORDER BY e.station, e.last_name, e.first_name", $params);

        /* ── Attendance & Reasons ── */
        $ym         = sprintf("%04d-%02d", $year, $month);
        $attendance = [];
        $reasons    = [];
        foreach ($db->select("SELECT employee_id, `day`, `status` FROM daily_attendance WHERE `year_month` = ?", [$ym]) as $r)
            $attendance[$r->employee_id][$r->day] = $r->status;
        foreach ($db->select("SELECT employee_id, `day`, reason FROM absence_reasons WHERE `year_month` = ?", [$ym]) as $r)
            $reasons[$r->employee_id][$r->day] = $r->reason;

        $daysInMonth   = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $periodCovered = strtoupper(date('F', mktime(0, 0, 0, $month, 1, $year)))
                       . ' 1-' . $daysInMonth . ', ' . $year;

        $reportTitle = strtoupper("{$statusFilter} REPORT");

        /* ── Group by station ── */
        $byStation = [];
        foreach ($empList as $emp)
            $byStation[$emp->station ?: 'Unassigned'][] = $emp;
        ksort($byStation);

        /* ════════════════════════════════════════════════════════════
         *  COLUMN MAP
         *  A  = No.   (col 1)
         *  B  = Name  (col 2)
         *  C–AG = days 1-31  (cols 3-33)
         *  AH = With Pay absences (col 34)
         *  AI = W/out Pay absences (col 35)
         *  AJ = Producers / instructions (col 36)
         * ══════════════════════════════════════════════════════════ */

        $spreadsheet = new Spreadsheet();
        $logoPath    = public_path('logo.png');
        $isFirst     = true;

        foreach ($byStation as $stn => $stnEmps) {

            /* ── Sheet ── */
            $ws      = $isFirst ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $isFirst = false;
            $safeName = substr(str_replace(['*','?',':','/','\\','[',']'], '_', (string)$stn), 0, 31);
            $ws->setTitle($safeName);

            /* ── Page setup ── */
            $ws->getPageSetup()
               ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
               ->setPaperSize(PageSetup::PAPERSIZE_LEGAL)
               ->setFitToPage(true)
               ->setFitToWidth(1)
               ->setFitToHeight(0);
            $ws->getPageMargins()->setTop(0.3)->setBottom(0.3)->setLeft(0.3)->setRight(0.3);

            /* ── Column widths ── */
            $ws->getColumnDimension('A')->setWidth(4.5);   // No.
            $ws->getColumnDimension('B')->setWidth(28);    // Name
            for ($i = 3; $i <= 33; $i++)                   // days 1-31
                $ws->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(3.8);
            $ws->getColumnDimension('AH')->setWidth(6.5);  // With Pay
            $ws->getColumnDimension('AI')->setWidth(6.5);  // W/out Pay
            $ws->getColumnDimension('AJ')->setWidth(22);   // Producers

            /* ════════════════════════════════════════════════
             *  ROW 1 – Full-width merged row with logo centred
             *           Height: 76.80 (128 px) — matches image
             * ═══════════════════════════════════════════════ */
            $ws->getRowDimension(1)->setRowHeight(76.80);

            // Merge A1:AI1 for the logo area (leave AJ1 for label)
            $ws->mergeCells('A1:AI1');

            // S.P. FORM A — top-right, vertically bottom-aligned in row 1
            $ws->setCellValue('AJ1', 'S.P. FORM A');
            $ws->getStyle('AJ1')->getFont()->setBold(true)->setSize(9);
            $ws->getStyle('AJ1')->getAlignment()
               ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
               ->setVertical(Alignment::VERTICAL_BOTTOM);

            // Logo — centred horizontally in the merged row 1
            $logoPath = public_path('logo.png');
            if (file_exists($logoPath)) {
                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setPath($logoPath);
                $drawing->setHeight(70);          // fits inside 76.80 row
                $drawing->setCoordinates('Q1');   // horizontally centred
                $drawing->setOffsetX(5);          // fine-tune horizontal
                $drawing->setOffsetY(3);          // small top padding
                $drawing->setWorksheet($ws);
            }

            /* ════════════════════════════════════════════════
             *  ROWS 2-6 – Agency Header text (merged A2:AJ6)
             * ═══════════════════════════════════════════════ */
            $ws->mergeCells('A2:AJ6');
            $ws->setCellValue('A2',
                "Republic of the Philippines\n" .
                "Department of Education\n" .
                "National Capital Region\n" .
                "SCHOOLS DIVISION OFFICE, QUEZON CITY"
            );
            $ws->getStyle('A2')
               ->getAlignment()
               ->setHorizontal(Alignment::HORIZONTAL_CENTER)
               ->setVertical(Alignment::VERTICAL_CENTER)
               ->setWrapText(true);
            $ws->getStyle('A2')->getFont()->setSize(10);
            $ws->getRowDimension(2)->setRowHeight(15);
            $ws->getRowDimension(3)->setRowHeight(15);
            $ws->getRowDimension(4)->setRowHeight(15);
            $ws->getRowDimension(5)->setRowHeight(15);
            $ws->getRowDimension(6)->setRowHeight(15);

            /* ════════════════════════════════════════════════
             *  ROW 7 – blank spacer
             * ═══════════════════════════════════════════════ */
            $ws->getRowDimension(7)->setRowHeight(6);

            /* ════════════════════════════════════════════════
             *  ROW 8 – NAME OF SCHOOL | ATTENDANCE REPORT | PERIOD COVERED
             * ═══════════════════════════════════════════════ */
            $ws->getRowDimension(8)->setRowHeight(18);

            // Left: NAME OF SCHOOL
            $ws->mergeCells('A8:G8');
            $ws->setCellValue('A8', 'NAME OF SCHOOL:');
            $ws->getStyle('A8')->getFont()->setBold(true)->setSize(10);

            // Centre: ATTENDANCE REPORT
            $ws->mergeCells('L8:X8');
            $ws->setCellValue('L8', $reportTitle);
            $ws->getStyle('L8')->getFont()->setBold(true)->setSize(14);
            $ws->getStyle('L8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Right: PERIOD COVERED:
            $ws->mergeCells('Z8:AJ8');
            $ws->setCellValue('Z8', 'PERIOD COVERED:');
            $ws->getStyle('Z8')->getFont()->setBold(true)->setSize(10);

            /* ════════════════════════════════════════════════
             *  ROW 9 – Station name | (blank) | Date range
             * ═══════════════════════════════════════════════ */
            $ws->getRowDimension(9)->setRowHeight(16);

            $ws->mergeCells('A9:G9');
            $ws->setCellValue('A9', "DIVISION OFFICE - STATION $stn");
            $ws->getStyle('A9')->getFont()->setBold(true)->setSize(10);

            $ws->mergeCells('Z9:AJ9');
            $ws->setCellValue('Z9', $periodCovered);
            $ws->getStyle('Z9')->getFont()->setBold(true)->setSize(10);

            /* ════════════════════════════════════════════════
             *  ROW 10 – thin border separator
             * ═══════════════════════════════════════════════ */
            $ws->getRowDimension(10)->setRowHeight(4);
            $ws->getStyle('A10:AJ10')->getBorders()
               ->getBottom()->setBorderStyle(Border::BORDER_THIN);

            /* ════════════════════════════════════════════════
             *  ROWS 11-12 – Column headers
             *   Row 11: No. | Name | 1 2 3 … 31 | Absences | Producers
             *   Row 12: (merged) | (merged) | SUN/SA/... | With Pay | W/out Pay | (merged)
             * ═══════════════════════════════════════════════ */
            $ws->getRowDimension(11)->setRowHeight(20);
            $ws->getRowDimension(12)->setRowHeight(18);

            // No.
            $ws->mergeCells('A11:A12');
            $ws->setCellValue('A11', 'No.');

            // Name of Employee
            $ws->mergeCells('B11:B12');
            $ws->setCellValue('B11', "Name of Employee");

            // Days 1-31
            for ($d = 1; $d <= 31; $d++) {
                $col    = Coordinate::stringFromColumnIndex($d + 2);
                $inMonth = ($d <= $daysInMonth);

                // Row 11 – day number
                $ws->setCellValue($col . '11', $d);

                // Row 12 – day-of-week abbreviation or blank if out of month
                if ($inMonth) {
                    $ts  = mktime(0, 0, 0, $month, $d, $year);
                    $dow = (int) date('w', $ts);
                    $dowLabel = strtoupper(substr(date('D', $ts), 0, 2)); // SU, MO…
                    $ws->setCellValue($col . '12', $dowLabel);

                    // Weekend / holiday colours
                    if (isset($holidays[$d])) {
                        $this->applyHolidayHeaderStyle($ws, $col . '11');
                        $this->applyHolidayHeaderStyle($ws, $col . '12');
                    } elseif ($dow === 0 || $dow === 6) {
                        $this->applyWeekendHeaderStyle($ws, $col . '11');
                        $this->applyWeekendHeaderStyle($ws, $col . '12');
                    }
                } else {
                    // Out-of-month — grey out
                    $ws->getStyle($col . '11:' . $col . '12')
                       ->getFill()->setFillType(Fill::FILL_SOLID)
                       ->getStartColor()->setARGB('FFE5E7EB');
                }
            }

            // Absences header (span 2 cols on row 11)
            $ws->mergeCells('AH11:AI11');
            $ws->setCellValue('AH11', 'Absences');

            // Absence sub-headers on row 12
            $ws->setCellValue('AH12', "With\nPay");
            $ws->setCellValue('AI12', "W/ou\nt Pay");

            // Producers – spans both rows
            $ws->mergeCells('AJ11:AJ12');
            $ws->setCellValue('AJ11', "Producers in\nAccomplishing\nAttendance");

            /* Apply global header style to rows 11-12 */
            $headerRange = 'A11:AJ12';
            $ws->getStyle($headerRange)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            // Left-align the Name header
            $ws->getStyle('B11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            /* ════════════════════════════════════════════════
             *  DATA ROWS – starting at row 13
             * ═══════════════════════════════════════════════ */
            $rowNum    = 13;
            $empCount  = 1;

            // Instructions text – placed in 'AJ' spanning all employee rows (rowspan via merge)
            $firstDataRow = $rowNum;
            $instructionsText =
                "Cross out unnecessary dates e.g.\n\n" .
                "All entries shall be based on the individual DTR or Time Card;\n\n" .
                "Indicate the presence of the employee by a (/) mark in the date column;\n\n" .
                "Enter absences in terms of days in the date column either 1/2 or 1 whole day;\n\n" .
                "Enter tardiness in the date column in terms of minutes, halfdays or under times.";

            foreach ($stnEmps as $emp) {
                $eid       = $emp->id;
                $absPay    = 0;
                $absNoPay  = 0;

                $ws->getRowDimension($rowNum)->setRowHeight(15);

                // No. & Name
                $ws->setCellValue('A' . $rowNum, $empCount++);
                $ws->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $ws->setCellValue('B' . $rowNum, strtoupper($emp->last_name . ', ' . $emp->first_name));
                $ws->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Day cells
                for ($d = 1; $d <= 31; $d++) {
                    $col  = Coordinate::stringFromColumnIndex($d + 2);
                    $cell = $col . $rowNum;

                    if ($d > $daysInMonth) {
                        $ws->setCellValue($cell, '');
                        $ws->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                           ->getStartColor()->setARGB('FFE5E7EB');
                        continue;
                    }

                    $ts  = mktime(0, 0, 0, $month, $d, $year);
                    $dow = (int) date('w', $ts);
                    $sym = '';

                    if (isset($holidays[$d])) {
                        $sym = 'HOL';
                        $ws->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                           ->getStartColor()->setARGB('FFFFF0F0');
                        $ws->getStyle($cell)->getFont()->getColor()->setARGB('FFBE123C');
                    } elseif ($dow === 0) {
                        $sym = 'SUN';
                        $ws->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                           ->getStartColor()->setARGB('FFF8FAFC');
                        $ws->getStyle($cell)->getFont()->getColor()->setARGB('FF94A3B8');
                    } elseif ($dow === 6) {
                        $sym = 'SAT';
                        $ws->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                           ->getStartColor()->setARGB('FFF8FAFC');
                        $ws->getStyle($cell)->getFont()->getColor()->setARGB('FF94A3B8');
                    } elseif (isset($attendance[$eid][$d])) {
                        $s = $attendance[$eid][$d];
                        if ($s === 'present') {
                            $sym = '/';
                        } elseif ($s === 'late') {
                            $sym = 'L';
                            $ws->getStyle($cell)->getFont()->getColor()->setARGB('FFEA580C');
                        } elseif ($s === 'undertime') {
                            $sym = 'U';
                            $ws->getStyle($cell)->getFont()->getColor()->setARGB('FFB45309');
                        } elseif ($s === 'halfday') {
                            $sym = '1/2';
                            $ws->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                               ->getStartColor()->setARGB('FFFEE2E2');
                            $rsn = $reasons[$eid][$d] ?? '';
                            stripos($rsn, 'without pay') !== false ? $absNoPay += 0.5 : $absPay += 0.5;
                        } elseif ($s === 'absent') {
                            $sym = '1';
                            $ws->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                               ->getStartColor()->setARGB('FFFEE2E2');
                            $ws->getStyle($cell)->getFont()->getColor()->setARGB('FFDC2626');
                            $rsn = $reasons[$eid][$d] ?? '';
                            stripos($rsn, 'without pay') !== false ? $absNoPay++ : $absPay++;
                        }
                    }

                    $ws->setCellValue($cell, $sym);
                    $ws->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $ws->getStyle($cell)->getFont()->setSize(8)->setBold(true);
                }

                // Absence totals
                $ws->setCellValue('AH' . $rowNum, $absPay > 0 ? $absPay : '');
                $ws->setCellValue('AI' . $rowNum, $absNoPay > 0 ? $absNoPay : '');
                $ws->getStyle('AH' . $rowNum . ':AI' . $rowNum)
                   ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Borders for full row
                $ws->getStyle("A{$rowNum}:AI{$rowNum}")
                   ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $rowNum++;
            }

            /* Producers / Instructions column – merge all data rows & right of header */
            $lastDataRow = $rowNum - 1;
            if ($lastDataRow >= $firstDataRow) {
                $ws->mergeCells("AJ{$firstDataRow}:AJ{$lastDataRow}");
            }
            $ws->setCellValue('AJ' . $firstDataRow, $instructionsText);
            $ws->getStyle('AJ' . $firstDataRow)
               ->getAlignment()
               ->setHorizontal(Alignment::HORIZONTAL_LEFT)
               ->setVertical(Alignment::VERTICAL_TOP)
               ->setWrapText(true);
            $ws->getStyle('AJ' . $firstDataRow)->getFont()->setSize(8);
            $ws->getStyle("AJ{$firstDataRow}:AJ{$lastDataRow}")
               ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            /* ── Signatories ── */
            $this->applySignatories($ws, $rowNum + 1, $request, 'AJ');
        }

        /* ── File output ── */
        $stationLabel = ($station === 'All') ? 'All_Stations' : "Station_{$station}";
        $filenameBase = "Attendance_Report_{$stationLabel}_" . date('F_Y', mktime(0, 0, 0, $month, 1, $year));

        return $this->downloadSpreadsheet($spreadsheet, $filenameBase, $request->input('format', 'excel'));
    }

    /**
     * Build standard DepEd header
     */
    private function buildFormHeader($ws, $title, $periodCovered, $stn, $lastCol, $logoCol)
    {
        $logoPath = public_path('logo.png');
        $ws->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
           ->setPaperSize(PageSetup::PAPERSIZE_LEGAL)->setFitToPage(true);
           
        // Row 1
        $ws->getRowDimension(1)->setRowHeight(76.80);
        $ws->mergeCells("A1:{$lastCol}1");
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(70);
            $drawing->setCoordinates("{$logoCol}1");
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(3);
            $drawing->setWorksheet($ws);
        }
        
        // Row 2-6
        $ws->mergeCells("A2:{$lastCol}6");
        $ws->setCellValue('A2', "Republic of the Philippines\nDepartment of Education\nNational Capital Region\nSCHOOLS DIVISION OFFICE, QUEZON CITY");
        $ws->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $ws->getStyle('A2')->getFont()->setSize(10);
        for ($r=2; $r<=6; $r++) $ws->getRowDimension($r)->setRowHeight(15);
        
        // Row 7 Space
        $ws->getRowDimension(7)->setRowHeight(6);
        
        // Row 8
        $ws->getRowDimension(8)->setRowHeight(18);
        $ws->setCellValue('A8', 'NAME OF SCHOOL:');
        $ws->getStyle('A8')->getFont()->setBold(true)->setSize(10);
        
        $ws->setCellValue("{$logoCol}8", $title);
        $ws->getStyle("{$logoCol}8")->getFont()->setBold(true)->setSize(14);
        $ws->getStyle("{$logoCol}8")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $pCol = chr(ord($lastCol) - 1); 
        $ws->setCellValue("{$pCol}8", 'PERIOD COVERED:');
        $ws->getStyle("{$pCol}8")->getFont()->setBold(true)->setSize(10);
        
        // Row 9
        $ws->getRowDimension(9)->setRowHeight(16);
        $ws->setCellValue('A9', "DIVISION OFFICE - STATION $stn");
        $ws->getStyle('A9')->getFont()->setBold(true)->setSize(10);
        
        $ws->setCellValue("{$pCol}9", $periodCovered);
        $ws->getStyle("{$pCol}9")->getFont()->setBold(true)->setSize(10);
        
        // Row 10
        $ws->getRowDimension(10)->setRowHeight(4);
        $ws->getStyle("A10:{$lastCol}10")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }

    /**
     * Export Weekly Summary (Excel)
     */
    public function weeklyReport(Request $request)
    {
        $db = DB::connection();
        $date = $request->input('date', date('Y-m-d'));
        $statusFilter = strtolower($request->input('status', 'all'));
        $ts = strtotime($date);
        $startOfWeek = date('Y-m-d', strtotime('monday this week', $ts));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week', $ts));
        $periodCov = date('M d', strtotime($startOfWeek)) . ' - ' . date('M d, Y', strtotime($endOfWeek));
        
        $having = "";
        if ($statusFilter === 'present') $having = "HAVING pres > 0";
        elseif ($statusFilter === 'late') $having = "HAVING late > 0";
        elseif ($statusFilter === 'undertime') $having = "HAVING ut > 0";
        elseif ($statusFilter === 'absent') $having = "HAVING abs > 0";

        $sql = "
            SELECT e.emp_name, e.station,
                   SUM(CASE WHEN d.status IN ('present', 'late') THEN 1 ELSE 0 END) as pres,
                   SUM(CASE WHEN d.status = 'late' THEN 1 ELSE 0 END) as late,
                   SUM(CASE WHEN d.status = 'undertime' THEN 1 ELSE 0 END) as ut,
                   SUM(CASE WHEN d.status IN ('absent', 'halfday') THEN 1 ELSE 0 END) as abs
            FROM employees e
            LEFT JOIN daily_attendance d ON e.id = d.employee_id AND CONCAT(d.year_month, '-', LPAD(d.day, 2, '0')) BETWEEN ? AND ?
            GROUP BY e.id, e.emp_name, e.station 
            $having
            ORDER BY e.station, e.emp_name
        ";
        $rows = $db->select($sql, [$startOfWeek, $endOfWeek]);

        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Weekly Report');
        
        $titlePrefix = ($statusFilter === 'all') ? 'WEEKLY ATTENDANCE' : "WEEKLY " . strtoupper($statusFilter);
        $this->buildFormHeader($ws, "{$titlePrefix} REPORT", $periodCov, 'All', 'G', 'D');
        
        $ws->getColumnDimension('A')->setWidth(6);
        $ws->getColumnDimension('B')->setWidth(35);
        $ws->getColumnDimension('C')->setWidth(25);
        $ws->getColumnDimension('D')->setWidth(15);
        $ws->getColumnDimension('E')->setWidth(15);
        $ws->getColumnDimension('F')->setWidth(15);
        $ws->getColumnDimension('G')->setWidth(15);
        
        $ws->setCellValue('A11', 'No.');
        $ws->setCellValue('B11', 'Name of Employee');
        $ws->setCellValue('C11', 'Station');
        $ws->setCellValue('D11', 'Total Present');
        $ws->setCellValue('E11', 'Total Lates');
        $ws->setCellValue('F11', 'Total Undertimes');
        $ws->setCellValue('G11', 'Total Absences');
        
        $ws->getStyle("A11:G11")->getFont()->setBold(true);
        $ws->getStyle("A11:G11")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $ws->getStyle("A11:G11")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        
        $rowIdx = 12;
        foreach($rows as $i => $r) {
            $ws->setCellValue("A{$rowIdx}", $i + 1);
            $ws->setCellValue("B{$rowIdx}", $r->emp_name);
            $ws->setCellValue("C{$rowIdx}", $r->station);
            $ws->setCellValue("D{$rowIdx}", $r->pres);
            $ws->setCellValue("E{$rowIdx}", $r->late);
            $ws->setCellValue("F{$rowIdx}", $r->ut);
            $ws->setCellValue("G{$rowIdx}", $r->abs);
            $ws->getStyle("A{$rowIdx}:G{$rowIdx}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $ws->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->getStyle("D{$rowIdx}:G{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowIdx++;
        }
        
        /* ── Signatories ── */
        $this->applySignatories($ws, $rowIdx + 1, $request, 'G');

        $filenameBase = "Weekly_Attendance_{$startOfWeek}";
        return $this->downloadSpreadsheet($spreadsheet, $filenameBase, $request->input('format', 'excel'));
    }

    /**
     * Export Yearly Summary (Excel)
     */
    public function yearlyReport(Request $request)
    {
        $db = DB::connection();
        $year = (int) $request->input('year', date('Y'));
        $statusFilter = strtolower($request->input('status', 'all'));

        $having = "";
        if ($statusFilter === 'present') $having = "HAVING pres > 0";
        elseif ($statusFilter === 'late') $having = "HAVING late > 0";
        elseif ($statusFilter === 'undertime') $having = "HAVING ut > 0";
        elseif ($statusFilter === 'absent') $having = "HAVING abs > 0";

        $sql = "
            SELECT e.emp_name, e.station,
                   SUM(CASE WHEN d.status IN ('present', 'late') THEN 1 ELSE 0 END) as pres,
                   SUM(CASE WHEN d.status = 'late' THEN 1 ELSE 0 END) as late,
                   SUM(CASE WHEN d.status = 'undertime' THEN 1 ELSE 0 END) as ut,
                   SUM(CASE WHEN d.status IN ('absent', 'halfday') THEN 1 ELSE 0 END) as abs
            FROM employees e
            LEFT JOIN daily_attendance d ON e.id = d.employee_id AND d.year_month LIKE ?
            GROUP BY e.id, e.emp_name, e.station 
            $having
            ORDER BY e.station, e.emp_name
        ";
        $rows = $db->select($sql, ["$year-%"]);

        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Yearly Report');
        
        $titlePrefix = ($statusFilter === 'all') ? 'YEARLY ATTENDANCE' : "YEARLY " . strtoupper($statusFilter);
        $this->buildFormHeader($ws, "{$titlePrefix} REPORT", "JAN - DEC $year", 'All', 'G', 'D');
        
        $ws->getColumnDimension('A')->setWidth(6);
        $ws->getColumnDimension('B')->setWidth(35);
        $ws->getColumnDimension('C')->setWidth(25);
        $ws->getColumnDimension('D')->setWidth(15);
        $ws->getColumnDimension('E')->setWidth(15);
        $ws->getColumnDimension('F')->setWidth(15);
        $ws->getColumnDimension('G')->setWidth(15);
        
        $ws->setCellValue('A11', 'No.');
        $ws->setCellValue('B11', 'Name of Employee');
        $ws->setCellValue('C11', 'Station');
        $ws->setCellValue('D11', 'Total Present');
        $ws->setCellValue('E11', 'Total Lates');
        $ws->setCellValue('F11', 'Total Undertimes');
        $ws->setCellValue('G11', 'Total Absences');
        
        $ws->getStyle("A11:G11")->getFont()->setBold(true);
        $ws->getStyle("A11:G11")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $ws->getStyle("A11:G11")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        
        $rowIdx = 12;
        foreach($rows as $i => $r) {
            $ws->setCellValue("A{$rowIdx}", $i + 1);
            $ws->setCellValue("B{$rowIdx}", $r->emp_name);
            $ws->setCellValue("C{$rowIdx}", $r->station);
            $ws->setCellValue("D{$rowIdx}", $r->pres);
            $ws->setCellValue("E{$rowIdx}", $r->late);
            $ws->setCellValue("F{$rowIdx}", $r->ut);
            $ws->setCellValue("G{$rowIdx}", $r->abs);
            $ws->getStyle("A{$rowIdx}:G{$rowIdx}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $ws->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->getStyle("D{$rowIdx}:G{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowIdx++;
        }
        
        /* ── Signatories ── */
        $this->applySignatories($ws, $rowIdx + 1, $request, 'G');

        $filenameBase = "Yearly_Attendance_{$year}";
        return $this->downloadSpreadsheet($spreadsheet, $filenameBase, $request->input('format', 'excel'));
    }

    /* ── Helpers ── */

    private function downloadSpreadsheet($spreadsheet, $filenameBase, $format)
    {
        if ($format === 'preview') {
            $writer = IOFactory::createWriter($spreadsheet, 'Html');
            ob_start();
            $writer->save('php://output');
            $html = ob_get_clean();
            
            return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
        }
        
        if ($format === 'pdf') {
            $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class;
            IOFactory::registerWriter('Pdf', $class);
            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
                $writer->save('php://output');
            }, "{$filenameBase}.pdf", [
                'Content-Type' => 'application/pdf',
            ]);
        }
        
        if ($format === 'docx') {
            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = IOFactory::createWriter($spreadsheet, 'Html');
                $writer->save('php://output');
            }, "{$filenameBase}.doc", [
                'Content-Type' => 'application/msword',
            ]);
        }
        
        // Excel Default
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, "{$filenameBase}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function applySignatories($ws, $rowNum, Request $request, $lastCol)
    {
        $prepName = $request->input('prep_name') ?: 'CHRISTINE JOY C. MAAPOY';
        $prepPos  = $request->input('prep_pos')  ?: 'Administrative Assistant III';
        $prepPos2 = $request->input('prep_pos2') ?: 'E-Form7 In-Charge';
        $prepPos3 = $request->input('prep_pos3') ?: '';

        $certName = $request->input('cert_name') ?: 'MICHELLE A. MAL-IN';
        $certPos  = $request->input('cert_pos')  ?: 'Human Resource Management Officer II';
        $certPos2 = $request->input('cert_pos2') ?: 'Administrative Officer IV';
        $certPos3 = $request->input('cert_pos3') ?: '';

        $verName  = $request->input('ver_name')  ?: 'ROSELYN B. SENCIL';
        $verPos   = $request->input('ver_pos')   ?: 'Human Resource Management Officer V';
        $verPos2  = $request->input('ver_pos2')  ?: 'Administrative Officer V';
        $verPos3  = $request->input('ver_pos3')  ?: '';

        /* ════════════════════════════════════════════════
         *  FOOTER – Certification + Signatories
         * ═══════════════════════════════════════════════ */

        $ws->getRowDimension($rowNum)->setRowHeight(8); // blank spacer row

        /* ── Certification block ── */
        $certR1 = $rowNum + 1;
        $certR2 = $rowNum + 2;
        $ws->getRowDimension($certR1)->setRowHeight(14);
        $ws->getRowDimension($certR2)->setRowHeight(24);

        $prepEnd = ($lastCol === 'G') ? 'B' : 'D';
        $certStart = ($lastCol === 'G') ? 'C' : 'E';
        $certEnd = ($lastCol === 'G') ? 'E' : 'Y';
        $verStart = ($lastCol === 'G') ? 'F' : 'Z';

        // "Prepared By:" label
        $ws->mergeCells("A{$certR1}:{$prepEnd}{$certR1}");
        $ws->setCellValue("A{$certR1}", 'Prepared By:');
        $ws->getStyle("A{$certR1}")->getFont()->setBold(true)->setSize(9);

        // "Certification:" label + text
        $ws->mergeCells("{$certStart}{$certR1}:{$certEnd}{$certR1}");
        $ws->setCellValue("{$certStart}{$certR1}", 'Certification:');
        $ws->getStyle("{$certStart}{$certR1}")->getFont()->setBold(true)->setSize(9);

        $ws->mergeCells("{$certStart}{$certR2}:{$certEnd}{$certR2}");
        $ws->setCellValue("{$certStart}{$certR2}", 'This is to certify that personnel listed in this report have rendered services');
        $ws->getStyle("{$certStart}{$certR2}")->getFont()->setItalic(true)->setSize(($lastCol === 'G') ? 7 : 9);
        $ws->getStyle("{$certStart}{$certR2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

        // "Verified Correct:" label
        $ws->mergeCells("{$verStart}{$certR1}:{$lastCol}{$certR1}");
        $ws->setCellValue("{$verStart}{$certR1}", 'Verified Correct:');
        $ws->getStyle("{$verStart}{$certR1}")->getFont()->setBold(true)->setSize(9);

        // Borders around the 3 cert sections
        foreach (["A{$certR1}:{$prepEnd}{$certR2}", "{$certStart}{$certR1}:{$certEnd}{$certR2}", "{$verStart}{$certR1}:{$lastCol}{$certR2}"] as $range) {
            $ws->getStyle($range)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        /* ── Signatory rows ── */
        $sigRow1 = $certR2 + 1; // blank spacer
        $ws->getRowDimension($sigRow1)->setRowHeight(8);

        $sigRow2 = $sigRow1 + 1; // names
        $sigRow3 = $sigRow2 + 1; // position 1
        $sigRow4 = $sigRow3 + 1; // position 2
        $sigRow5 = $sigRow4 + 1; // position 3

        foreach ([$sigRow2, $sigRow3, $sigRow4, $sigRow5] as $r) $ws->getRowDimension($r)->setRowHeight(14);
        
        // Underline above names
        $ws->getStyle("A{$sigRow2}:{$lastCol}{$sigRow2}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        // Signatories blocks
        $prepSigEnd = ($lastCol === 'G') ? 'B' : 'H';
        $certSigStart = ($lastCol === 'G') ? 'C' : 'L';
        $certSigEnd = ($lastCol === 'G') ? 'E' : 'X';
        $verSigStart = ($lastCol === 'G') ? 'F' : 'Z';

        // Prepared By
        $ws->mergeCells("A{$sigRow2}:{$prepSigEnd}{$sigRow2}");
        $ws->setCellValue("A{$sigRow2}", strtoupper($prepName));
        $ws->getStyle("A{$sigRow2}")->getFont()->setBold(true)->setSize(9);
        $ws->getStyle("A{$sigRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->setCellValue("A{$sigRow3}", $prepPos);
        $ws->mergeCells("A{$sigRow3}:{$prepSigEnd}{$sigRow3}");
        $ws->setCellValue("A{$sigRow4}", $prepPos2);
        $ws->mergeCells("A{$sigRow4}:{$prepSigEnd}{$sigRow4}");
        $ws->setCellValue("A{$sigRow5}", $prepPos3);
        $ws->mergeCells("A{$sigRow5}:{$prepSigEnd}{$sigRow5}");

        // Certified Correct
        $ws->mergeCells("{$certSigStart}{$sigRow2}:{$certSigEnd}{$sigRow2}");
        $ws->setCellValue("{$certSigStart}{$sigRow2}", strtoupper($certName));
        $ws->getStyle("{$certSigStart}{$sigRow2}")->getFont()->setBold(true)->setSize(9);
        $ws->getStyle("{$certSigStart}{$sigRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->setCellValue("{$certSigStart}{$sigRow3}", $certPos);
        $ws->mergeCells("{$certSigStart}{$sigRow3}:{$certSigEnd}{$sigRow3}");
        $ws->setCellValue("{$certSigStart}{$sigRow4}", $certPos2);
        $ws->mergeCells("{$certSigStart}{$sigRow4}:{$certSigEnd}{$sigRow4}");
        $ws->setCellValue("{$certSigStart}{$sigRow5}", $certPos3);
        $ws->mergeCells("{$certSigStart}{$sigRow5}:{$certSigEnd}{$sigRow5}");

        // Verified Correct
        $ws->mergeCells("{$verSigStart}{$sigRow2}:{$lastCol}{$sigRow2}");
        $ws->setCellValue("{$verSigStart}{$sigRow2}", strtoupper($verName));
        $ws->getStyle("{$verSigStart}{$sigRow2}")->getFont()->setBold(true)->setSize(9);
        $ws->getStyle("{$verSigStart}{$sigRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->setCellValue("{$verSigStart}{$sigRow3}", $verPos);
        $ws->mergeCells("{$verSigStart}{$sigRow3}:{$lastCol}{$sigRow3}");
        $ws->setCellValue("{$verSigStart}{$sigRow4}", $verPos2);
        $ws->mergeCells("{$verSigStart}{$sigRow4}:{$lastCol}{$sigRow4}");
        $ws->setCellValue("{$verSigStart}{$sigRow5}", $verPos3);
        $ws->mergeCells("{$verSigStart}{$sigRow5}:{$lastCol}{$sigRow5}");

        // Style positions
        foreach ([$sigRow3, $sigRow4, $sigRow5] as $r) {
            $ws->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setSize(8);
            $ws->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Underlines under names
        foreach (["A{$sigRow2}:{$prepSigEnd}{$sigRow2}", "{$certSigStart}{$sigRow2}:{$certSigEnd}{$sigRow2}", "{$verSigStart}{$sigRow2}:{$lastCol}{$sigRow2}"] as $range) {
            $ws->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        }
    }

    private function applyWeekendHeaderStyle($ws, string $cell): void
    {
        $ws->getStyle($cell)->getFill()
           ->setFillType(Fill::FILL_SOLID)
           ->getStartColor()->setARGB('FFF0F4F8');
        $ws->getStyle($cell)->getFont()->getColor()->setARGB('FF64748B');
    }

    private function applyHolidayHeaderStyle($ws, string $cell): void
    {
        $ws->getStyle($cell)->getFill()
           ->setFillType(Fill::FILL_SOLID)
           ->getStartColor()->setARGB('FFFFF0F0');
        $ws->getStyle($cell)->getFont()->getColor()->setARGB('FFBE123C');
    }
}
