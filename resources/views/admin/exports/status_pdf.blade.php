<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        @page {
            margin: 0.5in;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #0f172a;
            background: #fff;
        }
        .page-break {
            page-break-after: always;
        }
        .page-break:last-child {
            page-break-after: avoid;
        }
        
        /* Header Table */
        .header-table {
            width: 100%;
            margin-bottom: 5px;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-logo {
            width: 90px;
            text-align: right;
            padding-right: 15px;
        }
        .header-logo img {
            height: 65px;
        }
        .header-text {
            text-align: left;
            font-size: 10pt;
            line-height: 1.2;
            color: #334155;
            padding-left: 10px;
        }
        .header-text strong {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
        }
        
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-top: 20px;
        }
        .meta-table td {
            font-size: 10pt;
            font-weight: bold;
            color: #334155;
        }
        .meta-center {
            text-align: center;
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .meta-right {
            text-align: right;
            font-size: 9.5pt;
        }

        /* Status Table */
        .stat-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .stat-table th, .stat-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 6px;
            text-align: left;
            vertical-align: top;
        }
        .stat-table th {
            font-weight: bold;
            background-color: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 8.5pt;
            letter-spacing: 0.5px;
        }
        .col-no { width: 5%; text-align: center; }
        .col-name { width: 30%; font-weight: 600; color: #1e293b; }
        .col-station { width: 20%; }
        .col-total { width: 10%; text-align: center; font-weight: bold; }
        .col-details { width: 35%; color: #475569; font-size: 8.5pt; line-height: 1.4; }

        .detail-item {
            margin-bottom: 3px;
        }
        .detail-date {
            font-weight: 600;
            color: #334155;
            display: inline-block;
            width: 80px;
        }
        
        /* Colors for totals */
        .tot-present { color: #16a34a; }
        .tot-absent { color: #dc2626; }
        .tot-late { color: #ea580c; }
        .tot-undertime { color: #8b5cf6; }

        /* Copy icon button */
        .copy-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            border: none;
            background: rgba(59,130,246,0.10);
            color: #3b82f6;
            border-radius: 3px;
            cursor: pointer;
            font-size: 8px;
            line-height: 1;
            padding: 0;
            margin-left: 3px;
            vertical-align: middle;
            opacity: 0.5;
            transition: opacity 0.15s, background 0.15s;
        }
        .copy-btn:hover {
            opacity: 1;
            background: rgba(59,130,246,0.22);
        }
        .copy-btn.copied {
            background: rgba(16,185,129,0.18);
            color: #059669;
        }
        @media print {
            .copy-btn { display: none !important; }
        }

        /* Signatories */
        .sig-container {
            width: 100%;
            margin-top: 40px;
        }
        .sig-cert {
            width: 100%;
            border: 1px solid #94a3b8;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 25px;
        }
        .sig-cert td {
            border: 1px solid #94a3b8;
            padding: 6px;
            font-weight: bold;
            color: #334155;
        }
        .sig-cert-text {
            text-align: center;
            font-style: italic;
            font-weight: normal !important;
            color: #475569 !important;
            padding: 15px !important;
        }
        
        .sig-names {
            width: 100%;
            table-layout: fixed;
            margin-top: 15px;
            text-align: center;
        }
        .sig-names td {
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 10pt;
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #0f172a;
        }
        .sig-pos {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    @php
        $hasData = false;
        foreach($byStation as $stn => $emps) {
            if(count($emps) > 0) $hasData = true;
        }
        
        // Define color class and label based on status Filter
        $totClass = 'tot-present';
        $totLabel = 'Total Instances';
        if ($statusFilter === 'present') { $totClass = 'tot-present'; $totLabel = 'Days Present'; }
        elseif ($statusFilter === 'absent' || $statusFilter === 'halfday') { $totClass = 'tot-absent'; $totLabel = 'Days Absent'; }
        elseif ($statusFilter === 'late') { $totClass = 'tot-late'; $totLabel = 'Total Lates'; }
        elseif ($statusFilter === 'undertime') { $totClass = 'tot-undertime'; $totLabel = 'Total Undertimes'; }
    @endphp

    @if(!$hasData)
        <div class="page-break">
            <!-- Header -->
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if($logoBase64)<img src="{{ $logoBase64 }}" alt="Logo">@endif
                    </td>
                    <td class="header-text">
                        Republic of the Philippines<br>
                        Department of Education<br>
                        National Capital Region<br>
                        <strong>SCHOOLS DIVISION OFFICE, QUEZON CITY</strong>
                    </td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td style="width: 30%;">SCOPE:<br>ALL STATIONS</td>
                    <td class="meta-center" style="width: 40%;">{{ $reportTitle }}</td>
                    <td class="meta-right" style="width: 30%;">PERIOD COVERED:<br>{{ $periodCovered }}</td>
                </tr>
            </table>
            
            <table class="stat-table">
                <thead>
                    <tr>
                        <th class="col-no">No.</th>
                        <th class="col-name">Name of Employee</th>
                        <th class="col-station">Station</th>
                        <th class="col-total">{{ $totLabel }}</th>
                        <th class="col-details">Details / Specific Dates</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" style="text-align:center; padding:30px; font-style:italic; color:#64748b;">No records found for this category/period.</td></tr>
                </tbody>
            </table>
        </div>
    @else
        <div class="page-break">
            <!-- Header -->
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if($logoBase64)<img src="{{ $logoBase64 }}" alt="Logo">@endif
                    </td>
                    <td class="header-text">
                        Republic of the Philippines<br>
                        Department of Education<br>
                        National Capital Region<br>
                        <strong>SCHOOLS DIVISION OFFICE, QUEZON CITY</strong>
                    </td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td style="width: 30%;">SCOPE:<br>ALL STATIONS</td>
                    <td class="meta-center" style="width: 40%;">{{ $reportTitle }}</td>
                    <td class="meta-right" style="width: 30%;">PERIOD COVERED:<br>{{ $periodCovered }}</td>
                </tr>
            </table>
            
            <table class="stat-table">
                <thead>
                    <tr>
                        <th class="col-no">No.</th>
                        <th class="col-name">Name of Employee</th>
                        <th class="col-station">Station</th>
                        <th class="col-total">{{ $totLabel }}</th>
                        @if($statusFilter !== 'present')
                        <th class="col-details">Details / Specific Dates</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $seq = 1; @endphp
                    @foreach($byStation as $stn => $stnEmps)
                        @foreach($stnEmps as $emp)
                            @php
                                $eid = $emp->id;
                                $count = 0;
                                $details = [];
                                
                                for($d = 1; $d <= $daysInMonth; $d++) {
                                    if (isset($attendance[$eid][$d])) {
                                        $s = $attendance[$eid][$d];
                                        // Match the status filter closely
                                        $match = false;
                                        if ($statusFilter === 'all') $match = true;
                                        elseif ($statusFilter === 'present' && in_array($s, ['present'])) $match = true;
                                        elseif ($statusFilter === 'absent' && in_array($s, ['absent', 'halfday'])) $match = true;
                                        elseif ($statusFilter === 'late' && $s === 'late') $match = true;
                                        elseif ($statusFilter === 'undertime' && $s === 'undertime') $match = true;
                                        
                                        if ($match) {
                                            if ($statusFilter === 'absent' && $s === 'halfday') {
                                                $count += 0.5;
                                            } else {
                                                $count++;
                                            }
                                            
                                            $dateStr = date('M d', mktime(0,0,0, $month, $d, $year));
                                            $rsn = $reasons[$eid][$d] ?? '';
                                            if ($statusFilter !== 'present') {
                                                $details[] = ['date' => $dateStr, 'note' => $rsn];
                                            }
                                        }
                                    }
                                }
                            @endphp
                            
                            @if($count > 0)
                            <tr>
                                <td class="col-no">{{ $seq++ }}</td>
                                <td class="col-name">{{ strtoupper($emp->last_name . ', ' . $emp->first_name) }}</td>
                                <td class="col-station">{{ $stn }}</td>
                                <td class="col-total {{ $totClass }}">
                                    {{ $count }}
                                    @if($statusFilter !== 'present' && count($details) > 0)
                                        @php $allDatesStr = implode(', ', array_map(fn($det) => $det['date'], $details)); @endphp
                                        <button class="copy-btn" title="Copy all dates" onclick="copyDate(this, '{{ $allDatesStr }}')">📋</button>
                                    @endif
                                </td>
                                @if($statusFilter !== 'present')
                                <td class="col-details">
                                    @foreach($details as $det)
                                        <div class="detail-item">
                                            <span class="detail-date">{{ $det['date'] }}</span>
                                            <button class="copy-btn" title="Copy: {{ $det['date'] }}" onclick="copyDate(this, '{{ $det['date'] }}')">📋</button>
                                            @if($det['note'])<span style="color:#64748b;">- {{ $det['note'] }}</span>@endif
                                        </div>
                                    @endforeach
                                </td>
                                @endif
                            </tr>
                            @endif
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <!-- Signatory Footer -->
            <div class="sig-container">
                <table class="sig-cert">
                    <tr>
                        <td width="30%">Prepared By:</td>
                        <td width="40%">Certification:</td>
                        <td width="30%">Verified Correct:</td>
                    </tr>
                    <tr>
                        <td style="border-top:none;"></td>
                        <td class="sig-cert-text" style="border-top:none;">I hereby certify that the information above is true and correct based on existing records.</td>
                        <td style="border-top:none;"></td>
                    </tr>
                </table>

                <table class="sig-names">
                    <tr>
                        <td width="33%">
                            <div class="sig-name">{{ strtoupper($request->input('prep_name', 'CHRISTINE JOY C. MAAPOY')) }}</div>
                            <div class="sig-pos">{{ $request->input('prep_pos', 'Administrative Assistant III') }}</div>
                            @if($request->input('prep_pos2'))<div class="sig-pos">{{ $request->input('prep_pos2') }}</div>@endif
                            @if($request->input('prep_pos3'))<div class="sig-pos">{{ $request->input('prep_pos3') }}</div>@endif
                        </td>
                        <td width="34%">
                            <div class="sig-name">{{ strtoupper($request->input('cert_name', 'MICHELLE A. MAL-IN')) }}</div>
                            <div class="sig-pos">{{ $request->input('cert_pos', 'HRMO II') }}</div>
                            @if($request->input('cert_pos2'))<div class="sig-pos">{{ $request->input('cert_pos2') }}</div>@endif
                            @if($request->input('cert_pos3'))<div class="sig-pos">{{ $request->input('cert_pos3') }}</div>@endif
                        </td>
                        <td width="33%">
                            <div class="sig-name">{{ strtoupper($request->input('ver_name', 'ROSELYN B. SENCIL')) }}</div>
                            <div class="sig-pos">{{ $request->input('ver_pos', 'HRMO V') }}</div>
                            @if($request->input('ver_pos2'))<div class="sig-pos">{{ $request->input('ver_pos2') }}</div>@endif
                            @if($request->input('ver_pos3'))<div class="sig-pos">{{ $request->input('ver_pos3') }}</div>@endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endif

<script>
function copyDate(btn, text) {
    if (!text) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showCopied(btn);
        });
    } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showCopied(btn);
    }
}
function showCopied(btn) {
    btn.classList.add('copied');
    var orig = btn.innerHTML;
    btn.innerHTML = '✓';
    setTimeout(function() {
        btn.classList.remove('copied');
        btn.innerHTML = orig;
    }, 1200);
}
</script>
</body>
</html>
