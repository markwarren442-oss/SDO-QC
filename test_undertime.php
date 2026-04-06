<?php
// Quick debug: check undertime_minutes table
$pdo = new PDO('mysql:host=localhost;dbname=sdoqc', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$ym = date('Y-m'); // current month
echo "<h3>Checking undertime_minutes for: $ym</h3>";

// Show all records in undertime_minutes
$rows = $pdo->query("SELECT * FROM undertime_minutes ORDER BY year_month DESC, employee_id, day LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total rows in undertime_minutes: " . count($rows) . "</p>";
echo "<pre>";
print_r($rows);
echo "</pre>";

// Show distinct year_month values
$months = $pdo->query("SELECT DISTINCT year_month FROM undertime_minutes ORDER BY year_month DESC")->fetchAll(PDO::FETCH_COLUMN);
echo "<p>Distinct year_month values: " . implode(', ', $months) . "</p>";

// Show for current month
$stmt = $pdo->prepare("SELECT * FROM undertime_minutes WHERE year_month = ?");
$stmt->execute([$ym]);
$current = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h3>Records for $ym: " . count($current) . "</h3><pre>";
print_r($current);
echo "</pre>";
