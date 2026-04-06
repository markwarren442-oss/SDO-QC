@extends('layouts.app')

@section('title', 'Absent Records | SDO QC')

@section('styles')
    <style>
        .absent-page {
            display: flex;
            flex-direction: column;
            gap: 0;
            height: calc(100vh - 52px);
            overflow: hidden;
            padding: 0 10px;
        }

        .sticky-top-section {
            flex-shrink: 0;
            background: rgba(224, 242, 254, 0.4);
            backdrop-filter: blur(8px);
            padding: 8px 0 12px;
            z-index: 100;
            position: sticky;
            top: 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            background: white;
            padding: 16px 28px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
        }

        .page-header h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .content-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .scrollable-table-container {
            flex: 1;
            overflow-y: auto;
            position: relative;
            padding: 0 28px 28px;
            -webkit-overflow-scrolling: touch;
        }

        .scrollable-table-container::-webkit-scrollbar {
            width: 8px;
        }

        .scrollable-table-container::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .scrollable-table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid #f8fafc;
        }

        .scrollable-table-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .content-card thead {
            position: sticky;
            top: 0;
            z-index: 5;
            background: white;
            padding-top: 24px;
        }

        .content-card thead th {
            background: white;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .filter-bar select,
        .filter-bar input {
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            background: white;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        th {
            text-align: left;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 10px 15px;
            font-weight: 700;
        }

        td {
            padding: 12px 15px;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--dark);
            background: rgba(255, 255, 255, 0.6);
            border-top: 1px solid rgba(255, 255, 255, 0.6);
            border-bottom: 1px solid rgba(255, 255, 255, 0.6);
        }

        td:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
            border-left: 1px solid rgba(255, 255, 255, 0.6);
        }

        td:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
            border-right: 1px solid rgba(255, 255, 255, 0.6);
        }

        tr:hover td {
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .badge-absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-late {
            background: #ffedd5;
            color: #9a3412;
        }

        .search-container {
            background: white;
            border: 1px solid #cbd5e1;
            border-radius: 50px;
            padding: 7px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .suggestions-box {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #cbd5e1;
            border-top: none;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }

        .suggestion-item {
            padding: 10px 16px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.15s;
            color: var(--dark);
        }

        .suggestion-item:hover {
            background: #f8fafc;
        }

        .suggestion-item:last-child {
            border-radius: 0 0 16px 16px;
        }

        .search-container input {
            border: none;
            outline: none;
            font-size: 0.9rem;
            font-family: inherit;
            background: transparent;
            width: 280px;
        }

        .mode-btn {
            border: none; 
            padding: 7px 14px; 
            border-radius: 10px; 
            font-size: 0.75rem; 
            font-weight: 700; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            gap: 6px; 
            transition: all 0.2s;
        }

        .mode-btn.active {
            background: white; 
            color: #0f172a; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .mode-btn.inactive {
            background: transparent; 
            color: #64748b;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 16px !important;
                padding: 16px 20px !important;
            }

            .page-header>div:nth-child(2) {
                width: 100% !important;
                margin: 0 !important;
                max-width: 100% !important;
            }

            .page-header>div:last-child {
                width: 100%;
                flex-wrap: wrap;
                gap: 8px;
            }

            .content-card {
                padding: 16px;
            }

            /* Table: horizontal scroll */
            .scrollable-table-container {
                overflow-x: auto;
            }

            table {
                min-width: 620px;
            }

            .search-container input {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.25rem !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="absent-page">

        <div class="sticky-top-section animate-fade">
            <div class="page-header">
                <!-- Left: Title & Subtitle -->
                <div>
                    <h1>Absent &
                        Late Records</h1>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 4px 0 0; font-weight: 500;">
                        Track infractions and history ({{ $monthLabel }})
                    </p>
                </div>

                <!-- Center: Styled Search + Filter Pill (matches calendar style) -->
                <div style="flex: 1; display: flex; justify-content: center; max-width: 520px; margin: 0 24px;">
                    <div id="absentSearchBox"
                        style="display: flex; align-items: center; gap: 0; background: white; border: 1.5px solid #e5e7eb; border-radius: 50px; padding: 0; box-shadow: 0 1px 4px rgba(0,0,0,0.04); transition: all 0.2s; width: 100%; position: relative; overflow: hidden;"
                        onfocusin="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'"
                        onfocusout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 1px 4px rgba(0,0,0,0.04)'">
                        <!-- Filter dropdown -->
                        <select id="typeFilter"
                            style="border: none; background: transparent; padding: 10px 14px 10px 16px; font-size: 0.78rem; font-weight: 700; color: #475569; outline: none; cursor: pointer; border-right: 1.5px solid #e5e7eb; white-space: nowrap; flex-shrink: 0;"
                            onchange="filterAbsentTable()">
                            <option value="">All Types</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                        </select>
                        <!-- Search icon + input -->
                        <i class="fas fa-search"
                            style="color: #94a3b8; font-size: 0.82rem; margin-left: 14px; flex-shrink: 0;"></i>
                        <input type="text" id="searchInput" placeholder="Search name or ID..."
                            style="border: none; outline: none; background: transparent; font-size: 0.84rem; font-family: inherit; color: #1e293b; flex: 1; padding: 10px 14px; font-weight: 500;"
                            autocomplete="off">
                        <div id="searchSuggestions" class="suggestions-box"
                            style="top: 100%; left: 0; right: 0; border-radius: 0 0 16px 16px; margin-top: 0;"></div>
                    </div>
                </div>

                <!-- Right: Toggle & Date Selectors -->
                <div style="display: flex; align-items: center; gap: 14px;">


                    {{-- Mode Toggle Pill --}}
                    <div style="display: flex; background: #f1f5f9; padding: 4px; border-radius: 12px;">
                        <button onclick="changeMode('monthly')"
                            class="mode-btn {{ $mode === 'monthly' ? 'active' : 'inactive' }}">
                            <i class="fas fa-calendar-alt"></i> Monthly
                        </button>
                        <button onclick="changeMode('yearly')"
                            class="mode-btn {{ $mode === 'yearly' ? 'active' : 'inactive' }}">
                            <i class="fas fa-calendar"></i> Yearly
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card animate-fade" style="animation-delay: 0.1s;">
            <div class="scrollable-table-container">
                <table id="absentTable">
                    <thead>
                        <tr>
                            <th style="cursor:pointer;" onclick="sortTable(0)">No.</th>
                            <th style="cursor:pointer;" onclick="sortTable(1)">Employee Name</th>
                            <th style="cursor:pointer;" onclick="sortTable(2)">Station</th>
                            <th style="cursor:pointer;" onclick="sortTable(3)">Absent Days</th>
                            <th style="cursor:pointer;" onclick="sortTable(4)">Late Days</th>
                            <th style="cursor:pointer;" onclick="sortTable(5)">With Pay</th>
                            <th style="cursor:pointer;" onclick="sortTable(6)">Without Pay</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $i => $emp)
                            @php
                                $absentCount = 0;
                                $lateCount = 0;
                                $empAttendance = $attendance[$emp->id] ?? [];
                                foreach ($empAttendance as $day => $status) {
                                    if ($status === 'absent' || $status === 'halfday')
                                        $absentCount++;
                                    if ($status === 'late')
                                        $lateCount++;
                                }

                                $withPayCount = 0;
                                $withoutPayCount = 0;
                                $empReasons = [];
                                foreach ($absenceDetails[$emp->id] ?? [] as $rec) {
                                    $key = ($mode === 'monthly') ? $rec->day : ($rec->year_month . '-' . $rec->day);
                                    $empReasons[$key] = $rec->reason;

                                    if (stripos($rec->reason, 'without') !== false) {
                                        $withoutPayCount++;
                                    } elseif (!empty($rec->reason)) {
                                        $withPayCount++;
                                    }
                                }

                                // Only show persons with absent or late records
                                if ($absentCount == 0 && $lateCount == 0) {
                                    continue;
                                }
                            @endphp
                            <tr class="emp-row">
                                <td>{{ $i + 1 }}</td>
                                <td style="font-weight:700;">{{ $emp->last_name }}, {{ $emp->first_name }}</td>
                                <td>{{ $emp->station ?? 'N/A' }}</td>
                                <td>
                                    @if($absentCount > 0)
                                        <span class="badge badge-absent">{{ $absentCount }}
                                            day{{ $absentCount > 1 ? 's' : '' }}</span>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lateCount > 0)
                                        <span class="badge badge-late">{{ $lateCount }} day{{ $lateCount > 1 ? 's' : '' }}</span>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($withPayCount > 0)
                                        <span class="badge" style="background: #dcfce7; color: #166534;">{{ $withPayCount }}
                                            day{{ $withPayCount > 1 ? 's' : '' }}</span>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($withoutPayCount > 0)
                                        <span class="badge" style="background: #fef9c3; color: #854d0e;">{{ $withoutPayCount }}
                                            day{{ $withoutPayCount > 1 ? 's' : '' }}</span>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" onclick="openAbsenceModal(this)"
                                        data-name="{{ $emp->last_name }}, {{ $emp->first_name }}"
                                        data-station="{{ $emp->station ?? 'N/A' }}" data-empnum="{{ $emp->emp_number ?? '' }}"
                                        data-month="{{ $month }}" data-year="{{ $year }}" data-records='@json($empAttendance)'
                                        data-reasons='@json($empReasons)'
                                        style="background:#e0f2fe;color:#0369a1;padding:6px 14px;border-radius:8px;font-size:0.75rem;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        @if(empty($employees))
                            <tr>
                                <td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;">
                                    <i class="fas fa-check-circle"
                                        style="font-size:2rem;margin-bottom:10px;display:block;opacity:0.3;"></i>
                                    No absent or late records for this period.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        {{-- ═══ ABSENCE HISTORY MODAL ═══════════════════════════════ --}}
        <div id="absenceHistoryModal"
            style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:9000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
            <div
                style="background:white;border-radius:20px;max-width:580px;width:93%;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,0.25);overflow:hidden;">

                {{-- Dark Navy Header --}}
                <div
                    style="background:#0f1e30;padding:22px 28px 20px;display:flex;align-items:flex-start;justify-content:space-between;flex-shrink:0;">
                    <div>
                        <h2 id="absHistName" style="font-size:1.45rem;font-weight:800;color:white;margin:0 0 5px;"></h2>
                        <p id="absHistSubtitle" style="font-size:0.8rem;color:#94a3b8;font-weight:500;margin:0;"></p>
                    </div>
                    <button onclick="document.getElementById('absenceHistoryModal').style.display='none';"
                        style="color:#94a3b8;font-size:1.1rem;background:none;border:none;cursor:pointer;padding:4px;line-height:1;margin-top:2px;">×</button>
                </div>

                {{-- Timeline Body --}}
                <div style="flex:1;overflow-y:auto;background:#f1f5f9;padding:24px 28px;">
                    <p id="absHistTimelineLabel"
                        style="font-size:0.68rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#7c8fa8;margin:0 0 18px;">
                    </p>

                    {{-- Timeline container filled by JS --}}
                    <div id="absHistTimeline" style="position:relative;padding-left:24px;"></div>

                    <div id="absHistEmpty" style="display:none;text-align:center;padding:30px;color:#94a3b8;">
                        <i class="fas fa-check-circle"
                            style="font-size:2rem;margin-bottom:10px;display:block;opacity:0.3;"></i>
                        No absences or late records.
                    </div>
                </div>

                {{-- Footer with Print button --}}
                <div
                    style="padding:14px 28px;background:white;border-top:1px solid #e9edf2;display:flex;justify-content:flex-end;flex-shrink:0;">
                    <button onclick="openPrintConfigModal()"
                        style="background:#0f1e30;color:white;border:none;border-radius:50px;padding:10px 20px;font-size:0.82rem;font-weight:700;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>
        </div>

        {{-- Print Configuration Modal --}}
        <div id="printConfigModal"
            style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:9100;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
            <div
                style="background:white;border-radius:20px;max-width:850px;width:95%;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,0.25);overflow:hidden;">
                
                {{-- Dark Navy Header --}}
                <div
                    style="background:#0f1e30;padding:22px 28px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                    <h2 style="font-size:1.45rem;font-weight:800;color:white;margin:0;">Print Report Configuration</h2>
                    <button onclick="document.getElementById('printConfigModal').style.display='none';"
                        style="color:#94a3b8;font-size:1.5rem;background:none;border:none;cursor:pointer;line-height:1;">&times;</button>
                </div>

                <div style="padding:24px 28px;overflow-y:auto;background:#f1f5f9;flex:1;">
                    <p style="font-size:0.85rem;color:#64748b;margin-bottom:20px;">Please specify the signatories for this report before printing.</p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                        
                        {{-- Prepared By --}}
                        <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Prepared By</div>
                            <div style="margin-bottom:10px;">
                                <label style="display:block;font-size:0.75rem;font-weight:700;color:#64748b;margin-bottom:4px;">Signatory Name</label>
                                <input type="text" id="printSigPrepName" placeholder="CHRISTINE JOY C. MAAPOY" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:0.8rem;text-transform:uppercase;font-weight:700;">
                            </div>
                            <div id="print-prep-pos1">
                                <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">
                                    Position Line 1
                                    <button type="button" onclick="addPosField('print', 'prep')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                                </label>
                                <input type="text" id="printSigPrepPos" placeholder="Administrative Assistant III" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:0.8rem;">
                            </div>
                            <div id="print-prep-pos2" style="display:none; margin-top:10px;">
                                <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">
                                    Position Line 2
                                    <button type="button" onclick="removePosField('print', 'prep', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                                </label>
                                <input type="text" id="printSigPrepPos2" placeholder="E-Form7 In-Charge" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:0.8rem;">
                            </div>
                            <div id="print-prep-pos3" style="display:none; margin-top:10px;">
                                <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">
                                    Position Line 3
                                    <button type="button" onclick="removePosField('print', 'prep', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                                </label>
                                <input type="text" id="printSigPrepPos3" placeholder="..." style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:0.8rem;">
                            </div>
                        </div>

                        {{-- Removed middle signatory --}}

                        {{-- Verified By --}}
                        <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Verified Correct By</div>
                            <div style="margin-bottom:10px;">
                                <label style="display:block;font-size:0.75rem;font-weight:700;color:#64748b;margin-bottom:4px;">Signatory Name</label>
                                <input type="text" id="printSigVerName" placeholder="ROSELYN B. SENCIL" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:0.8rem;text-transform:uppercase;font-weight:700;">
                            </div>
                            <div id="print-ver-pos1">
                                <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">
                                    Position Line 1
                                    <button type="button" onclick="addPosField('print', 'ver')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                                </label>
                                <input type="text" id="printSigVerPos" placeholder="HRMO V" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:0.8rem;">
                            </div>
                            <div id="print-ver-pos2" style="display:none; margin-top:10px;">
                                <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">
                                    Position Line 2
                                    <button type="button" onclick="removePosField('print', 'ver', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                                </label>
                                <input type="text" id="printSigVerPos2" placeholder="Administrative Officer V" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:0.8rem;">
                            </div>
                            <div id="print-ver-pos3" style="display:none; margin-top:10px;">
                                <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">
                                    Position Line 3
                                    <button type="button" onclick="removePosField('print', 'ver', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                                </label>
                                <input type="text" id="printSigVerPos3" placeholder="..." style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:0.8rem;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div
                    style="padding:14px 28px;background:white;border-top:1px solid #e9edf2;display:flex;justify-content:flex-end;flex-shrink:0;">
                    <button onclick="document.getElementById('printConfigModal').style.display='none';"
                        style="background:#f1f5f9;color:#64748b;border:none;border-radius:50px;padding:10px 20px;font-size:0.82rem;font-weight:700;font-family:inherit;cursor:pointer;margin-right:10px;">
                        Cancel
                    </button>
                    <button onclick="executePrintReport()"
                        style="background:#0f1e30;color:white;border:none;border-radius:50px;padding:10px 20px;font-size:0.82rem;font-weight:700;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                        <i class="fas fa-print"></i> Generate & Print
                    </button>
                </div>
            </div>
        </div>

@endsection

    @section('scripts')
        <script>
            /* ─── Navigation and Sorting ────────────────────────────── */
            function changeMode(m) {
                window.location.href = `{{ url('admin/absent') }}?mode=${m}&year={{ $year }}&month={{ $month }}`;
            }
            function goToMonth(ym) {
                const [y, m] = ym.split('-');
                window.location.href = `{{ url('admin/absent') }}?mode={{ $mode }}&year=${y}&month=${parseInt(m)}`;
            }
            function goToYear(y) {
                window.location.href = `{{ url('admin/absent') }}?mode={{ $mode }}&year=${y}&month={{ $month }}`;
            }

            let sortDir = {};
            function sortTable(n) {
                const table = document.getElementById("absentTable");
                let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
                switching = true;
                dir = sortDir[n] === "asc" ? "desc" : "asc";
                sortDir[n] = dir;

                while (switching) {
                    switching = false;
                    rows = table.rows;
                    for (i = 1; i < (rows.length - 1); i++) {
                        shouldSwitch = false;
                        x = rows[i].getElementsByTagName("TD")[n];
                        y = rows[i + 1].getElementsByTagName("TD")[n];

                        let xVal = x.innerHTML.toLowerCase().trim();
                        let yVal = y.innerHTML.toLowerCase().trim();

                        // Numeric sort for columns 0, 3, 4
                        if ([0, 3, 4].includes(n)) {
                            // Extract number from badge or text
                            xVal = parseFloat(xVal.replace(/[^0-9.]/g, '')) || 0;
                            yVal = parseFloat(yVal.replace(/[^0-9.]/g, '')) || 0;
                        }

                        if (dir === "asc") {
                            if (xVal > yVal) { shouldSwitch = true; break; }
                        } else if (dir === "desc") {
                            if (xVal < yVal) { shouldSwitch = true; break; }
                        }
                    }
                    if (shouldSwitch) {
                        rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                        switching = true;
                        switchcount++;
                    }
                }
            }

            /* ─── Absence History Modal ─────────────────────────────── */
            let _currentPrintName = '', _currentPrintMonth = '', _currentPrintStation = '',
                _currentPrintEmpNum = '', _currentPrintRecords = {}, _currentPrintReasons = {},
                _currentPrintMode = '', _currentPrintMonthNum = 0, _currentPrintYear = 0;

            function openAbsenceModal(btn) {
                const name = btn.dataset.name;
                const station = btn.dataset.station;
                const empNum = btn.dataset.empnum || '';
                const month = parseInt(btn.dataset.month);
                const year = parseInt(btn.dataset.year);
                const records = JSON.parse(btn.dataset.records);
                const reasons = JSON.parse(btn.dataset.reasons);
                const mode = '{{ $mode }}';

                const monthName = mode === 'monthly'
                    ? new Date(year, month - 1, 1).toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase()
                    : year + ' YEARLY REPORT';

                _currentPrintName    = name;
                _currentPrintMonth   = monthName;
                _currentPrintStation = station;
                _currentPrintEmpNum  = empNum;
                _currentPrintRecords = records;
                _currentPrintReasons = reasons;
                _currentPrintMode    = mode;
                _currentPrintMonthNum = month;
                _currentPrintYear    = year;

                document.getElementById('absHistName').textContent = name;
                document.getElementById('absHistSubtitle').textContent = (empNum ? 'ID: ' + empNum + ' • ' : '') + station;
                document.getElementById('absHistTimelineLabel').textContent = (mode === 'monthly' ? 'Infraction Timeline (' : 'Yearly Infraction Timeline (') + monthName + ')';

                const timeline = document.getElementById('absHistTimeline');
                timeline.innerHTML = '';

                // Vertical line
                const line = document.createElement('div');
                line.style.cssText = 'position:absolute;left:7px;top:6px;bottom:6px;width:2px;background:#e2e8f0;border-radius:2px;';
                timeline.appendChild(line);

                // In yearly mode, keys are 'YYYY-MM-DD'. In monthly, they are 'DD'.
                const keys = Object.keys(records).sort((a, b) => b.localeCompare(a)); // newest first
                let hasItems = false;

                keys.forEach(key => {
                    const status = records[key];
                    if (status !== 'absent' && status !== 'late' && status !== 'halfday') return;
                    hasItems = true;

                    let date;
                    if (mode === 'monthly') {
                        date = new Date(year, month - 1, parseInt(key));
                    } else {
                        // key is like '2026-03-13'
                        const parts = key.split('-');
                        date = new Date(parts[0], parts[1] - 1, parts[2]);
                    }

                    const dayLabelText = date.toLocaleString('default', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }).toUpperCase();
                    const reason = reasons[key] ? reasons[key] : '—';

                    let statusIcon = '<i class="fas fa-user-xmark" style="margin-right:6px;"></i>';
                    let statusLabel = 'ABSENT';
                    let statusColor = '#e53e3e';
                    if (status === 'halfday') { statusLabel = 'HALF-DAY'; statusColor = '#d97706'; }
                    if (status === 'late') { statusLabel = 'LATE'; statusIcon = '<i class="fas fa-clock" style="margin-right:6px;"></i>'; statusColor = '#c05621'; }

                    const item = document.createElement('div');
                    item.style.cssText = 'position:relative;margin-bottom:14px;padding-left:22px;';

                    const dot = document.createElement('div');
                    dot.style.cssText = 'position:absolute;left:0;top:14px;width:14px;height:14px;border-radius:50%;background:#e53e3e;border:2px solid white;box-shadow:0 0 0 2px #e53e3e;flex-shrink:0;';

                    const card = document.createElement('div');
                    card.style.cssText = 'background:white;border-radius:12px;border:1px solid #e9edf2;padding:14px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.05);';
                    card.innerHTML = `
                                    <div style="font-size:0.72rem;font-weight:800;color:#64748b;letter-spacing:0.4px;margin-bottom:5px;">${dayLabelText}</div>
                                    <div style="font-size:0.88rem;font-weight:800;color:${statusColor};margin-bottom:4px;">${statusIcon}${statusLabel}</div>
                                    <div style="font-size:0.82rem;color:#374151;font-weight:500;">Reason: <strong>${reason}</strong></div>
                                `;

                    item.appendChild(dot);
                    item.appendChild(card);
                    timeline.appendChild(item);
                });

                document.getElementById('absHistEmpty').style.display = hasItems ? 'none' : 'block';
                document.getElementById('absHistTimeline').style.display = hasItems ? 'block' : 'none';
                document.getElementById('absenceHistoryModal').style.display = 'flex';
            }

            function openPrintConfigModal() {
                // Reset extra fields visibility
                ['prep', 'ver'].forEach(type => {
                    document.getElementById(`print-${type}-pos2`).style.display = 'none';
                    document.getElementById(`print-${type}-pos3`).style.display = 'none';
                });
                document.getElementById('printConfigModal').style.display = 'flex';
            }

            function addPosField(modalType, sigType) {
                const p2 = document.getElementById(`${modalType}-${sigType}-pos2`);
                const p3 = document.getElementById(`${modalType}-${sigType}-pos3`);
                if (p2.style.display === 'none') {
                    p2.style.display = 'block';
                } else if (p3.style.display === 'none') {
                    p3.style.display = 'block';
                } else {
                    alert('Maximum 3 position lines reached.');
                }
            }

            function removePosField(modalType, sigType, lineNum) {
                const field = document.getElementById(`${modalType}-${sigType}-pos${lineNum}`);
                const inputId = sigType.charAt(0).toUpperCase() + sigType.slice(1);
                const fullInputId = `${modalType}Sig${inputId}Pos${lineNum}`;
                const input = document.getElementById(fullInputId);
                if (input) input.value = '';
                field.style.display = 'none';
            }

            function executePrintReport() {
                document.getElementById('printConfigModal').style.display = 'none';
                const name     = _currentPrintName;
                const station  = _currentPrintStation;
                const empNum   = _currentPrintEmpNum;
                const period   = _currentPrintMonth;
                const records  = _currentPrintRecords;
                const reasons  = _currentPrintReasons;
                const mode     = _currentPrintMode;
                const month    = _currentPrintMonthNum;
                const year     = _currentPrintYear;
                const logoUrl  = '{{ asset('logo.png') }}';

                const pPrepName = document.getElementById('printSigPrepName').value || 'CHRISTINE JOY C. MAAPOY';
                const pPrepPos = document.getElementById('printSigPrepPos').value || 'Administrative Assistant III';
                const pPrepPos2 = document.getElementById('printSigPrepPos2').value;
                const pPrepPos3 = document.getElementById('printSigPrepPos3').value;
                
                const pVerName = document.getElementById('printSigVerName').value || 'ROSELYN B. SENCIL';
                const pVerPos = document.getElementById('printSigVerPos').value || 'HRMO V';
                const pVerPos2 = document.getElementById('printSigVerPos2').value;
                const pVerPos3 = document.getElementById('printSigVerPos3').value;

                // Build table rows from records
                const keys = Object.keys(records).sort((a, b) => a.localeCompare(b));
                let rows = '';
                let rowNum = 0;
                keys.forEach(key => {
                    const status = records[key];
                    if (status !== 'absent' && status !== 'late' && status !== 'halfday') return;
                    rowNum++;

                    let date;
                    if (mode === 'monthly') {
                        date = new Date(year, month - 1, parseInt(key));
                    } else {
                        const parts = key.split('-');
                        date = new Date(parts[0], parseInt(parts[1]) - 1, parseInt(parts[2]));
                    }
                    const dayLabel = date.toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    const reason   = reasons[key] ? reasons[key] : '—';

                    let typeLabel = 'ABSENT';
                    if (status === 'halfday') typeLabel = 'HALF-DAY';
                    if (status === 'late')    typeLabel = 'LATE';

                    const rowBg = rowNum % 2 === 0 ? '#f8fafc' : '#ffffff';
                    rows += `<tr style="background:${rowBg};">
                        <td style="border:1px solid #cbd5e1;padding:7px 10px;text-align:center;">${rowNum}</td>
                        <td style="border:1px solid #cbd5e1;padding:7px 10px;">${dayLabel}</td>
                        <td style="border:1px solid #cbd5e1;padding:7px 10px;text-align:center;font-weight:700;">${typeLabel}</td>
                        <td style="border:1px solid #cbd5e1;padding:7px 10px;">${reason}</td>
                    </tr>`;
                });

                if (!rows) {
                    rows = `<tr><td colspan="4" style="text-align:center;padding:20px;color:#94a3b8;border:1px solid #cbd5e1;">No records found.</td></tr>`;
                }

                const html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Absence &amp; Late Report – ${name}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            padding: 20mm 18mm;
            background: white;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .header { text-align: center; margin-bottom: 18px; }
        .header img { width: 70px; height: 70px; margin-bottom: 6px; }
        .header-agency { font-size: 10pt; line-height: 1.55; }
        .header-agency strong { font-size: 13pt; display: block; margin-top: 4px; text-transform: uppercase; }
        .header-divider { border-top: 2px solid #000; margin: 12px 0 10px; }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-top: 1.5px solid #000;
            padding-top: 10px;
            margin-bottom: 20px;
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .meta-left { text-align: left; }
        .meta-left span { display: block; font-size: 12pt; }
        .meta-center { text-align: center; font-size: 13pt; letter-spacing: 1px; margin-top: 14px; }
        .meta-right { text-align: right; }
        .meta-right span { display: block; font-size: 11pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 10pt; }
        thead th {
            background: #1e3a5f;
            color: #fff;
            padding: 8px 10px;
            border: 1px solid #1e3a5f;
            text-align: left;
            font-size: 10pt;
        }
        thead th:nth-child(1), thead th:nth-child(3) { text-align: center; }
        tbody td { border: 1px solid #cbd5e1; padding: 7px 10px; font-size: 10pt; vertical-align: middle; }
        .signatories {
            display: flex;
            justify-content: space-between;
            margin-top: 45px;
            gap: 30px;
            page-break-inside: avoid;
        }
        .sig-block { flex: 1; text-align: center; }
        .sig-title { font-weight: bold; font-size: 10pt; margin-bottom: 30px; text-align: left; }
        .sig-line { border-bottom: 1.5px solid #000; width: 100%; margin-bottom: 5px; min-height: 22px; display: block; font-weight: 800; text-transform: uppercase; font-size: 10.5pt; }
        .sig-sub { font-size: 9pt; color: #4b5563; }
        @media print {
            body { padding: 0; }
            @page { size: portrait; margin: 12mm 16mm; }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="${logoUrl}" alt="SDO-QC Logo" onerror="this.style.display='none'">
        <div class="header-agency">
            Republic of the Philippines<br>
            Department of Education<br>
            National Capital Region<br>
            <strong>Schools Division Office, Quezon City</strong>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-left">
            EMPLOYEE:<br>
            <span>${name}${empNum ? ' (ID: ' + empNum + ')' : ''}</span>
            STATION / SCHOOL:<br>
            <span>${station}</span>
        </div>
        <div class="meta-center">ABSENCE &amp; LATE REPORT</div>
        <div class="meta-right">
            PERIOD COVERED:<br>
            <span>${period}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Date</th>
                <th style="width:110px;">Type</th>
                <th>Remarks / Reason</th>
            </tr>
        </thead>
        <tbody>
            ${rows}
        </tbody>
    </table>

    <div class="signatories">
        <div class="sig-block">
            <div class="sig-title">Prepared by:</div>
            <div class="sig-line">${pPrepName}</div>
            <div class="sig-sub">${pPrepPos}</div>
            ${pPrepPos2 ? `<div class="sig-sub" style="margin-top:2px;">${pPrepPos2}</div>` : ''}
            ${pPrepPos3 ? `<div class="sig-sub" style="margin-top:2px;">${pPrepPos3}</div>` : ''}
        </div>
        <div class="sig-block">
            <div class="sig-title">Verified Correct by:</div>
            <div class="sig-line">${pVerName}</div>
            <div class="sig-sub">${pVerPos}</div>
            ${pVerPos2 ? `<div class="sig-sub" style="margin-top:2px;">${pVerPos2}</div>` : ''}
            ${pVerPos3 ? `<div class="sig-sub" style="margin-top:2px;">${pVerPos3}</div>` : ''}
        </div>
    </div>
</body>
</html>`;

                const w = window.open('', '_blank');
                w.document.write(html);
                w.document.close();
                w.focus();
                setTimeout(() => w.print(), 600);
            }

            // Close modal when backdrop is clicked
            document.getElementById('absenceHistoryModal').addEventListener('click', function (e) {
                if (e.target === this) this.style.display = 'none';
            });

            const searchInput = document.getElementById('searchInput');
            const typeFilter = document.getElementById('typeFilter');
            const suggestionsBox = document.getElementById('searchSuggestions');
            const tableRows = document.querySelectorAll('#absentTable tbody tr.emp-row');

            function filterAbsentTable() {
                const query = searchInput.value.toLowerCase().trim();
                const type = typeFilter.value.toLowerCase();

                tableRows.forEach(row => {
                    if (row.cells.length < 2) return;
                    const name = row.cells[1].textContent.toLowerCase();
                    const absText = row.cells[3].textContent.toLowerCase();
                    const lateText = row.cells[4].textContent.toLowerCase();

                    const searchMatch = !query || name.includes(query) || row.textContent.toLowerCase().includes(query);

                    let typeMatch = true;
                    if (type === 'absent') typeMatch = absText.includes('day');
                    else if (type === 'late') typeMatch = lateText.includes('day');

                    row.style.display = (searchMatch && typeMatch) ? '' : 'none';
                });

                // Autocomplete suggestions
                if (query.length >= 2) {
                    const matches = [];
                    tableRows.forEach(row => {
                        if (row.cells.length > 1 && row.style.display !== 'none') {
                            const n = row.cells[1].textContent.trim();
                            if (n.toLowerCase().includes(query)) matches.push(n);
                        }
                    });
                    const unique = [...new Set(matches)].slice(0, 6);
                    if (unique.length) {
                        suggestionsBox.innerHTML = unique.map(n =>
                            `<div class="suggestion-item" onclick="selectSuggestion('${n.replace(/'/g, "\\'")}')">${n}</div>`
                        ).join('');
                        suggestionsBox.style.display = 'block';
                    } else {
                        suggestionsBox.style.display = 'none';
                    }
                } else {
                    suggestionsBox.style.display = 'none';
                }
            }

            searchInput.addEventListener('input', filterAbsentTable);

            function selectSuggestion(name) {
                searchInput.value = name;
                suggestionsBox.style.display = 'none';
                filterAbsentTable();
            }

            window.addEventListener('click', function (e) {
                if (!e.target.closest('#absentSearchBox')) suggestionsBox.style.display = 'none';
            });
        </script>
    @endsection