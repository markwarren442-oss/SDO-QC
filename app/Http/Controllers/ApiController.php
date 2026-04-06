<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    private function getDb()
    {
        return DB::connection();
    }

    /**
     * Dashboard data (AJAX refresh) — from get_dashboard_data.php
     */
    public function dashboardData()
    {
        $db = $this->getDb();
        $currentYM = date('Y-m');
        $currentDay = (int) date('d');

        $stats = ['present' => 0, 'late' => 0, 'absent' => 0];
        $rows = $db->select("SELECT `status`, COUNT(*) as `count` FROM daily_attendance WHERE `year_month` = ? AND `day` = ? GROUP BY `status`", [$currentYM, $currentDay]);
        foreach ($rows as $row)
            $stats[strtolower($row->status)] = $row->count;

        $totalEmp = $db->selectOne("SELECT COUNT(*) as cnt FROM employees")->cnt;
        $logs = $db->select("SELECT * FROM login_logs ORDER BY login_time DESC LIMIT 10");

        return response()->json([
            'total' => $totalEmp,
            'stats' => $stats,
            'logs' => $logs,
            'timestamp' => date('h:i:s A')
        ]);
    }

    /**
     * Search employees (autocomplete) — from api/search_employees.php
     */
    public function searchEmployees(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2)
            return response()->json([]);

        $db = $this->getDb();
        $term = "%$q%";
        $results = $db->select(
            "SELECT id, first_name, last_name, station, emp_number FROM employees WHERE first_name LIKE ? OR last_name LIKE ? OR emp_number LIKE ? ORDER BY last_name ASC LIMIT 10",
            [$term, $term, $term]
        );
        return response()->json($results);
    }

    /**
     * AI Query (Gemini proxy) — from ai_handler.php
     */
    public function aiQuery(Request $request)
    {
        $apiKey = config('services.gemini.key', '');
        if (empty($apiKey)) {
            return response()->json(['reply' => '<b>Setup Error:</b> No Gemini API key configured. Set GEMINI_API_KEY in .env']);
        }

        $userMessage = $request->input('message', '');
        $context = $request->input('context', '');
        if (empty($userMessage))
            return response()->json(['reply' => 'Error: No message received.']);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . trim($apiKey);
        $data = ["contents" => [["parts" => [["text" => $context . "\n\nUser Question: " . $userMessage]]]]];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            $reply = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'AI returned no text.';
            return response()->json(['reply' => $reply]);
        }
        $errorData = json_decode($response, true);
        $msg = $errorData['error']['message'] ?? $response;
        return response()->json(['reply' => "<b>API Error ($httpCode):</b> $msg"]);
    }
}
