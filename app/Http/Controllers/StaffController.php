<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StaffController extends Controller
{
    private function getDb()
    {
        return DB::connection();
    }

    /**
     * Employee View / Personnel Directory
     */
    public function employeeview(Request $request)
    {
        $db = $this->getDb();

        $year = $request->has('year') ? (int) $request->get('year') : (int) date('Y');
        $month = $request->has('month') ? (int) $request->get('month') : (int) date('m');

        if ($month < 1 || $month > 12)
            $month = (int) date('m');
        if ($year < 2000)
            $year = (int) date('Y');

        $ym = sprintf("%04d-%02d", $year, $month);
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $currentMonth = sprintf("%04d-%02d", $year, $month);
        $prevDate = date('Y-m', strtotime("$currentMonth-01 -1 month"));
        $nextDate = date('Y-m', strtotime("$currentMonth-01 +1 month"));
        $monthLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));
        $monthVal = sprintf('%04d-%02d', $year, $month);

        // Employees
        $employees = $db->select("SELECT * FROM employees ORDER BY station, last_name, first_name ASC");

        // Attendance summary (status counts per employee)
        $attRows = $db->select(
            "SELECT employee_id, `status`, COUNT(*) as cnt FROM daily_attendance WHERE `year_month` = ? GROUP BY employee_id, `status`",
            [$ym]
        );
        $attendanceSummary = [];
        foreach ($attRows as $row) {
            $statusKey = strtolower($row->status);
            if (!isset($attendanceSummary[$row->employee_id]))
                $attendanceSummary[$row->employee_id] = ['present' => 0, 'absent' => 0, 'late' => 0, 'undertime' => 0, 'halfday' => 0];
            $attendanceSummary[$row->employee_id][$statusKey] = (int) $row->cnt;
        }

        // Late minutes totals & details
        $lateMins = [];
        $lateDetails = [];
        foreach ($db->select("SELECT employee_id, day, minutes FROM late_minutes WHERE `year_month` = ? ORDER BY day ASC", [$ym]) as $r) {
            $empId = $r->employee_id;
            if (!isset($lateMins[$empId])) {
                $lateMins[$empId] = 0;
                $lateDetails[$empId] = [];
            }
            $lateMins[$empId] += (int) $r->minutes;
            $lateDetails[$empId][] = ['day' => $r->day, 'mins' => $r->minutes, 'type' => 'Late'];
        }

        // Undertime minutes totals & details
        $undertimeMins = [];
        $utDetails = [];
        try {
            foreach ($db->select("SELECT employee_id, day, minutes FROM undertime_minutes WHERE `year_month` = ? ORDER BY day ASC", [$ym]) as $r) {
                $empId = $r->employee_id;
                if (!isset($undertimeMins[$empId])) {
                    $undertimeMins[$empId] = 0;
                    $utDetails[$empId] = [];
                }
                $undertimeMins[$empId] += (int) $r->minutes;
                $utDetails[$empId][] = ['day' => $r->day, 'mins' => $r->minutes, 'type' => 'Undertime'];
            }
        } catch (\Exception $e) {
        }

        // Absence reasons – with/without pay
        $absReasons = [];
        $absDetails = [];
        foreach ($db->select("SELECT employee_id, day, reason FROM absence_reasons WHERE `year_month` = ?", [$ym]) as $r) {
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

        // Attendance (day-level, for holidays)
        $attendance = [];
        $rows = $db->select("SELECT * FROM daily_attendance WHERE `year_month` = ?", [$ym]);
        foreach ($rows as $row) {
            $attendance[$row->employee_id][$row->day] = $row->status;
        }

        // Absence reasons (day-level)
        $reasons = [];
        $rRows = $db->select("SELECT * FROM absence_reasons WHERE `year_month` = ?", [$ym]);
        foreach ($rRows as $row) {
            $reasons[$row->employee_id][$row->day] = $row->reason;
        }

        // Holidays
        $holidays = [];
        try {
            $hRows = $db->select("SELECT * FROM special_days WHERE date_str LIKE ?", ["$year-" . sprintf('%02d', $month) . "-%"]);
            foreach ($hRows as $row) {
                $d = (int) date('j', strtotime($row->date_str));
                $holidays[$d] = $row->reason;
            }
        } catch (\Exception $e) {
        }

        // Unique Stations for Filter
        $stations = [];
        $stRows = $db->select("SELECT DISTINCT station FROM employees WHERE station IS NOT NULL AND station != '' ORDER BY station ASC");
        foreach ($stRows as $r) {
            $stations[] = $r->station;
        }

        // Stats
        $totalEmp = $db->selectOne("SELECT COUNT(*) as cnt FROM employees")->cnt;
        $presentToday = $db->selectOne("SELECT COUNT(*) as cnt FROM daily_attendance WHERE `year_month` = ? AND `day` = ? AND `status` = 'present'", [date('Y-m'), (int) date('d')])->cnt;

        return view('staff.employeeview', compact(
            'employees',
            'attendance',
            'attendanceSummary',
            'lateMins',
            'lateDetails',
            'undertimeMins',
            'utDetails',
            'absReasons',
            'absDetails',
            'reasons',
            'holidays',
            'year',
            'month',
            'ym',
            'monthLabel',
            'monthVal',
            'daysInMonth',
            'currentMonth',
            'prevDate',
            'nextDate',
            'totalEmp',
            'presentToday',
            'stations'
        ));
    }
    public function updateProfile(Request $request)
    {
        $db = $this->getDb();
        $userId = session('user_id');
        
        if ($request->filled('self_password')) {
            $db->update("UPDATE users SET password = ? WHERE id = ?", [password_hash($request->input('self_password'), PASSWORD_DEFAULT), $userId]);
        }
        
        if ($request->filled('cropped_photo')) {
            $base64_image = $request->input('cropped_photo');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $type)) {
                $base64_image = substr($base64_image, strpos($base64_image, ',') + 1);
                $type = strtolower($type[1]);
                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) { $type = 'jpg'; }

                $base64_image = base64_decode(str_replace(' ', '+', $base64_image));
                if ($base64_image !== false) {
                    $filename = 'profile_' . $userId . '_' . time() . '.' . $type;
                    file_put_contents(public_path('uploads') . '/' . $filename, $base64_image);
                    $db->update("UPDATE users SET photo = ? WHERE id = ?", ["uploads/$filename", $userId]);
                    session(['user_photo' => "uploads/$filename"]);
                }
            }
        } elseif ($request->hasFile('self_photo')) {
            $photo = $request->file('self_photo');
            $filename = 'profile_' . $userId . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads'), $filename);
            $db->update("UPDATE users SET photo = ? WHERE id = ?", ["uploads/$filename", $userId]);
            session(['user_photo' => "uploads/$filename"]);
        }
        
        return redirect()->route('staff.employeeview')->with('success', 'Profile updated successfully.');
    }
}
