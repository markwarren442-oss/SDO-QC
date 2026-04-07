<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        @page {
            margin: 0.5in;
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
        
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
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
        }

        /* Summary Table */
        .att-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .att-table th, .att-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 5px;
            text-align: center;
        }
        .att-table th {
            font-weight: bold;
            background-color: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 8.5pt;
            letter-spacing: 0.5px;
        }
        .col-no { width: 5%; }
        .col-name { width: 30%; text-align: left !important; padding-left: 10px !important; font-weight: 600; }
        .col-station { width: 20%; }
        .col-stat { width: 11.25%; font-weight: 600; }

        /* Signatories */
        .sig-container {
            width: 100%;
            margin-top: 30px;
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
    <div class="page-break">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @if(isset($logoBase64) && $logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo">
                    @endif
                </td>
                <td class="header-text">
                    Republic of the Philippines<br>
                    Department of Education<br>
                    National Capital Region<br>
                    <strong>SCHOOLS DIVISION OFFICE, QUEZON CITY</strong>
                </td>
                <td style="width: 100px;"></td>
            </tr>
        </table>

        <table class="meta-table">
            <tr>
                <td style="width: 25%;">NAME OF SCHOOL:<br>ALL STATIONS</td>
                <td class="meta-center" style="width: 50%;">{{ $reportTitle }}</td>
                <td class="meta-right" style="width: 25%;">PERIOD COVERED:<br>{{ $periodCovered }}</td>
            </tr>
        </table>

        <!-- Summary Table -->
        <table class="att-table">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-name">Name of Employee</th>
                    <th class="col-station">Station</th>
                    <th class="col-stat">Total Present</th>
                    <th class="col-stat">Total Lates</th>
                    <th class="col-stat">Total Undertimes</th>
                    <th class="col-stat">Total Absences</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="col-name">{{ strtoupper($r->emp_name) }}</td>
                        <td>{{ strtoupper($r->station) }}</td>
                        <td style="color:#16a34a;">{{ $r->pres > 0 ? $r->pres : '-' }}</td>
                        <td style="color:#ea580c;">{{ $r->late > 0 ? $r->late : '-' }}</td>
                        <td style="color:#b45309;">{{ $r->ut > 0 ? $r->ut : '-' }}</td>
                        <td style="color:#dc2626;">{{ $r->abs > 0 ? $r->abs : '-' }}</td>
                    </tr>
                @endforeach
                @if(count($rows) === 0)
                    <tr>
                        <td colspan="7" style="padding:20px; color:#64748b; font-style:italic;">No records found for this period.</td>
                    </tr>
                @endif
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
                    <td class="sig-cert-text" style="border-top:none;">This is to certify that personnel listed in this report have rendered services</td>
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
</body>
</html>
