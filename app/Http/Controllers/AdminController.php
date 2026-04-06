<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    private function getDb()
    {
        return DB::connection();
    }

    private function logAudit(string $action, string $module, string $details = ''): void
    {
        $this->getDb()->insert(
            "INSERT INTO audit_logs (user_id, username, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
            [session('user_id', 0), session('username', 'Unknown'), $action, $module, $details, request()->ip()]
        );
    }

    private function getAllHolidays(int $year): array
    {
        $db = $this->getDb();
        $holidays = [
            "$year-01-01" => "New Year",
            "$year-02-25" => "EDSA `day`",
            "$year-04-09" => "Valor `day`",
            "$year-05-01" => "Labor `day`",
            "$year-06-12" => "Independence",
            "$year-08-21" => "Ninoy `day`",
            "$year-08-31" => "Heroes `day`",
            "$year-11-01" => "All Saints",
            "$year-11-30" => "Bonifacio",
            "$year-12-25" => "Christmas",
            "$year-12-30" => "Rizal `day`"
        ];
        $rows = $db->select("SELECT date_str, reason FROM special_days WHERE date_str LIKE ?", ["$year-%"]);
        foreach ($rows as $row)
            $holidays[$row->date_str] = $row->reason;
        return $holidays;
    }

    // ─── GET: Dashboard ─────────────────────────────────────────────
    public function dashboard()
    {
        $db = $this->getDb();
        $currentYM = date('Y-m');
        $currentDay = (int) date('d');

        $stats = ['present' => 0, 'late' => 0, 'absent' => 0];
        $rows = $db->select("SELECT `status`, COUNT(*) as `count` FROM daily_attendance WHERE `year_month` = ? AND `day` = ? GROUP BY `status`", [$currentYM, $currentDay]);
        foreach ($rows as $row)
            $stats[strtolower($row->status)] = $row->count;

        $totalEmp = $db->selectOne("SELECT COUNT(*) as cnt FROM employees")->cnt;
        $cntActive = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE `status` = 'ACTIVE'")->cnt;
        $cntInactive = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE `status` = 'INACTIVE'")->cnt;
        $cntOthers = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE `status` != 'ACTIVE' AND `status` != 'INACTIVE'")->cnt;
        $logs = $db->select("SELECT * FROM login_logs ORDER BY login_time DESC LIMIT 5");

        return view('admin.dashboard', compact('stats', 'totalEmp', 'cntActive', 'cntInactive', 'cntOthers', 'logs'));
    }

    // ─── GET: Reports ─────────────────────────────────────────────
    public function reports()
    {
        return view('admin.reports');
    }

    // ─── GET: Employee Management ───────────────────────────────────
    public function index(Request $request)
    {
        $db = $this->getDb();
        $search = trim($request->input('search', ''));
        $filterStation = $request->input('station', '');
        $mode = $request->input('mode', 'monthly');
        $year = (int) ($request->input('year', date('Y')));
        $month = (int) ($request->input('month', date('m')));
        if ($month < 1 || $month > 12)
            $month = (int) date('m');

        $sql = "SELECT * FROM employees WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (last_name LIKE ? OR first_name LIKE ? OR emp_number LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
        }
        if ($filterStation) {
            $sql .= " AND station = ?";
            $params[] = $filterStation;
        }
        $sql .= " ORDER BY last_name ASC";
        $employees = $db->select($sql, $params);
        $stations = $db->select("SELECT DISTINCT station FROM employees WHERE station IS NOT NULL AND station != '' ORDER BY station");

        // ── Attendance summary for selected month or year ──
        $ym = sprintf('%04d-%02d', $year, $month);
        $monthLabel = $mode === 'yearly'
            ? $year
            : date('F Y', mktime(0, 0, 0, $month, 1, $year));

        $dateFilterParams = [];
        $dateFilterCondition = "";

        if ($mode === 'yearly') {
            $dateFilterCondition = "`year_month` LIKE ?";
            $dateFilterParams = [$year . '-%'];
        } else {
            $dateFilterCondition = "`year_month` = ?";
            $dateFilterParams = [$ym];
        }

        // Status counts per employee
        $attSummary = [];
        $attRows = $db->select(
            "SELECT employee_id, `day`, `status` FROM daily_attendance WHERE $dateFilterCondition",
            $dateFilterParams
        );
        $attendanceSummary = [];
        foreach ($attRows as $row) {
            $statusKey = strtolower($row->status);
            $empId = $row->employee_id;
            if (!isset($attendanceSummary[$empId])) {
                $attendanceSummary[$empId] = ['present' => 0, 'present_days' => [], 'absent' => 0, 'late' => 0, 'undertime' => 0, 'halfday' => 0];
            }
            if ($statusKey === 'present') {
                $attendanceSummary[$empId]['present']++;
                $attendanceSummary[$empId]['present_days'][] = $row->day;
            } else {
                $attendanceSummary[$empId][$statusKey] = ($attendanceSummary[$empId][$statusKey] ?? 0) + 1;
            }
        }

        // Late minutes totals & details
        $lateMins = [];
        $lateDetails = [];
        $lateRows = $db->select("SELECT id, employee_id, day, minutes FROM late_minutes WHERE $dateFilterCondition ORDER BY day ASC", $dateFilterParams);
        foreach ($lateRows as $r) {
            $empId = $r->employee_id;
            if (!isset($lateMins[$empId])) {
                $lateMins[$empId] = 0;
                $lateDetails[$empId] = [];
            }
            $lateMins[$empId] += (int) $r->minutes;
            $lateDetails[$empId][] = ['id' => $r->id, 'day' => $r->day, 'mins' => $r->minutes, 'type' => 'Late'];
        }

        // Undertime minutes totals & details
        $undertimeMins = [];
        $utDetails = [];
        try {
            $utRows = $db->select("SELECT id, employee_id, day, minutes FROM undertime_minutes WHERE $dateFilterCondition ORDER BY day ASC", $dateFilterParams);
            foreach ($utRows as $r) {
                $empId = $r->employee_id;
                if (!isset($undertimeMins[$empId])) {
                    $undertimeMins[$empId] = 0;
                    $utDetails[$empId] = [];
                }
                $undertimeMins[$empId] += (int) $r->minutes;
                $utDetails[$empId][] = ['id' => $r->id, 'day' => $r->day, 'mins' => $r->minutes, 'type' => 'Undertime'];
            }
        } catch (\Exception $e) {
        }

        // Absence reasons – with/without pay
        $absReasons = [];
        $absDetails = [];
        foreach ($db->select("SELECT employee_id, day, reason FROM absence_reasons WHERE $dateFilterCondition", $dateFilterParams) as $r) {
            if (!isset($absReasons[$r->employee_id])) {
                $absReasons[$r->employee_id] = ['with_pay' => 0, 'without_pay' => 0];
                $absDetails[$r->employee_id] = ['with_pay_days' => [], 'without_pay_days' => []];
            }
            if (!isset($absDetails[$r->employee_id])) {
                $absDetails[$r->employee_id] = ['with_pay_days' => [], 'without_pay_days' => []];
            }
            
            if (stripos($r->reason, 'without') !== false) {
                $absReasons[$r->employee_id]['without_pay']++;
                $absDetails[$r->employee_id]['without_pay_days'][] = $r->day;
            } else {
                $absReasons[$r->employee_id]['with_pay']++;
                $absDetails[$r->employee_id]['with_pay_days'][] = $r->day;
            }
        }

        // Stats for the new header
        $totalEmp = $db->selectOne("SELECT COUNT(*) as cnt FROM employees")->cnt;
        $activeCount = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE `status` = 'ACTIVE'")->cnt;
        $leaveCount = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE `status` = 'ON LEAVE'")->cnt;
        $inactiveCount = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE `status` != 'ACTIVE' AND `status` != 'ON LEAVE'")->cnt;

        return view('admin.index', compact(
            'employees',
            'stations',
            'search',
            'filterStation',
            'attendanceSummary',
            'lateMins',
            'undertimeMins',
            'lateDetails',
            'utDetails',
            'absReasons',
            'absDetails',
            'year',
            'month',
            'monthLabel',
            'mode',
            'ym',
            'totalEmp',
            'activeCount',
            'leaveCount',
            'inactiveCount'
        ));
    }


    // ─── POST: Add Employee ─────────────────────────────────────────
    public function addEmployee(Request $request)
    {
        $db = $this->getDb();
        $lastName = trim($request->input('last_name', ''));
        $firstName = trim($request->input('first_name', ''));
        $empNumber = trim($request->input('emp_number', ''));
        $middleName = trim($request->input('middle_name', ''));
        $station = $request->input('station', '');
        $officialTime = trim($request->input('official_time', '')); // Capture official time
        if (!$lastName || !$firstName || !$empNumber) {
            return response()->json(['success' => false, 'message' => 'Name and Employee Number are required']);
        }
        try {
            $fullName = "$lastName, $firstName $middleName";
            $db->insert(
                "INSERT INTO employees (emp_name, emp_number, station, last_name, first_name, middle_name, `status`, official_time) VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE', ?)",
                [$fullName, $empNumber, $station, $lastName, $firstName, $middleName, $officialTime]
            );
            $this->logAudit('Add Employee', 'Employee', "Added: $fullName ($empNumber)");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Import Excel ─────────────────────────────────────────
    public function importExcel(Request $request)
    {
        try {
            $db = $this->getDb();
            $inserted = 0;
            $skipped = 0;
            $errors = [];

            // Accept pre-edited JSON rows from the preview table
            if ($request->has('rows')) {
                $rows = json_decode($request->input('rows'), true);
                if (!is_array($rows)) {
                    return response()->json(['success' => false, 'message' => 'Invalid data format']);
                }
                foreach ($rows as $i => $row) {
                    $ln  = mb_strtoupper(trim($row['last_name'] ?? ''));
                    $fn  = ucwords(mb_strtolower(trim($row['first_name'] ?? '')));
                    $mn  = ucwords(mb_strtolower(trim($row['middle_name'] ?? '')));
                    $en  = trim($row['emp_number'] ?? '');
                    $st  = trim($row['station'] ?? '');
                    $ot  = trim($row['official_time'] ?? '');

                    // Only skip if BOTH first name and last name are missing
                    if (!$ln && !$fn) { $skipped++; continue; }

                    // Auto-generate Employee ID if not provided
                    if (!$en) {
                        $en = 'EMP-' . date('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                        // Ensure uniqueness — keep incrementing suffix if needed
                        $suffix = 1;
                        $baseEn = $en;
                        while ($db->selectOne("SELECT id FROM employees WHERE emp_number = ?", [$en])) {
                            $en = $baseEn . '-' . $suffix++;
                        }
                    }

                    // Use first name as last name fallback if one is missing
                    if (!$ln) $ln = mb_strtoupper($fn);
                    if (!$fn) $fn = ucwords(mb_strtolower($ln));

                    try {
                        $full = $mn ? "$ln, $fn $mn" : "$ln, $fn";
                        $db->insert(
                            "INSERT INTO employees (
                                emp_name, emp_number, station, last_name, first_name, middle_name, `status`,
                                official_time, without_pay, tardy, tardy_minutes, tardiness_dates, gender
                             ) VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE', ?, 0.00, 0, 0, '', 'Male')",
                            [$full, $en, $st, $ln, $fn, $mn, $ot]
                        );
                        $this->logAudit('Import Excel', 'Employee', "Imported: $full ($en)");
                        $inserted++;
                    } catch (\Exception $ex) {
                        $errors[] = "Row " . ($i + 1) . " ($en): " . $ex->getMessage();
                        $skipped++;
                    }
                }
                return response()->json(['success' => true, 'inserted' => $inserted, 'skipped' => $skipped, 'errors' => $errors]);
            }

            // Fallback: file-based import (for backwards compatibility)
            if (!$request->hasFile('excel_file')) {
                return response()->json(['success' => false, 'message' => 'No file or data provided']);
            }
            $file = $request->file('excel_file');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                return response()->json(['success' => false, 'message' => 'Only .xlsx, .xls, or .csv allowed']);
            }
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $fileRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $colMap = $this->detectExcelColumns($fileRows[0] ?? []);

            foreach (array_slice($fileRows, 1) as $i => $row) {
                $ln  = mb_strtoupper(trim((string) ($row[$colMap['last_name']]  ?? '')));
                $fn  = ucwords(mb_strtolower(trim((string) ($row[$colMap['first_name']] ?? ''))));
                $mn  = ucwords(mb_strtolower(trim((string) ($row[$colMap['middle_name'] ?? 999] ?? ''))));
                $en  = trim((string) ($row[$colMap['emp_number']] ?? ''));
                $st  = trim((string) ($row[$colMap['station'] ?? 999] ?? ''));
                $ot  = trim((string) ($row[$colMap['official_time'] ?? 999] ?? ''));

                if (!$ln || !$fn || !$en) { $skipped++; continue; }

                try {
                    $full = "$ln, $fn $mn";
                    $db->insert(
                        "INSERT INTO employees (emp_name, emp_number, station, last_name, first_name, middle_name, `status`, official_time) VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE', ?)",
                        [$full, $en, $st, $ln, $fn, $mn, $ot]
                    );
                    $this->logAudit('Import Excel', 'Employee', "Imported: $full ($en)");
                    $inserted++;
                } catch (\Exception $ex) {
                    $errors[] = "Row " . ($i + 2) . ": " . $ex->getMessage();
                    $skipped++;
                }
            }
            return response()->json(['success' => true, 'inserted' => $inserted, 'skipped' => $skipped, 'errors' => $errors]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Preview Excel Before Import ──────────────────────────
    public function previewExcel(Request $request)
    {
        try {
            if (!$request->hasFile('excel_file')) {
                return response()->json(['success' => false, 'message' => 'No file uploaded']);
            }
            $file = $request->file('excel_file');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                return response()->json(['success' => false, 'message' => 'Only .xlsx, .xls, or .csv allowed']);
            }
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $db = $this->getDb();

            $colMap = $this->detectExcelColumns($rows[0] ?? []);

            // Get existing employee numbers to detect duplicates
            $existingNums = collect($db->select("SELECT emp_number FROM employees"))->pluck('emp_number')->map(fn($v) => strtoupper(trim($v)))->toArray();

            // Build detected column labels for the frontend
            $headerRow = $rows[0] ?? [];
            $detected = [];
            foreach ($colMap as $field => $colIdx) {
                if ($colIdx !== null) {
                    $detected[$field] = trim((string) ($headerRow[$colIdx] ?? "Column " . ($colIdx + 1)));
                }
            }

            $preview = [];
            foreach (array_slice($rows, 1) as $i => $row) {
                $ln  = trim((string) ($row[$colMap['last_name']]  ?? ''));
                $fn  = trim((string) ($row[$colMap['first_name']] ?? ''));
                $mn  = trim((string) ($row[$colMap['middle_name'] ?? 999] ?? ''));
                $en  = trim((string) ($row[$colMap['emp_number']] ?? ''));
                $st  = trim((string) ($row[$colMap['station'] ?? 999] ?? ''));
                $ot  = trim((string) ($row[$colMap['official_time'] ?? 999] ?? ''));

                // Skip completely empty rows
                if (!$ln && !$fn && !$en) continue;

                // Proper name casing
                $lnFormatted = mb_strtoupper($ln);
                $fnFormatted = ucwords(mb_strtolower($fn));
                $mnFormatted = ucwords(mb_strtolower($mn));

                $issues = [];
                if (!$ln) $issues[] = 'Missing last name';
                if (!$fn) $issues[] = 'Missing first name';
                if (!$en) $issues[] = 'Missing employee ID';
                if (!$st) $issues[] = 'Missing station';
                if ($en && in_array(strtoupper(trim($en)), $existingNums)) $issues[] = 'Duplicate ID';

                $preview[] = [
                    'row' => $i + 2,
                    'last_name' => $lnFormatted,
                    'first_name' => $fnFormatted,
                    'middle_name' => $mnFormatted,
                    'emp_number' => strtoupper($en),
                    'station' => $st,
                    'official_time' => $ot,
                    'valid' => count($issues) === 0,
                    'issues' => $issues,
                ];
            }
            return response()->json([
                'success' => true,
                'preview' => $preview,
                'total' => count($preview),
                'detected' => $detected,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Smart column auto-detection for Excel imports.
     * Reads header row and fuzzy-matches column names to fields.
     */
    private function detectExcelColumns(array $headerRow): array
    {
        $map = [
            'last_name'   => null,
            'first_name'  => null,
            'middle_name' => null,
            'emp_number'  => null,
            'station'     => null,
            'official_time' => null,
        ];

        // Keywords for each field (checked in order of priority). [\s\W]* handles any spaces/punctuation
        $patterns = [
            'last_name'   => ['last[\s\W]*name', 'surname', 'family[\s\W]*name', 'apelyido', '^l[\s\W]*name', '^lname'],
            'first_name'  => ['first[\s\W]*name', 'given[\s\W]*name', 'pangalan', '^f[\s\W]*name', '^fname'],
            'middle_name' => ['middle[\s\W]*name', 'mid[\s\W]*name', 'middle[\s\W]*initial', '^m[\s\W]*name', '^mname', '^mi$'],
            'emp_number'  => ['emp[\s\W]*(?:loyee)?[\s\W]*(?:no|num|number|id)', 'id[\s\W]*(?:no|num|number)', 'personnel[\s\W]*(?:no|num|id)', 'item[\s\W]*no', 'plantilla', 'employee[\s\W]*#'],
            'station'     => ['station', 'office', 'school', 'division', 'department', 'unit', 'assignment', 'workplace', 'location', 'place'],
            'official_time' => ['time', 'sched', 'schedule', 'shift', 'official[\s\W]*time'],
        ];

        foreach ($headerRow as $colIdx => $rawHeader) {
            $header = strtolower(trim((string) $rawHeader));
            if (!$header) continue;

            foreach ($patterns as $field => $keywords) {
                if ($map[$field] !== null) continue; // already matched
                foreach ($keywords as $kw) {
                    if (preg_match('/' . $kw . '/i', $header)) {
                        $map[$field] = $colIdx;
                        break 2; // matched this header, move to next column
                    }
                }
            }
        }

        return $map;
    }

    // ─── GET: Download Import Template ──────────────────────────────
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Employees');
        $headers = ['Last Name', 'First Name', 'Middle Name', 'Employee ID', 'Station', 'Time'];
        foreach ($headers as $col => $h) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue($letter . '1', $h);
            $sheet->getStyle($letter . '1')->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
        }
        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A5F');
        $sheet->getStyle('A1:F1')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sample = ['DELA CRUZ', 'Juan', 'Santos', 'EMP-001', 'ICT Division', '7:00-8:00'];
        foreach ($sample as $col => $v) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue($letter . '2', $v);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'employee_import_template.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ─── POST: Update Employee ──────────────────────────────────────
    public function updateEmployee(Request $request)
    {
        $db = $this->getDb();
        try {
            $status = $request->input('status');
            if ($status === 'OTHERS' && $request->input('custom_status'))
                $status = strtoupper(trim($request->input('custom_status')));
            $db->update("UPDATE employees SET `status`=?, official_time=? WHERE id=?", [$status, $request->input('official_time'), $request->input('id')]);
            $this->logAudit('Update Employee', 'Employee', "Updated #" . $request->input('id') . " — status: $status");
            return response()->json(['success' => true, 'new_status' => $status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Bulk Update Employees ────────────────────────────────
    public function bulkUpdateEmployees(Request $request)
    {
        $db = $this->getDb();
        try {
            $updates = json_decode($request->input('updates'), true);
            if (!is_array($updates)) {
                return response()->json(['success' => false, 'message' => 'Invalid data format']);
            }

            foreach ($updates as $update) {
                if (!isset($update['id']))
                    continue;
                $status = $update['status'] ?? 'ACTIVE';
                $time = $update['official_time'] ?? '';
                $db->update("UPDATE employees SET `status`=?, official_time=? WHERE id=?", [strtoupper($status), $time, $update['id']]);
            }
            $this->logAudit('Bulk Update Employees', 'Employee', "Bulk updated " . count($updates) . " employees' status/time");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Delete Employee ──────────────────────────────────────
    public function deleteEmployee(Request $request)
    {
        $db = $this->getDb();
        $empId = (int) $request->input('id');
        $empRow = $db->selectOne("SELECT last_name, first_name, emp_number FROM employees WHERE id = ?", [$empId]);
        $label = $empRow ? "{$empRow->last_name}, {$empRow->first_name} ({$empRow->emp_number})" : "ID:$empId";
        $db->delete("DELETE FROM employees WHERE id=?", [$empId]);
        $db->delete("DELETE FROM daily_attendance WHERE employee_id=?", [$empId]);
        $db->delete("DELETE FROM absence_reasons WHERE employee_id=?", [$empId]);
        try {
            $db->delete("DELETE FROM late_minutes WHERE employee_id=?", [$empId]);
        } catch (\Exception $e) {
        }
        try {
            $db->delete("DELETE FROM undertime_minutes WHERE employee_id=?", [$empId]);
        } catch (\Exception $e) {
        }
        $this->logAudit('Delete Employee', 'Employee', "Deleted: $label");
        return response()->json(['success' => true]);
    }

    // ─── POST: Clear All Employees ──────────────────────────────────
    public function clearAllEmployees(Request $request)
    {
        $db = $this->getDb();
        $count = $db->selectOne("SELECT COUNT(*) as cnt FROM employees")->cnt;
        $db->delete("DELETE FROM daily_attendance");
        $db->delete("DELETE FROM absence_reasons");
        try { $db->delete("DELETE FROM late_minutes"); } catch (\Exception $e) {}
        try { $db->delete("DELETE FROM undertime_minutes"); } catch (\Exception $e) {}
        $db->delete("DELETE FROM employees");
        $this->logAudit('Clear All Employees', 'Employee', "Deleted all $count employees and their records");
        return response()->json(['success' => true]);
    }

    // ─── POST: Save Late Minutes ────────────────────────────────────
    public function saveLateMinutes(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            $date = $request->input('late_date');
            $mins = (int) $request->input('late_mins');
            if (!$empId || !$date || $mins < 1)
                return response()->json(['success' => false, 'message' => 'Invalid data']);
            $ym = date('Y-m', strtotime($date));
            $day = (int) date('d', strtotime($date));
            $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'late')", [$empId, $ym, $day]);
            $db->statement("REPLACE INTO late_minutes (employee_id, `year_month`, `day`, minutes) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $mins]);
            $this->logAudit('Log Late', 'Attendance', "Emp#$empId late {$mins}min on $date");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Save Attendance Actions (Multi-save) ─────────────────
    public function deleteAbsentRecord(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            $date = $request->input('abs_date');
            if (!$empId || !$date) return response()->json(['success' => false, 'message' => 'Invalid parameters']);
            
            $ym = date('Y-m', strtotime($date));
            $day = (int) date('d', strtotime($date));
            
            // Delete the reasoning
            $db->delete("DELETE FROM absence_reasons WHERE employee_id = ? AND `year_month` = ? AND `day` = ?", [$empId, $ym, $day]);
            
            // Automatically mark as present
            $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'present')", [$empId, $ym, $day]);
            
            $this->logAudit('Delete Absence', 'Attendance', "Deleted absence for Emp#$empId on $date and marked as Present");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Save Employee Remarks ────────────────────────────────
    public function saveRemarks(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId  = (int) $request->input('emp_id');
            $remark = trim($request->input('remarks', ''));
            if (!$empId || !$remark) {
                return response()->json(['success' => false, 'message' => 'Employee ID and remark text are required.']);
            }
            // Save as the current active remark and mark NOT done
            $db->update(
                "UPDATE employees SET remarks = ?, remarks_done = 0 WHERE id = ?",
                [$remark, $empId]
            );
            // Push to history
            $db->insert(
                "INSERT INTO remarks_history (employee_id, remark, is_done) VALUES (?, ?, 0)",
                [$empId, $remark]
            );
            $this->logAudit('Save Remarks', 'Employee', "Remark added for Emp#$empId");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Mark Current Remark as Done ──────────────────────────
    public function markRemarkDone(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            if (!$empId) {
                return response()->json(['success' => false, 'message' => 'No employee ID']);
            }
            // Flag the employee's remark as done (do NOT clear the text)
            $db->update(
                "UPDATE employees SET remarks_done = 1 WHERE id = ?",
                [$empId]
            );
            // Mark the latest un-done history entry as done
            $db->update(
                "UPDATE remarks_history SET is_done = 1, done_at = NOW()
                 WHERE employee_id = ? AND is_done = 0
                 ORDER BY created_at DESC
                 LIMIT 1",
                [$empId]
            );
            $this->logAudit('Remark Done', 'Employee', "Remark marked done for Emp#$empId");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── GET: Get Remarks History ────────────────────────────────────
    public function getRemarksHistory(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            if (!$empId) {
                return response()->json(['success' => false, 'message' => 'No employee ID']);
            }
            $history = $db->select(
                "SELECT id, remark, is_done, done_at, created_at
                 FROM remarks_history
                 WHERE employee_id = ?
                 ORDER BY created_at DESC",
                [$empId]
            );
            return response()->json(['success' => true, 'history' => $history]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getIndividualSummary(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            $start = $request->input('start_date'); // YYYY-MM-DD
            $end = $request->input('end_date');     // YYYY-MM-DD

            if (!$empId || !$start || !$end) {
                return response()->json(['success' => false, 'message' => 'Missing parameters']);
            }

            // Fetch Daily Attendance
            $attRows = $db->select("
                SELECT `status`, `day`, `year_month` 
                FROM daily_attendance 
                WHERE employee_id = ? 
                AND STR_TO_DATE(CONCAT(`year_month`, '-', LPAD(`day`, 2, '0')), '%Y-%m-%d') BETWEEN ? AND ?
            ", [$empId, $start, $end]);

            $summary = [
                'present_days' => [],
                'absent_days' => [],
                'late_logs' => [],
                'ut_logs' => []
            ];

            foreach ($attRows as $row) {
                $status = strtolower($row->status);
                $day = (int) $row->day;
                $ym = $row->year_month;
                $fullDate = "$ym-" . str_pad($day, 2, '0', STR_PAD_LEFT);

                if ($status === 'present') {
                    $summary['present_days'][] = $day; // For preview we usually just need the day if it's single month, but for range we might need more.
                    // But wait, the preview modal currently shows 'Day X'. If it's a range, we should show full date.
                } elseif ($status === 'absent') {
                    $summary['absent_days'][] = $day;
                }
            }

            // Fetch Late Details
            $lateRows = $db->select("
                SELECT `day`, `year_month`, `minutes` 
                FROM late_minutes 
                WHERE employee_id = ? 
                AND STR_TO_DATE(CONCAT(`year_month`, '-', LPAD(`day`, 2, '0')), '%Y-%m-%d') BETWEEN ? AND ?
            ", [$empId, $start, $end]);
            foreach ($lateRows as $r) {
                $summary['late_logs'][] = ['day' => $r->day, 'mins' => $r->minutes, 'type' => 'Late'];
            }

            // Fetch Undertime Details
            $utRows = $db->select("
                SELECT `day`, `year_month`, `minutes` 
                FROM undertime_minutes 
                WHERE employee_id = ? 
                AND STR_TO_DATE(CONCAT(`year_month`, '-', LPAD(`day`, 2, '0')), '%Y-%m-%d') BETWEEN ? AND ?
            ", [$empId, $start, $end]);
            foreach ($utRows as $r) {
                $summary['ut_logs'][] = ['day' => $r->day, 'mins' => $r->minutes, 'type' => 'Undertime'];
            }

            // Fetch Absence Reasons
            $absRows = $db->select("
                SELECT `day`, `year_month`, `reason` 
                FROM absence_reasons 
                WHERE employee_id = ? 
                AND STR_TO_DATE(CONCAT(`year_month`, '-', LPAD(`day`, 2, '0')), '%Y-%m-%d') BETWEEN ? AND ?
            ", [$empId, $start, $end]);
            $absDetailed = [];
            foreach ($absRows as $r) {
                $absDetailed[] = ['day' => $r->day, 'type' => $r->reason];
            }
            $summary['absent_detailed'] = $absDetailed;

            return response()->json(['success' => true, 'data' => $summary]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveAttendanceActions(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            if (!$empId) return response()->json(['success' => false, 'message' => 'No employee ID']);

            // 1. Process Late Dates
            $lateDates = $request->input('late_dates', []);
            $lateMins  = $request->input('late_mins_arr', []);
            foreach ($lateDates as $idx => $date) {
                $mins = (int) ($lateMins[$idx] ?? 0);
                if ($date && $mins > 0) {
                    $ym = date('Y-m', strtotime($date));
                    $day = (int) date('d', strtotime($date));
                    $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'late')", [$empId, $ym, $day]);
                    $db->statement("REPLACE INTO late_minutes (employee_id, `year_month`, `day`, minutes) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $mins]);
                }
            }

            // 2. Process Undertime Dates
            $utDates = $request->input('ut_dates', []);
            $utMins  = $request->input('ut_mins_arr', []);
            foreach ($utDates as $idx => $date) {
                $mins = (int) ($utMins[$idx] ?? 0);
                if ($date && $mins > 0) {
                    $ym = date('Y-m', strtotime($date));
                    $day = (int) date('d', strtotime($date));
                    $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'undertime')", [$empId, $ym, $day]);
                    $db->statement("REPLACE INTO undertime_minutes (employee_id, `year_month`, `day`, minutes) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $mins]);
                }
            }

            // 3. Process Absence Dates
            $absDates = $request->input('abs_dates', []);
            $payType  = $request->input('pay_type');
            $reason   = $request->input('reason');
            $finalReason = $payType . ($reason ? " - $reason" : "");
            foreach ($absDates as $date) {
                if ($date) {
                    $ym = date('Y-m', strtotime($date));
                    $day = (int) date('d', strtotime($date));
                    $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'absent')", [$empId, $ym, $day]);
                    $db->statement("REPLACE INTO absence_reasons (employee_id, `year_month`, `day`, reason) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $finalReason]);
                }
            }

            // 4. Process "Mark All Present" if requested
            if ($request->has('mark_present_month')) {
                $year = (int) $request->input('mark_present_year');
                $month = (int) $request->input('mark_present_month');
                if ($year && $month) {
                    $this->markSingleEmpPresentAll($empId, $year, $month);
                }
            }

            $this->logAudit('Attendance Actions', 'Attendance', "Multi-save for Emp#$empId");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function markSingleEmpPresentAll($empId, $year, $month)
    {
        $db = $this->getDb();
        $ym = sprintf("%04d-%02d", $year, $month);
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $holidays = $this->getAllHolidays($year);

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $time = mktime(0, 0, 0, $month, $d, $year);
            $dateStr = date('Y-m-d', $time);
            $w = (int) date('N', $time); // 1 (Mon) to 7 (Sun)
            
            // Skip weekends (6=Sat, 7=Sun) and holidays
            if ($w >= 6 || isset($holidays[$dateStr])) continue;
            
            // "existing records are kept safe"
            $exists = $db->selectOne("SELECT 1 FROM daily_attendance WHERE employee_id=? AND `year_month`=? AND `day`=?", [$empId, $ym, $d]);
            if (!$exists) {
                $db->statement("INSERT INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'present')", [$empId, $ym, $d]);
            }
        }
    }

    // ─── POST: Save Undertime Minutes ───────────────────────────────
    public function saveUndertimeMinutes(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            $date = $request->input('ut_date');
            $mins = (int) $request->input('ut_mins');
            if (!$empId || !$date || $mins < 1)
                return response()->json(['success' => false, 'message' => 'Invalid data']);
            $ym = date('Y-m', strtotime($date));
            $day = (int) date('d', strtotime($date));
            $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'undertime')", [$empId, $ym, $day]);
            $db->statement("REPLACE INTO undertime_minutes (employee_id, `year_month`, `day`, minutes) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $mins]);
            $this->logAudit('Log Undertime', 'Attendance', "Emp#$empId undertime {$mins}min on $date");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteLateRecord(Request $request)
    {
        $db = $this->getDb();
        try {
            $id = (int) $request->input('id');
            $row = $db->selectOne("SELECT employee_id, `day`, `year_month`, minutes FROM late_minutes WHERE id = ?", [$id]);
            if ($row) {
                $db->delete("DELETE FROM late_minutes WHERE id = ?", [$id]);
                // Automatically mark as present instead of deleting the attendance record
                $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'present')", 
                    [$row->employee_id, $row->year_month, $row->day]);
                $this->logAudit('Delete Late', 'Attendance', "Deleted late record ID: $id and marked as Present (Emp#{$row->employee_id}, Date:{$row->year_month}-{$row->day})");
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteUndertimeRecord(Request $request)
    {
        $db = $this->getDb();
        try {
            $id = (int) $request->input('id');
            $row = $db->selectOne("SELECT employee_id, `day`, `year_month`, minutes FROM undertime_minutes WHERE id = ?", [$id]);
            if ($row) {
                $db->delete("DELETE FROM undertime_minutes WHERE id = ?", [$id]);
                // Automatically mark as present instead of deleting the attendance record
                $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'present')", 
                    [$row->employee_id, $row->year_month, $row->day]);
                $this->logAudit('Delete Undertime', 'Attendance', "Deleted undertime record ID: $id and marked as Present (Emp#{$row->employee_id}, Date:{$row->year_month}-{$row->day})");
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Mark Absent (from index) ─────────────────────────────
    public function saveAbsentFromIndex(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            $date = $request->input('abs_date');
            $payType = trim($request->input('pay_type', ''));
            $customReason = trim($request->input('reason', ''));

            if (!$empId || !$date || !$payType)
                return response()->json(['success' => false, 'message' => 'Invalid data']);

            $finalReason = $payType;
            if ($customReason) {
                $finalReason .= ' - ' . $customReason;
            }

            $ym = date('Y-m', strtotime($date));
            $day = (int) date('d', strtotime($date));
            $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'absent')", [$empId, $ym, $day]);
            $db->statement("REPLACE INTO absence_reasons (employee_id, `year_month`, `day`, reason) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $finalReason]);
            $this->logAudit('Mark Absent', 'Attendance', "Emp#$empId absent ($finalReason) on $date");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Mark Present All ─────────────────────────────────────
    public function markPresentAll(Request $request)
    {
        $db = $this->getDb();
        try {
            $empId = (int) $request->input('emp_id');
            $year = (int) $request->input('year');
            $month = (int) $request->input('month');
            if (!$empId || !$year || !$month)
                return response()->json(['success' => false, 'message' => 'Invalid data']);
            
            $ym = sprintf("%04d-%02d", $year, $month);
            $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
            $holidays = $this->getAllHolidays($year);
            $count = 0;
            
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $ts = mktime(0, 0, 0, $month, $d, $year);
                $dateStr = date('Y-m-d', $ts);
                $dw = (int) date('N', $ts);
                
                // Skip weekends (6=Sat, 7=Sun) and holidays
                if ($dw >= 6 || isset($holidays[$dateStr])) continue;
                
                // Keep existing records safe
                $exists = $db->selectOne("SELECT 1 FROM daily_attendance WHERE employee_id=? AND `year_month`=? AND `day`=?", [$empId, $ym, $d]);
                if (!$exists) {
                    $db->statement("INSERT INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'present')", [$empId, $ym, $d]);
                    $count++;
                }
            }
            $this->logAudit('Mark Present All', 'Attendance', "Emp#$empId marked present $count days in $ym");
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── GET: Absent Records ────────────────────────────────────────
    public function absent(Request $request)
    {
        $db = $this->getDb();
        $mode = $request->input('mode', 'monthly');
        $year = (int) ($request->input('year', date('Y')));
        $month = (int) ($request->input('month', date('m')));

        $selectedMonth = sprintf("%04d-%02d", $year, $month);
        $monthLabel = date('F Y', strtotime($selectedMonth . '-01'));

        if ($mode === 'yearly') {
            $monthLabel = "$year Report";
            $filter = "$year-%";
        } else {
            $filter = $selectedMonth;
        }

        $employees = $db->select("SELECT * FROM employees ORDER BY last_name ASC");
        $attendance = [];
        $rows = $db->select("SELECT employee_id, `day`, `status`, `year_month` FROM daily_attendance WHERE `year_month` LIKE ?", [$filter]);
        foreach ($rows as $row) {
            // For monthly, we just use day. For yearly, we might have duplicate days across months, 
            // but for the tally in the main table, we just need the counts.
            // For the modal details, we'll store them slightly differently or combine them.
            if ($mode === 'monthly') {
                $attendance[$row->employee_id][$row->day] = $row->status;
            } else {
                // For yearly aggregate sums in the view
                $attendance[$row->employee_id][$row->year_month . '-' . $row->day] = $row->status;
            }
        }

        // Full absence reason records keyed by employee_id for modal detail view
        $absenceDetails = [];
        $rRows = $db->select("SELECT employee_id, `day`, `year_month`, reason FROM absence_reasons WHERE `year_month` LIKE ?", [$filter]);
        foreach ($rRows as $row) {
            $absenceDetails[$row->employee_id][] = $row;
        }

        return view('admin.absent', compact('employees', 'attendance', 'absenceDetails', 'monthLabel', 'selectedMonth', 'year', 'month', 'mode'));
    }

    // ─── GET: Calendar / Attendance ─────────────────────────────────
    public function calendar(Request $request)
    {
        $db = $this->getDb();
        $mode = $request->input('mode', 'batch');
        $year = (int) ($request->input('year', date('Y')));
        $month = (int) ($request->input('month', date('m')));

        if ($request->has('start_date')) {
            $ts = strtotime($request->input('start_date'));
            $year = (int) date('Y', $ts);
            $month = (int) date('m', $ts);
        }

        $empId = $request->input('emp_id');

        if ($month < 1 || $month > 12)
            $month = (int) date('m');
        $ym = sprintf("%04d-%02d", $year, $month);
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $employees = $db->select("SELECT * FROM employees ORDER BY station, last_name, first_name ASC");

        $selectedEmp = null;
        if ($mode === 'individual' && $empId) {
            $selectedEmp = collect($employees)->firstWhere('id', $empId);
        }

        $attendance = [];
        $reasons = [];
        $lateMinutes = [];
        foreach ($db->select("SELECT * FROM daily_attendance WHERE `year_month` = ?", [$ym]) as $r)
            $attendance[$r->employee_id][$r->day] = $r->status;
        foreach ($db->select("SELECT * FROM absence_reasons WHERE `year_month` = ?", [$ym]) as $r)
            $reasons[$r->employee_id][$r->day] = $r->reason;
        try {
            foreach ($db->select("SELECT * FROM late_minutes WHERE `year_month` = ?", [$ym]) as $r)
                $lateMinutes[$r->employee_id][$r->day] = $r->minutes;
        } catch (\Exception $e) {
        }
        $holidays = [];
        foreach ($db->select("SELECT * FROM special_days WHERE date_str LIKE ?", ["$year-" . sprintf('%02d', $month) . "-%"]) as $r) {
            $holidays[(int) date('j', strtotime($r->date_str))] = $r->reason;
        }
        $stations = $db->select("SELECT DISTINCT station FROM employees WHERE station != '' ORDER BY station");

        // Load saved cell merges for this month
        $merges = [];
        try {
            foreach ($db->select("SELECT * FROM cell_merges WHERE `year_month` = ?", [$ym]) as $r) {
                $merges[$r->employee_id][$r->start_day] = [
                    'start_day' => (int) $r->start_day,
                    'end_day'   => (int) $r->end_day,
                    'label'     => $r->label,
                    'reason'    => $r->reason,
                ];
            }
        } catch (\Exception $e) {}

        return view('admin.calendar', compact('mode', 'year', 'month', 'ym', 'daysInMonth', 'employees', 'attendance', 'reasons', 'holidays', 'lateMinutes', 'stations', 'empId', 'selectedEmp', 'merges'));
    }

    // ─── POST: Save Cell Merge ─────────────────────────────────────
    public function saveCellMerge(Request $request)
    {
        $db       = $this->getDb();
        $empId    = (int) $request->input('emp_id');
        $ym       = $request->input('ym');
        $startDay = (int) $request->input('start_day');
        $endDay   = (int) $request->input('end_day');
        $label    = trim($request->input('label', ''));
        $reason   = trim($request->input('reason', ''));

        if ($empId && $ym && $startDay && $endDay && $label) {
            $db->statement(
                "REPLACE INTO cell_merges (employee_id, `year_month`, start_day, end_day, `label`, reason, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [$empId, $ym, $startDay, $endDay, $label, $reason]
            );
        }
        return response()->json(['success' => true]);
    }

    // ─── POST: Delete Cell Merge ─────────────────────────────────────
    public function deleteCellMerge(Request $request)
    {
        $db       = $this->getDb();
        $empId    = (int) $request->input('emp_id');
        $ym       = $request->input('ym');
        $startDay = (int) $request->input('start_day');

        if ($empId && $ym && $startDay) {
            $db->delete(
                "DELETE FROM cell_merges WHERE employee_id = ? AND `year_month` = ? AND start_day = ?",
                [$empId, $ym, $startDay]
            );
        }
        return response()->json(['success' => true]);
    }

    // ─── POST: Calendar Autosave (AJAX single cell) ─────────────────
    public function calendarAutosave(Request $request)
    {
        $db = $this->getDb();
        $empId = $request->input('emp_id');
        $day = $request->input('day');
        $val = trim(strtoupper($request->input('val', '')));
        $ym = $request->input('ym');
        $statusMap = ['/' => 'present', 'P' => 'present', '1' => 'absent', 'A' => 'absent', 'L' => 'late', 'U' => 'undertime', 'H' => 'halfday'];
        $status = $statusMap[$val] ?? '';
        if ($status) {
            $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $status]);
        } else {
            $db->delete("DELETE FROM daily_attendance WHERE employee_id = ? AND `year_month` = ? AND `day` = ?", [$empId, $ym, $day]);
        }
        return response('OK');
    }

    // ─── POST: Save Absence Reason (AJAX) ───────────────────────────
    public function saveAbsenceReason(Request $request)
    {
        $db = $this->getDb();
        $empId = (int) $request->input('emp_id');
        $day = (int) $request->input('day');
        $ym = $request->input('ym');
        $payType = trim($request->input('pay_type', ''));
        $customReason = trim($request->input('custom_reason', ''));

        $finalReason = $payType;
        if ($customReason) {
            $finalReason .= ' - ' . $customReason;
        }

        if ($empId && $day && $payType) {
            $db->statement("REPLACE INTO absence_reasons (employee_id, `year_month`, `day`, reason) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $finalReason]);
            $this->logAudit('Absence Reason', 'Attendance', "Emp#$empId day $day $ym: $finalReason");
        }
        return response('OK');
    }

    // ─── POST: Save Batch Sheet ─────────────────────────────────────
    public function saveBatch(Request $request)
    {
        $db = $this->getDb();
        $ym = $request->input('ym');
        $batchData = $request->input('attendance', []);
        $statusMap = ['/' => 'present', 'P' => 'present', '1' => 'absent', 'A' => 'absent', 'L' => 'late', 'U' => 'undertime', 'H' => 'halfday'];
        foreach ($batchData as $empId => $days) {
            foreach ($days as $day => $val) {
                $val = trim(strtoupper($val));
                $status = $statusMap[$val] ?? '';
                if ($status)
                    $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, ?)", [$empId, $ym, $day, $status]);
                elseif ($val === '')
                    $db->delete("DELETE FROM daily_attendance WHERE employee_id = ? AND `year_month` = ? AND `day` = ?", [$empId, $ym, $day]);
            }
        }
        $this->logAudit('Batch Update', 'Attendance', "Updated batch for $ym");
        return response()->json(['success' => true, 'message' => 'Batch saved']);
    }

    // ─── POST: Quick Mark Absent ────────────────────────────────────
    public function quickMarkAbsent(Request $request)
    {
        $db = $this->getDb();
        $empId = (int) $request->input('q_emp_id');
        $date = $request->input('absent_date');
        if ($empId && $date) {
            $ym = date('Y-m', strtotime($date));
            $day = (int) date('d', strtotime($date));
            $db->statement("REPLACE INTO daily_attendance (employee_id, `year_month`, `day`, `status`) VALUES (?, ?, ?, 'absent')", [$empId, $ym, $day]);
            $db->delete("DELETE FROM late_minutes WHERE employee_id=? AND `year_month`=? AND `day`=?", [$empId, $ym, $day]);
            $this->logAudit('Quick Absent', 'Attendance', "Marked ID:$empId absent $date");
        }
        return response()->json(['success' => true]);
    }

    // ─── POST: Add Holiday ──────────────────────────────────────────
    public function addHoliday(Request $request)
    {
        $db = $this->getDb();
        $date = trim($request->input('holiday_date'));
        $reason = trim($request->input('holiday_reason'));
        if ($date && $reason) {
            $db->statement("REPLACE INTO special_days (date_str, reason) VALUES (?, ?)", [$date, $reason]);
            $this->logAudit('Add Holiday', 'Attendance', "Declared '$reason' on $date");
        }
        return response()->json(['success' => true]);
    }

    // ─── POST: Clear All Month Data ─────────────────────────────────
    public function clearMonthData(Request $request)
    {
        $db = $this->getDb();
        $ym = $request->input('ym');
        $db->delete("DELETE FROM daily_attendance WHERE `year_month` = ?", [$ym]);
        $db->delete("DELETE FROM absence_reasons WHERE `year_month` = ?", [$ym]);
        try {
            $db->delete("DELETE FROM late_minutes WHERE `year_month` = ?", [$ym]);
        } catch (\Exception $e) {
        }
        try {
            $db->delete("DELETE FROM undertime_minutes WHERE `year_month` = ?", [$ym]);
        } catch (\Exception $e) {
        }
        $db->delete("DELETE FROM special_days WHERE date_str LIKE ?", ["$ym-%"]);
        $this->logAudit('Clear Month Data', 'Attendance', "Cleared all data for $ym");
        return response()->json(['success' => true]);
    }

    // ─── GET: Forms / Documents ─────────────────────────────────────
    public function forms(Request $request)
    {
        $db = $this->getDb();
        $search = trim($request->input('search', ''));
        $folderId = $request->input('folder_id'); // can be null for root

        $breadcrumbs = [];
        if ($folderId) {
            $curr = $db->selectOne("SELECT * FROM form_folders WHERE id = ?", [$folderId]);
            if ($curr) {
                $temp = $curr;
                while ($temp) {
                    array_unshift($breadcrumbs, $temp);
                    if ($temp->parent_id) {
                        $temp = $db->selectOne("SELECT * FROM form_folders WHERE id = ?", [$temp->parent_id]);
                    } else {
                        $temp = null;
                    }
                }
            }
        }

        if ($search) {
            // Searched files globally
            $forms = $db->select("SELECT * FROM forms WHERE title LIKE ? ORDER BY uploaded_at DESC", ["%$search%"]);
            $folders = [];
        } else {
            // Filtered by folder
            $folders = $db->select("SELECT * FROM form_folders WHERE " . ($folderId ? "parent_id = ?" : "parent_id IS NULL") . " ORDER BY name ASC", $folderId ? [$folderId] : []);
            $forms = $db->select("SELECT * FROM forms WHERE " . ($folderId ? "folder_id = ?" : "folder_id IS NULL") . " ORDER BY uploaded_at DESC", $folderId ? [$folderId] : []);
        }

        return view('admin.forms', compact('forms', 'folders', 'search', 'folderId', 'breadcrumbs'));
    }

    public function createFolder(Request $request)
    {
        $db = $this->getDb();
        $name = trim($request->input('name'));
        $parentId = $request->input('parent_id');

        if ($name) {
            $db->insert("INSERT INTO form_folders (name, parent_id) VALUES (?, ?)", [$name, $parentId]);
            $this->logAudit('Create Folder', 'Forms', "Created folder: $name");
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Folder name required']);
    }

    public function deleteFolder(Request $request)
    {
        $db = $this->getDb();
        $id = $request->input('id');
        $db->delete("DELETE FROM form_folders WHERE id = ?", [$id]);
        $this->logAudit('Delete Folder', 'Forms', "Deleted folder #$id");
        return response()->json(['success' => true]);
    }

    public function moveForm(Request $request)
    {
        $db = $this->getDb();
        $formId = $request->input('form_id');
        $folderId = $request->input('folder_id'); // can be null for root

        $db->update("UPDATE forms SET folder_id = ? WHERE id = ?", [$folderId, $formId]);
        $this->logAudit('Move Form', 'Forms', "Moved form #$formId to folder #$folderId");
        return response()->json(['success' => true]);
    }

    public function listFolders()
    {
        $db = $this->getDb();
        $folders = $db->select("SELECT id, name, parent_id FROM form_folders ORDER BY name ASC");
        return response()->json($folders);
    }

    // ─── POST: Upload Form (Add Link) ───────────────────────────────
    public function uploadForm(Request $request)
    {
        $db = $this->getDb();
        $title = trim($request->input('title', ''));
        $url = trim($request->input('link_url', ''));
        $folderId = $request->input('folder_id'); // Get destination folder

        if ($title && $url) {
            $db->insert("INSERT INTO forms (title, folder_id, filename, filesize, html_content, embed_url) VALUES (?, ?, 'LINK', 'LINK', '', ?)", [$title, $folderId, $url]);
            $this->logAudit('Add Form Link', 'Forms', "Added: $title");
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Title and URL required']);
    }

    // ─── POST: Delete Form ──────────────────────────────────────────
    public function deleteForm(Request $request)
    {
        $db = $this->getDb();
        $id = $request->input('id');
        $file = $db->selectOne("SELECT filename FROM forms WHERE id = ?", [$id]);
        if ($file && $file->filename && $file->filename !== 'LINK') {
            $path = public_path('uploads/' . $file->filename);
            if (file_exists($path))
                unlink($path);
        }
        $db->delete("DELETE FROM forms WHERE id = ?", [$id]);
        $this->logAudit('Delete Form', 'Forms', "Deleted form #$id");
        return response()->json(['success' => true]);
    }

    // ─── POST: Save Form Layout (AJAX) ──────────────────────────────
    public function saveFormLayout(Request $request)
    {
        $db = $this->getDb();
        $db->update("UPDATE forms SET html_content = ? WHERE id = ?", [$request->input('html_content'), $request->input('form_id')]);
        return response('saved');
    }

    // ─── GET: Download Form ─────────────────────────────────────────
    public function downloadForm(Request $request, $id)
    {
        $db = $this->getDb();
        $form = $db->selectOne("SELECT * FROM forms WHERE id = ?", [$id]);
        if (!$form)
            abort(404);
        if (!empty($form->html_content)) {
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $form->title) . '_Edited.doc';
            $html = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><title>" . htmlspecialchars($form->title) . "</title></head><body>" . $form->html_content . "</body></html>";
            return response($html)->header('Content-Type', 'application/vnd.ms-word')->header('Content-Disposition', "attachment; filename=\"$filename\"");
        }
        if ($form->filename && $form->filename !== 'LINK') {
            $path = public_path('uploads/' . $form->filename);
            if (file_exists($path))
                return response()->download($path);
        }
        abort(404);
    }

    // ─── GET: Audit Logs ────────────────────────────────────────────
    public function audit(Request $request)
    {
        $db = $this->getDb();
        $filterModule = trim($request->input('module', ''));
        $filterUser = trim($request->input('user', ''));
        $filterDate = trim($request->input('date', ''));

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];
        if ($filterModule) {
            $where[] = "module = ?";
            $params[] = $filterModule;
        }
        if ($filterUser) {
            $where[] = "username LIKE ?";
            $params[] = "%$filterUser%";
        }
        if ($filterDate) {
            $where[] = "DATE(created_at) = ?";
            $params[] = $filterDate;
        }

        $wc = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $totalCount = $db->selectOne("SELECT COUNT(*) as cnt FROM audit_logs $wc", $params)->cnt;
        $totalPages = max(1, ceil($totalCount / $perPage));
        $logs = $db->select("SELECT * FROM audit_logs $wc ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);

        $moduleList = array_map(fn($m) => $m->module, $db->select("SELECT DISTINCT module FROM audit_logs ORDER BY module"));

        // Stats for cards
        $todayCount = $db->selectOne("SELECT COUNT(*) as cnt FROM audit_logs WHERE DATE(created_at) = CURDATE()")->cnt;
        $weekCount = $db->selectOne("SELECT COUNT(*) as cnt FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->cnt;
        $activeUsersCount = $db->selectOne("SELECT COUNT(DISTINCT username) as cnt FROM audit_logs WHERE DATE(created_at) = CURDATE()")->cnt;
        $lastActivityLog = $db->selectOne("SELECT action FROM audit_logs ORDER BY created_at DESC LIMIT 1");
        $lastActivity = $lastActivityLog ? $lastActivityLog->action : 'No activity';

        return view('admin.audit', compact(
            'logs',
            'filterModule',
            'filterUser',
            'filterDate',
            'page',
            'totalPages',
            'totalCount',
            'moduleList',
            'todayCount',
            'weekCount',
            'activeUsersCount',
            'lastActivity'
        ));
    }

    // ─── GET: Profile ───────────────────────────────────────────────
    public function profile(Request $request)
    {
        $db = $this->getDb();
        $currentUser = $db->selectOne("SELECT * FROM users WHERE id = ?", [session('user_id')]);
        if (!$currentUser)
            $currentUser = (object) ['username' => 'Unknown', 'role' => 'Guest', 'photo' => null];
        $allUsers = session('role') === 'Admin' ? $db->select("SELECT * FROM users ORDER BY created_at DESC") : [];
        return view('admin.profile', compact('currentUser', 'allUsers'));
    }

    public function manual()
    {
        return view('admin.manual');
    }

    // ─── POST: Update Profile ───────────────────────────────────────
    public function updateProfile(Request $request)
    {
        $db = $this->getDb();
        $userId = session('user_id');
        if ($request->input('self_password')) {
            $db->update("UPDATE users SET password = ? WHERE id = ?", [password_hash($request->input('self_password'), PASSWORD_DEFAULT), $userId]);
        }
        if ($request->hasFile('self_photo')) {
            $photo = $request->file('self_photo');
            $filename = 'profile_' . $userId . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads'), $filename);
            $db->update("UPDATE users SET photo = ? WHERE id = ?", ["uploads/$filename", $userId]);
            session(['user_photo' => "uploads/$filename"]);
        }
        $this->logAudit('Update Profile', 'System', "User #$userId updated profile");
        return redirect()->route('admin.profile')->with('success', 'Profile updated');
    }

    // ─── POST: Add User ─────────────────────────────────────────────
    public function addUser(Request $request)
    {
        $db = $this->getDb();
        $username = trim($request->input('new_username'));
        $password = $request->input('new_password');
        $role = $request->input('new_role', 'Staff');
        if (!$username || !$password)
            return response()->json(['success' => false, 'message' => 'Username and password required']);
        try {
            $db->insert("INSERT INTO users (username, password, role) VALUES (?, ?, ?)", [$username, password_hash($password, PASSWORD_DEFAULT), $role]);
            $this->logAudit('Add User', 'System', "Created user: $username ($role)");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── POST: Delete User ──────────────────────────────────────────
    public function deleteUser(Request $request)
    {
        $db = $this->getDb();
        $id = (int) $request->input('user_id');
        if ($id == session('user_id'))
            return response()->json(['success' => false, 'message' => 'Cannot delete own account']);
        $db->delete("DELETE FROM users WHERE id = ?", [$id]);
        $this->logAudit('Delete User', 'System', "Deleted user #$id");
        return response()->json(['success' => true]);
    }
}

