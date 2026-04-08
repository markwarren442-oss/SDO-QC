<?php
$dates = [
    '03/12,16/2026',
    '3/25/2026',
    "3/5,6/2026 - CTO w/pay; 3/30,31/2026 - WL w/pay",
    "03/02/2026 - WL w/pay\n03/30,31/2026 - SPL w/pay"
];

foreach ($dates as $dateStr) {
    echo "--- Testing: " . str_replace("\n", "\\n", $dateStr) . " ---\n";
    preg_match_all('/(\d{1,2})\/([\d,\-]+)\/(\d{4})(?:\s*-\s*([^;\n]+))?/', $dateStr, $matches, PREG_SET_ORDER);
    print_r($matches);
}
