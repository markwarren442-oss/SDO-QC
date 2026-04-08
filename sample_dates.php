<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('Report On Attendance, Absences and Tardiness 2025 - 2026 (1).xlsx');
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, false);

$samples = [];
foreach (array_slice($rows, 1) as $row) {
    if (trim($row[10]) || trim($row[14]) || trim($row[17])) {
        $samples[] = [
            'Name' => trim($row[3]) . ', ' . trim($row[4]),
            'Remarks' => trim($row[10]),
            'Tardy Dates' => trim($row[14]),
            'UT Dates' => trim($row[17])
        ];
    }
}
print_r(array_slice($samples, 0, 10));
