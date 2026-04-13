<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        @page {
            margin: 0.3in;
            size: legal landscape;
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
            width: 100px;
            text-align: right;
            padding-right: 15px;
        }
        .header-logo img {
            height: 70px;
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
        .header-sp {
            width: 120px;
            text-align: right;
            font-size: 9pt;
            font-weight: bold;
            vertical-align: bottom !important;
            padding-bottom: 5px;
            color: #64748b;
        }
        
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
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
        }

        /* Attendance Table */
        .att-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            table-layout: fixed;
        }
        .att-table th, .att-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 2px;
            text-align: center;
        }
        .att-table th {
            font-weight: bold;
            background-color: #f8fafc;
            color: #475569;
        }
        .col-no { width: 3.5%; }
        .col-name { width: 19%; text-align: left !important; padding-left: 6px !important; font-weight: 600; }
        .col-day { width: 2.2%; font-size: 7.5pt; }
        .col-abs { width: 4.5%; font-size: 8pt; }
        
        .procedures-sidebar {
            float: right;
            width: 130px;
            font-size: 6.5pt;
            color: #475569;
            line-height: 1.25;
            padding: 8px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            margin-bottom: 15px;
            margin-left: 10px;
        }
        
        .att-table-wrap {
            width: calc(100% - 150px);
            float: left;
        }

        .sym-L { color: #ea580c; font-weight: bold; }
        .sym-U { color: #b45309; font-weight: bold; }
        .sym-A { color: #dc2626; font-weight: bold; }
        .bg-weekend { background-color: #f1f5f9; color: #94a3b8; }
        .bg-holiday { background-color: #fee2e2; color: #be123c; font-weight: bold; }
        .bg-out { background-color: #e2e8f0; }

        /* Copy icon button */
        .copy-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 13px;
            height: 13px;
            border: none;
            background: rgba(59,130,246,0.10);
            color: #3b82f6;
            border-radius: 3px;
            cursor: pointer;
            font-size: 7px;
            line-height: 1;
            padding: 0;
            margin-left: 1px;
            vertical-align: middle;
            opacity: 0.5;
            transition: opacity 0.15s, background 0.15s;
            position: relative;
        }
        .copy-btn:hover {
            opacity: 1;
            background: rgba(59,130,246,0.22);
        }
        .copy-btn.copied {
            background: rgba(16,185,129,0.18);
            color: #059669;
        }
        /* Summary cell copy btn (With Pay / Without Pay) */
        .copy-summary-btn {
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
            font-size: 7.5px;
            line-height: 1;
            padding: 0;
            margin-left: 2px;
            vertical-align: middle;
            opacity: 0.6;
            transition: opacity 0.15s, background 0.15s;
        }
        .copy-summary-btn:hover {
            opacity: 1;
            background: rgba(59,130,246,0.22);
        }
        .copy-summary-btn.copied {
            background: rgba(16,185,129,0.18);
            color: #059669;
        }
        @media print {
            .copy-btn, .copy-summary-btn { display: none !important; }
        }

        /* Signatories */
        .sig-container {
            width: 100%;
            margin-top: 20px;
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
            padding: 0 15px;
        }
        .sig-name {
            display: inline-block;
            width: 250px;
            font-weight: bold;
            font-size: 10pt;
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #0f172a;
            text-align: center;
        }
        .sig-pos {
            display: inline-block;
            width: 250px;
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
            text-align: center;
        }
        .sig-names td {
            vertical-align: top;
            padding: 0 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    @forelse($byStation as $stn => $stnEmps)
        <div class="page-break">
            <!-- Header -->
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" alt="Logo">
                        @endif
                    </td>
                    <td class="header-text">
                        Republic of the Philippines<br>
                        Department of Education<br>
                        National Capital Region<br>
                        <strong>SCHOOLS DIVISION OFFICE, QUEZON CITY</strong>
                    </td>
                    <td class="header-sp">S.P. FORM A</td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td style="width: 30%;">NAME OF SCHOOL:<br>DIVISION OFFICE - STATION {{ $stn }}</td>
                    <td class="meta-center" style="width: 40%;">{{ $reportTitle }}</td>
                    <td class="meta-right" style="width: 30%;">PERIOD COVERED:<br>{{ $periodCovered }}</td>
                </tr>
            </table>

            <!-- Floating Procedures (Page 1 Only) -->
            <div class="procedures-sidebar">
                <div style="font-weight: bold; margin-bottom: 5px; text-decoration: underline; color: #1e293b;">PROCEDURES IN ACCOMPLISHING ATTENDANCE REPORT:</div>
                1. Cross out unnecessary dates e.g.<br>
                2. All entries shall be based on the individual DTR or Time Card;<br>
                3. Indicate the presence of the employee by a (/) mark in the date column;<br>
                4. Enter absences in terms of days in the date column either 1/2 or 1 whole day. Leave absence shall be attached to the attendance report form;<br>
                5. Enter tardiness in the date column in terms of minutes, halfdays or under times. Enclose the late/halfday/undertime entries by parenthesis; etc.
            </div>

            <div class="att-table-wrap">
            <!-- Table -->
            <table class="att-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="col-no">No.</th>
                        <th rowspan="2" class="col-name">Name of Employee</th>
                        @for($d = 1; $d <= 31; $d++)
                            <th class="col-day">{{ $d }}</th>
                        @endfor
                        <th colspan="2" class="col-abs">Absences</th>
                    </tr>
                    <tr>
                        @for($d = 1; $d <= 31; $d++)
                            @php
                                $inMonth = ($d <= $daysInMonth);
                                $ts = $inMonth ? mktime(0, 0, 0, $month, $d, $year) : null;
                                $dow = $ts ? (int) date('w', $ts) : -1;
                                $class = '';
                                if (!$inMonth) $class = 'bg-out';
                                elseif (isset($holidays[$d])) $class = 'bg-holiday';
                                elseif ($dow === 0 || $dow === 6) $class = 'bg-weekend';
                            @endphp
                            <th class="col-day {{ $class }}">
                                {{ $inMonth ? strtoupper(substr(date('D', $ts), 0, 2)) : '' }}
                            </th>
                        @endfor
                        <th class="col-abs" style="border-right: 1px dotted #cbd5e1;">With<br>Pay</th>
                        <th class="col-abs">W/out<br>Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @php $empCount = 1; $rowCount = count($stnEmps); @endphp
                    @foreach($stnEmps as $index => $emp)
                        @php
                            $eid = $emp->id;
                            $absPay = 0;
                            $absNoPay = 0;
                            $absPayDates = [];
                            $absNoPayDates = [];
                        @endphp
                        <tr>
                            <td>{{ $empCount++ }}</td>
                            <td class="col-name">{{ strtoupper($emp->last_name . ', ' . $emp->first_name) }}</td>
                            @for($d = 1; $d <= 31; $d++)
                                @php
                                    $inMonth = ($d <= $daysInMonth);
                                    $ts = $inMonth ? mktime(0, 0, 0, $month, $d, $year) : null;
                                    $dow = $ts ? (int) date('w', $ts) : -1;
                                    $class = '';
                                    $sym = '';
                                    $showCopy = false;
                                    $copyDate = $inMonth ? date('M d, Y', $ts) : '';
                                    if (!$inMonth) {
                                        $class = 'bg-out';
                                    } elseif (isset($holidays[$d])) {
                                        $class = 'bg-holiday';
                                        $sym = 'HOL';
                                    } elseif ($dow === 0) {
                                        $class = 'bg-weekend';
                                        $sym = 'SUN';
                                    } elseif ($dow === 6) {
                                        $class = 'bg-weekend';
                                        $sym = 'SAT';
                                    } else {
                                        if (isset($attendance[$eid][$d])) {
                                            $s = $attendance[$eid][$d];
                                            if ($s === 'present') { $sym = '/'; }
                                            elseif ($s === 'late') { $sym = 'L'; $class = 'sym-L'; $showCopy = true; }
                                            elseif ($s === 'undertime') { $sym = 'U'; $class = 'sym-U'; $showCopy = true; }
                                            elseif ($s === 'halfday') {
                                                $sym = '1/2';
                                                $rsn = $reasons[$eid][$d] ?? '';
                                                if (stripos($rsn, 'without pay') !== false) {
                                                    $absNoPay += 0.5;
                                                    $absNoPayDates[] = $copyDate;
                                                } else {
                                                    $absPay += 0.5;
                                                    $absPayDates[] = $copyDate;
                                                }
                                                $class = 'sym-A bg-holiday';
                                                $showCopy = true;
                                            }
                                            elseif ($s === 'absent') {
                                                $sym = '1';
                                                $rsn = $reasons[$eid][$d] ?? '';
                                                if (stripos($rsn, 'without pay') !== false) {
                                                    $absNoPay++;
                                                    $absNoPayDates[] = $copyDate;
                                                } else {
                                                    $absPay++;
                                                    $absPayDates[] = $copyDate;
                                                }
                                                $class = 'sym-A bg-holiday';
                                                $showCopy = true;
                                            }
                                        } else {
                                            // No record found, default to '/'
                                            $sym = '/';
                                        }
                                    }
                                @endphp
                                <td class="{{ $class }}">
                                    <strong>{{ $sym }}</strong>
                                    @if($showCopy)
                                        <button class="copy-btn" title="Copy date: {{ $copyDate }}" onclick="copyDate(this, '{{ $copyDate }}')">📋</button>
                                    @endif
                                </td>
                            @endfor
                            <td style="border-right: 1px dotted #cbd5e1;">
                                <strong>{{ $absPay > 0 ? $absPay : '' }}</strong>
                                @if($absPay > 0)
                                    <button class="copy-summary-btn" title="Copy With Pay dates" onclick="copyDate(this, '{{ implode(', ', $absPayDates) }}')">📋</button>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $absNoPay > 0 ? $absNoPay : '' }}</strong>
                                @if($absNoPay > 0)
                                    <button class="copy-summary-btn" title="Copy Without Pay dates" onclick="copyDate(this, '{{ implode(', ', $absNoPayDates) }}')">📋</button>
                                @endif
                            </td>
                            
                            @if($index === 0)
                                {{-- Instructions column removed to prevent overflow --}}
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>


            <!-- Signatory Footer -->
            <div style="clear: both;"></div>
            <div class="sig-container">
                <table class="sig-cert">
                    <tr>
                        <td width="30%">Prepared By:</td>
                        <td width="40%">Certification:</td>
                        <td width="30%">Verified Correct:</td>
                    </tr>
                    <tr>
                        <td style="border-top:none;"></td>
                        <td class="sig-cert-text" style="border-top:none;">This is to certify that personnel listed in this report have rendered services</td>
                        <td style="border-top:none;"></td>
                    </tr>
                </table>

                <table class="sig-names">
                    <tr>
                        <td width="33%">
                            <div class="sig-name">{{ strtoupper($request->input('prep_name', 'CHRISTINE JOY C. MAAPOY')) }}</div><br>
                            <div class="sig-pos">{{ $request->input('prep_pos', 'Administrative Assistant III') }}</div>
                            @if($request->input('prep_pos2'))<br><div class="sig-pos">{{ $request->input('prep_pos2') }}</div>@endif
                            @if($request->input('prep_pos3'))<br><div class="sig-pos">{{ $request->input('prep_pos3') }}</div>@endif
                        </td>
                        <td width="34%">
                            <div class="sig-name">{{ strtoupper($request->input('cert_name', 'MICHELLE A. MAL-IN')) }}</div><br>
                            <div class="sig-pos">{{ $request->input('cert_pos', 'HRMO II') }}</div>
                            @if($request->input('cert_pos2'))<br><div class="sig-pos">{{ $request->input('cert_pos2') }}</div>@endif
                            @if($request->input('cert_pos3'))<br><div class="sig-pos">{{ $request->input('cert_pos3') }}</div>@endif
                        </td>
                        <td width="33%">
                            <div class="sig-name">{{ strtoupper($request->input('ver_name', 'ROSELYN B. SENCIL')) }}</div><br>
                            <div class="sig-pos">{{ $request->input('ver_pos', 'HRMO V') }}</div>
                            @if($request->input('ver_pos2'))<br><div class="sig-pos">{{ $request->input('ver_pos2') }}</div>@endif
                            @if($request->input('ver_pos3'))<br><div class="sig-pos">{{ $request->input('ver_pos3') }}</div>@endif
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    @empty
        <div class="page-break">
            <!-- Header -->
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" alt="Logo">
                        @endif
                    </td>
                    <td class="header-text">
                        Republic of the Philippines<br>
                        Department of Education<br>
                        National Capital Region<br>
                        <strong>SCHOOLS DIVISION OFFICE, QUEZON CITY</strong>
                    </td>
                    <td class="header-sp">S.P. FORM A</td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td style="width: 30%;">NAME OF SCHOOL:<br>ALL STATIONS</td>
                    <td class="meta-center" style="width: 40%;">{{ $reportTitle }}</td>
                    <td class="meta-right" style="width: 30%;">PERIOD COVERED:<br>{{ $periodCovered }}</td>
                </tr>
            </table>

            <div class="att-table-wrap">
            <!-- Table -->
            <table class="att-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="col-no">No.</th>
                        <th rowspan="2" class="col-name">Name of Employee</th>
                        @for($d = 1; $d <= 31; $d++)
                            <th class="col-day">{{ $d }}</th>
                        @endfor
                        <th colspan="2" class="col-abs">Absences</th>
                    </tr>
                    <tr>
                        @for($d = 1; $d <= 31; $d++)
                            @php
                                $inMonth = ($d <= $daysInMonth);
                                $ts = $inMonth ? mktime(0, 0, 0, $month, $d, $year) : null;
                                $dow = $ts ? (int) date('w', $ts) : -1;
                                $class = '';
                                if (!$inMonth) $class = 'bg-out';
                                elseif (isset($holidays[$d])) $class = 'bg-holiday';
                                elseif ($dow === 0 || $dow === 6) $class = 'bg-weekend';
                            @endphp
                            <th class="col-day {{ $class }}">
                                {{ $inMonth ? strtoupper(substr(date('D', $ts), 0, 2)) : '' }}
                            </th>
                        @endfor
                        <th class="col-abs" style="border-right: 1px dotted #cbd5e1;">With<br>Pay</th>
                        <th class="col-abs">W/out<br>Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="36" style="padding:40px; font-style:italic; color:#64748b; font-size:11pt;">No attendance records found for this scope/period.</td>
                    </tr>
                </tbody>
            </table>
            </div>


            <!-- Signatory Footer -->
            <div style="clear: both;"></div>
            <div class="sig-container">
                <table class="sig-cert">
                    <tr>
                        <td width="30%">Prepared By:</td>
                        <td width="40%">Certification:</td>
                        <td width="30%">Verified Correct:</td>
                    </tr>
                    <tr>
                        <td style="border-top:none;"></td>
                        <td class="sig-cert-text" style="border-top:none;">This is to certify that personnel listed in this report have rendered services</td>
                        <td style="border-top:none;"></td>
                    </tr>
                </table>

                <table class="sig-names">
                    <tr>
                        <td width="33%">
                            <div style="margin: 0 auto; width: 250px;">
                                <div class="sig-name">{{ strtoupper($request->input('prep_name', 'CHRISTINE JOY C. MAAPOY')) }}</div>
                                <div class="sig-pos">{{ $request->input('prep_pos', 'Administrative Assistant III') }}</div>
                                @if($request->input('prep_pos2'))<div class="sig-pos">{{ $request->input('prep_pos2') }}</div>@endif
                                @if($request->input('prep_pos3'))<div class="sig-pos">{{ $request->input('prep_pos3') }}</div>@endif
                            </div>
                        </td>
                        <td width="34%">
                            <div style="margin: 0 auto; width: 250px;">
                                <div class="sig-name">{{ strtoupper($request->input('cert_name', 'MICHELLE A. MAL-IN')) }}</div>
                                <div class="sig-pos">{{ $request->input('cert_pos', 'HRMO II') }}</div>
                                @if($request->input('cert_pos2'))<div class="sig-pos">{{ $request->input('cert_pos2') }}</div>@endif
                                @if($request->input('cert_pos3'))<div class="sig-pos">{{ $request->input('cert_pos3') }}</div>@endif
                            </div>
                        </td>
                        <td width="33%">
                            <div style="margin: 0 auto; width: 250px;">
                                <div class="sig-name">{{ strtoupper($request->input('ver_name', 'ROSELYN B. SENCIL')) }}</div>
                                <div class="sig-pos">{{ $request->input('ver_pos', 'HRMO V') }}</div>
                                @if($request->input('ver_pos2'))<div class="sig-pos">{{ $request->input('ver_pos2') }}</div>@endif
                                @if($request->input('ver_pos3'))<div class="sig-pos">{{ $request->input('ver_pos3') }}</div>@endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforelse

<script>
function copyDate(btn, text) {
    if (!text) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showCopied(btn);
        });
    } else {
        // Fallback for older browsers
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
