@extends('layouts.app')

@section('title', 'Employee Management | SDO QC')

@section('styles')
    {{-- SheetJS for client-side Excel parsing --}}
    <script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
    <style>
        /* ═══════════════════════════════════════════════
                                                                   PAGE WRAPPER
                                                                ═══════════════════════════════════════════════ */
        /* ── Page Layout ── */
        .em-page {
            display: flex;
            flex-direction: column;
            gap: 0;
            height: calc(100vh - 52px);
            overflow: hidden;
        }

        .sticky-top-section {
            flex-shrink: 0;
            background: rgba(224, 242, 254, 0.4);
            backdrop-filter: blur(8px);
            padding: 8px 16px 12px;
            z-index: 100;
            position: sticky;
            top: 0;
            margin-bottom: 8px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            background: white;
            padding: 14px 24px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
        }

        .page-header h1 {
            font-size: 1.4rem;
            font-weight: 850;
            color: #1e293b;
            margin: 0;
        }

        /* ── Text-based Stats Header ── */
        .stat-summary-text {
            display: flex;
            gap: 24px;
            font-size: 0.85rem;
            font-weight: 750;
            color: #64748b;
            background: #f8fafc;
            padding: 8px 24px;
            border-radius: 50px;
            border: 1.5px solid #edf2f7;
        }

        .stat-summary-text span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stat-summary-text strong {
            font-size: 1.05rem;
            color: #0f172a;
        }

        /* ── Control Bar ── */
        .control-panel {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 0;
            margin-bottom: 2px;
        }

        .search-action-bar {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 800px;
        }

        .pill-container {
            display: flex;
            align-items: center;
            background: white;
            border: 1.5px solid #e2e8f0;
            border-radius: 50px;
            height: 42px;
            overflow: hidden;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
        }

        .pill-container:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        /* Suggestions Dropdown */
        .em-suggestions {
            position: absolute;
            top: 40px;
            left: 0;
            width: 310px;
            background: white;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }

        .suggestion-item {
            padding: 10px 16px;
            cursor: pointer;
            font-size: 0.82rem;
            transition: background 0.15s;
        }

        .suggestion-item:hover {
            background: #f8fafc;
        }

        /* ── Action Buttons ── */
        .em-btn-add {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 20px;
            height: 42px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .em-btn-add:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.2);
        }

        /* ── Responsive ── */
        @media (max-width: 1100px) {
            .page-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                padding: 16px 20px !important;
            }
            .stat-summary-text {
                width: 100%;
                justify-content: space-between;
                padding: 10px 16px;
                border: none;
                background: #f1f5f9;
                border-radius: 12px;
                gap: 12px;
            }
            .control-panel {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 600px) {
            .stat-cards-grid {
                grid-template-columns: 1fr !important;
            }
        }

        /* ═══════════════════════════════════════════════
                                                                   TABLE CARD
                                                                ═══════════════════════════════════════════════ */
        .em-card {
            background: var(--glass-strong);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.4);
            overflow: hidden;
            animation: fadeInUp 0.5s ease;
            flex: 1 1 0;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .em-table-wrap {
            overflow-x: auto;
            overflow-y: auto;
            flex: 1 1 0;
            min-height: 0;
        }

        table.em-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            min-width: 1100px;
        }

        /* Table Head */
        table.em-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        table.em-table thead tr.head-main th {
            padding: 6px 10px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #3b82f6;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
            text-align: left;
            position: relative;
        }

        table.em-table thead tr.head-main th.att-group-header {
            text-align: center;
            color: #3b82f6;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        table.em-table thead tr.head-att th {
            padding: 4px 6px;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: #94a3b8;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        /* Table Rows */
        table.em-table tbody tr {
            transition: background 0.1s;
        }

        table.em-table tbody tr:last-child td {
            border-bottom: 1px solid #e2e8f0;
        }

        table.em-table tbody tr:hover {
            background: #eff6ff;
        }

        table.em-table td {
            padding: 5px 10px;
            font-size: 0.9rem;
            color: #1e293b;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
            height: 38px;
        }

        /* ─── Employee Profile Cell ─── */
        .emp-profile-name {
            font-weight: 800;
            font-size: 0.95rem;
            color: #111827;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .emp-profile-id {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .emp-station-badge {
            display: inline-block;
            padding: 1px 6px;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* ─── Status & Time Cell ─── */
        .status-select {
            width: 100%;
            padding: 3px 6px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            font-size: 0.72rem;
            font-weight: 700;
            font-family: inherit;
            color: #374151;
            background: white;
            cursor: pointer;
            outline: none;
            margin-bottom: 3px;
            transition: border-color 0.18s;
        }

        .status-select:focus {
            border-color: #3b82f6;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.68rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-active  { background: #22c55e; }
        .dot-inactive { background: #ef4444; }
        .dot-other   { background: #ef4444; }

        .official-time {
            font-size: 0.68rem;
            color: #6b7280;
            font-weight: 600;
            white-space: nowrap;
        }

        /* ─── Attendance Stat Box ─── */
        .att-stat-box {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .att-stat-val {
            font-size: 1.15rem;
            font-weight: 700;
            color: #374151;
            line-height: 1;
        }

        .att-stat-val.has-data {
            color: #2563eb;
        }

        .stat-yellow {
            color: #d97706 !important; /* Amber-600 */
        }

        .stat-red {
            color: #dc2626 !important; /* Red-600 */
        }

        .att-stat-sub {
            font-size: 0.58rem;
            color: #b0bec5;
            font-weight: 500;
            margin-top: 2px;
            white-space: nowrap;
        }

        /* ─── Days W/ Pay ─── */
        .days-circle {
            font-weight: 700;
            font-size: 1.15rem;
            color: #15803d;
        }

        /* ─── W.O.P. ─── */
        .wop-circle {
            font-weight: 700;
            font-size: 1.15rem;
            color: #dc2626;
        }

        /* ─── Copy All Button (Profile Cell) ─── */
        .copy-all-btn {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 1px 6px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 4px;
            cursor: pointer;
            color: #94a3b8;
            font-size: 0.6rem;
            font-weight: 700;
            transition: all 0.18s;
            position: relative;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .copy-all-btn:hover {
            background: #dbeafe;
            color: #3b82f6;
            border-color: #93c5fd;
        }
        .copy-all-btn.copied {
            background: #dcfce7;
            color: #16a34a;
            border-color: #86efac;
        }
        .copy-all-tooltip {
            position: absolute;
            bottom: calc(100% + 5px);
            left: 50%;
            transform: translateX(-50%) scale(0.85);
            background: #1e293b;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 5px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.18s ease;
            z-index: 9999;
        }
        .copy-all-btn.copied .copy-all-tooltip {
            opacity: 1;
            transform: translateX(-50%) scale(1);
        }

        /* ─── Bento Menu Actions ─── */
        .bento-menu-wrap {
            position: relative;
            display: flex;
            justify-content: center;
        }

        .bento-btn {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: white;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            font-size: 0.95rem;
        }

        .bento-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .bento-menu-wrap:hover .bento-btn {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .bento-menu-wrap:hover .bento-dropdown {
            display: grid;
        }
        
        /* Bridge to make hovering smooth */
        .bento-menu-wrap::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50px;
            height: 100%;
            background: transparent;
            z-index: 999;
            display: none;
        }
        .bento-menu-wrap:hover::after { display: block; }

        .bento-dropdown {
            position: absolute;
            top: 0;
            right: 42px;
            background: white;
            border-radius: 16px;
            padding: 8px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
            border: 1px solid #f1f5f9;
            z-index: 1000;
            display: none;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            min-width: 180px;
            animation: bentoFadeIn 0.2s ease;
        }

        @keyframes bentoFadeIn {
            from { opacity: 0; transform: scale(0.9) translateX(10px); }
            to { opacity: 1; transform: scale(1) translateX(0); }
        }

        .bento-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 8px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.15s;
            gap: 4px;
        }

        .bento-item i { font-size: 1rem; }
        .bento-item span { font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px; }

        .bento-attendance { background: #f0fdf4; color: #166534; }
        .bento-attendance:hover { background: #dcfce7; }
        .bento-edit { background: #eff6ff; color: #1e40af; }
        .bento-edit:hover { background: #dbeafe; }
        .bento-delete { background: #fef2f2; color: #991b1b; }
        .bento-delete:hover { background: #fee2e2; }
        .bento-print { background: #faf5ff; color: #6b21a8; }
        .bento-print:hover { background: #f3e8ff; }

        /* ─── Remarks Name Highlight ─── */
        .emp-profile-name.has-remarks {
            color: #d97706 !important;
            position: relative;
        }
        .remarks-tooltip {
            display: none;
            position: absolute;
            left: 0;
            top: 100%;
            min-width: 200px;
            max-width: 280px;
            background: #1e293b;
            color: #fbbf24;
            font-size: 0.73rem;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
            z-index: 9999;
            box-shadow: 0 8px 16px rgba(0,0,0,0.25);
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
            margin-top: 4px;
            pointer-events: none;
        }
        .remarks-tooltip::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 14px;
            width: 10px;
            height: 10px;
            background: #1e293b;
            transform: rotate(45deg);
            z-index: -1;
        }
        .emp-profile-name.has-remarks:hover .remarks-tooltip {
            display: block;
        }

        /* ─── Remarks Tab ─── */
        .tab-remarks { background: #fff7ed; color: #b45309; opacity: 0.6; }
        .tab-remarks.active { background: #fed7aa !important; opacity: 1 !important; border-color: #f59e0b !important; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        /* ── Report Preview Styles ── */
        .report-section { background: #fff; border-radius: 16px; padding: 14px; border: 1.2px solid #f1f5f9; }
        .report-pills { display: flex; flex-wrap: wrap; gap: 6px; }
        .report-pill { display: inline-flex; align-items: center; padding: 5px 12px; border-radius: 50px; font-size: 0.72rem; font-weight: 850; letter-spacing: 0.2px; }
        .pill-pres { background: #f0fdf4; color: #166534; box-shadow: 0 2px 5px rgba(22, 101, 52, 0.05); }
        .pill-abs { background: #fef2f2; color: #991b1b; box-shadow: 0 2px 5px rgba(153, 27, 27, 0.05); }
        .pill-late { background: #faf5ff; color: #6b21a8; }

        /* ─── Empty Row ─── */
        .empty-row td {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        /* ═══════════════════════════════════════════════
                                                                   MODALS
                                                                ═══════════════════════════════════════════════ */
        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 700;
            font-size: 0.8rem;
            color: #475569;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 9px 13px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            outline: none;
            font-family: inherit;
            font-size: 0.85rem;
            color: #1e293b;
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-row {
            display: flex;
            gap: 12px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .modal-btn {
            padding: 9px 18px;
            border-radius: 10px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.82rem;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.18s;
        }

        .modal-btn-cancel {
            background: #f1f5f9;
            color: #475569;
        }

        .modal-btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 3px 12px rgba(59, 130, 246, 0.3);
        }

        .modal-btn-green {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 3px 12px rgba(16, 185, 129, 0.3);
        }

        .modal-btn-red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 3px 12px rgba(239, 68, 68, 0.3);
        }

        .modal-btn-amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 3px 12px rgba(245, 158, 11, 0.3);
        }

        .modal-btn-purple {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            color: white;
            box-shadow: 0 3px 12px rgba(139, 92, 246, 0.3);
        }

        .modal-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        /* ── Excel Preview Modal ─────────────────────────── */
        .excel-preview-box {
            max-width: 900px !important;
            width: 96vw;
            padding: 0 !important;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 88vh;
        }

        .excel-preview-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 22px 24px 14px;
            border-bottom: 1.5px solid #f1f5f9;
            flex-shrink: 0;
        }

        .excel-preview-stats {
            padding: 10px 24px;
            background: #f8fafc;
            font-size: 0.82rem;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
        }

        .excel-preview-table-wrap {
            overflow-y: auto;
            flex: 1;
            padding: 0 12px;
        }

        .excel-preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .excel-preview-table thead th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            padding: 10px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1.5px solid #e2e8f0;
            z-index: 2;
            text-align: left;
        }

        .preview-row td {
            padding: 5px 4px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .preview-row:hover td {
            background: #f8fafc;
        }

        .preview-idx {
            text-align: center;
            color: #94a3b8;
            font-size: 0.72rem;
            font-weight: 600;
            width: 34px;
        }

        .preview-input {
            width: 100%;
            border: 1.5px solid transparent;
            background: transparent;
            padding: 5px 7px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-family: inherit;
            color: #1e293b;
            font-weight: 500;
            outline: none;
            transition: border-color 0.15s, background 0.15s;
            box-sizing: border-box;
        }

        .preview-input:hover {
            border-color: #e2e8f0;
            background: #fff;
        }

        .preview-input:focus {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .preview-del-btn {
            background: none;
            border: none;
            color: #fca5a5;
            cursor: pointer;
            font-size: 0.85rem;
            padding: 4px 6px;
            border-radius: 6px;
            transition: color 0.15s, background 0.15s;
        }

        .preview-del-btn:hover {
            color: #dc2626;
            background: #fff1f2;
        }

        .excel-preview-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 24px;
            border-top: 1.5px solid #f1f5f9;
            background: #fff;
            flex-shrink: 0;
        }

        /* Action tab buttons */
        .att-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 18px;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 14px;
        }

        .att-tab {
            flex: 1;
            padding: 7px 10px;
            border-radius: 8px;
            border: 1.5px solid transparent;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.18s;
        }

        .tab-late { background: #fdf6e3; color: #b45309; opacity: 0.6; }
        .tab-undertime { background: #f0f9ff; color: #0369a1; opacity: 0.6; }
        .tab-absent { background: #fef2f2; color: #dc2626; opacity: 0.6; }
        .tab-present { background: #f5f3ff; color: #7e22ce; opacity: 0.6; }

        .att-tab.active {
            opacity: 1 !important;
            border-color: currentColor !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .tab-late.active { background: #fef3c7 !important; }
        .tab-undertime.active { background: #e0f2fe !important; }
        .tab-absent.active { background: #fee2e2 !important; }
        .tab-present.active { background: #f3e8ff !important; }

        /* Present month preview */
        .present-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .present-preview-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .present-preview-icon i {
            color: white;
            font-size: 1.2rem;
        }

        .present-preview-info strong {
            display: block;
            color: #065f46;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .present-preview-info span {
            color: #059669;
            font-size: 0.75rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .em-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .em-header-controls {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .em-search-part input {
                width: 100%;
            }

            /* Wrap table for horizontal scroll  */
            .em-page>div {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 480px) {
            .em-header-title h1 {
                font-size: 1.2rem;
            }

            .em-header-controls {
                gap: 8px;
            }
        }
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }
    </style>
@endsection

@section('content')
    @php
        /* ── Helpers ── */
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $monthVal = sprintf('%04d-%02d', $year, $month); // for <input type="month">

        function fmtMins(int $mins): string
        {
            if ($mins <= 0)
                return '—';
            $h = intdiv($mins, 60);
            $m = $mins % 60;
            return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
        }
    @endphp

    <div class="em-page">

        <div class="sticky-top-section animate-fade">
            <div class="page-header">
                {{-- Left: Title & Subtitle --}}
                <div style="flex-shrink: 0;">
                    <h1>Personnel Management</h1>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 2px 0 0; font-weight: 500;">
                        Workforce overview & attendance summary
                    </p>
                </div>

                {{-- Center: Text-based Stats (Simplified) --}}
                <div class="stat-summary-text">
                    <span>Personnel: <strong style="color:#2563eb;">{{ $totalEmp }}</strong></span>
                    <span>Active: <strong style="color:#16a34a;">{{ $activeCount }}</strong></span>
                    <span>Leave: <strong style="color:#d97706;">{{ $leaveCount }}</strong></span>
                    <span>Inactive: <strong style="color:#dc2626;">{{ $inactiveCount }}</strong></span>
                </div>

                {{-- Right: Date Nav & Mode --}}
                <div style="display: flex; align-items: center; gap: 16px;">
                    {{-- Date Navigation --}}
                    @php 
                        $prevM_str = sprintf('%02d', $prevMonth); 
                        $nextM_str = sprintf('%02d', $nextMonth); 
                    @endphp
                    <div style="display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 4px 12px; border-radius: 12px; border: 1.5px solid #edf2f7;">
                        <button onclick="goToMonth('{{ $prevYear }}-{{ $prevM_str }}')"
                            style="background: transparent; border: none; font-size: 0.9rem; color: #94a3b8; cursor: pointer; padding: 4px;"><i class="fas fa-chevron-left"></i></button>
                        <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a; position: relative; cursor:pointer;">
                            {{ strtoupper($monthLabel) }}
                            <input type="month" value="{{ $monthVal }}" onchange="goToMonth(this.value)"
                                style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;">
                        </span>
                        <button onclick="goToMonth('{{ $nextYear }}-{{ $nextM_str }}')"
                            style="background: transparent; border: none; font-size: 0.9rem; color: #94a3b8; cursor: pointer; padding: 4px;"><i class="fas fa-chevron-right"></i></button>
                    </div>

                    <style>
                        .mode-btn { border: none; padding: 7px 14px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: 0.2s; }
                        .mode-btn.active { background: white; color: #0f172a; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
                        .mode-btn.inactive { background: transparent; color: #64748b; }
                    </style>
                    {{-- Mode Toggle --}}
                    <div style="display: flex; background: #f1f5f9; padding: 4px; border-radius: 12px;">
                        <button onclick="goToMode('monthly')"
                            class="mode-btn {{ $mode !== 'yearly' ? 'active' : 'inactive' }}">
                            <i class="fas fa-calendar-day"></i> Monthly
                        </button>
                        <button onclick="goToMode('yearly')"
                            class="mode-btn {{ $mode === 'yearly' ? 'active' : 'inactive' }}">
                            <i class="fas fa-calendar-alt"></i> Yearly
                        </button>
                    </div>
                </div>
            </div>

            {{-- Secondary Bar: Search, Filters & Actions --}}
            <div class="control-panel">
                <div class="search-action-bar">
                    <div class="pill-container" style="flex: 1;">
                        {{-- Station Filter --}}
                        <select onchange="window.location.href='?station='+this.value"
                            style="border: none; background: #f8fafc; padding: 0 16px; font-size: 0.78rem; font-weight: 800; color: #475569; outline: none; border-right: 1.5px solid #e2e8f0; height: 100%;">
                            <option value="">All Stations</option>
                            @foreach($stations as $s)
                                <option value="{{ $s->station }}" {{ $filterStation == $s->station ? 'selected' : '' }}>
                                    {{ strtoupper($s->station) }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Search Input --}}
                        <div style="flex: 1; display: flex; align-items: center; padding: 0 14px;">
                            <i class="fas fa-search" style="color: #94a3b8; font-size: 0.82rem; margin-right: 10px;"></i>
                            <input type="text" id="searchInput" placeholder="Search name or ID..."
                                style="border: none; outline: none; background: transparent; font-size: 0.85rem; font-weight: 500; width: 100%; color: #1e293b;" autocomplete="off">
                            <div id="searchSuggestions" class="em-suggestions" style="top: 40px; left: 0;"></div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <button class="em-btn-add" onclick="openAddModal()" style="height: 42px; padding: 0 18px; font-size: 0.8rem; border-radius: 12px;">
                        <i class="fas fa-plus"></i> Add New
                    </button>
                    
                    <button class="em-btn-add" onclick="saveAllStatusAndTimes()" id="saveAllBtn" 
                        style="background: linear-gradient(135deg, #10b981, #059669); height: 42px; padding: 0 18px; font-size: 0.8rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(16,185,129,0.2);">
                        <i class="fas fa-save"></i> Save All
                    </button>

                    <button class="em-btn-add" onclick="confirmClearAllEmployees()" id="clearAllBtn"
                        style="background: linear-gradient(135deg, #ef4444, #dc2626); height: 42px; padding: 0 18px; font-size: 0.8rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);">
                        <i class="fas fa-trash-alt"></i> Clear All
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ TABLE CARD ═════════════════════════════════════════════ --}}
        <div class="em-card">
            <div class="em-table-wrap">
                <table class="em-table" id="empTable">
                    <thead>
                        {{-- Main header row --}}
                        <tr class="head-main">
                            <th rowspan="2" style="vertical-align: middle; width: 210px;">Employee Profile</th>
                            <th rowspan="2" style="vertical-align: middle; width: 140px;">Status &amp; Time</th>
                            <th colspan="6" class="att-group-header">
                                Attendance ({{ strtoupper($monthLabel) }})
                            </th>
                            <th rowspan="2" style="text-align:center; vertical-align: middle; width: 100px;">Days W/ Pay</th>
                            <th rowspan="2" style="text-align:center; vertical-align: middle; width: 100px;">W.O.P.</th>
                            <th rowspan="2" style="text-align:center; vertical-align: middle; width: 160px;">Actions</th>
                        </tr>
                        {{-- Sub-header for attendance columns --}}
                        <tr class="head-att">
                            <th style="width: 70px;">PRES</th>
                            <th style="width: 70px;">ABS</th>
                            <th style="width: 70px;">UNDRTM</th>
                            <th style="width: 70px;">LATE</th>
                            <th style="width: 70px;">TOTAL MINUTES</th>
                            <th style="width: 70px;">TOTAL CONVERTED</th>
                        </tr>
                    </thead>
                    <tbody id="empTableBody">
                        @forelse ($employees as $emp)
                            @php
                                $empId = $emp->id;
                                $status = $emp->status ?? 'ACTIVE';
                                $att = $attendanceSummary[$empId] ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'undertime' => 0, 'halfday' => 0];
                                $lMin = $lateMins[$empId] ?? 0;
                                $utMin = $undertimeMins[$empId] ?? 0;
                                $totalMins = $lMin + $utMin;
                                $totalConv = number_format($totalMins / 480, 3);
                                $reasons = $absReasons[$empId] ?? ['with_pay' => 0, 'without_pay' => 0];

                                $empLateLogs = $lateDetails[$empId] ?? [];
                                $empUtLogs = $utDetails[$empId] ?? [];
                                $lateCount = count($empLateLogs);
                                $utCount = count($empUtLogs);
                                $tardyCount = $lateCount + $utCount;

                                $daysWithPay = $reasons['with_pay'];
                                $wop = $reasons['without_pay'];
                                $absCount = $daysWithPay + $wop;
                                $presCount = ($att['present'] ?? 0) + $lateCount;

                                $tardyLog = array_merge($empLateLogs, $empUtLogs);
                                usort($tardyLog, function($a, $b) { return $a['day'] <=> $b['day']; });

                                $daysWithPay = $reasons['with_pay'];
                                $wop = $reasons['without_pay'];

                                $empAbsDetails = $absDetails[$empId] ?? ['with_pay_days' => [], 'without_pay_days' => []];
                                // empWithPayLogs / empWopLogs now expect [{day, type}] for preview modal
                                $empWithPayLogs = array_map(function($d) { return ['day' => $d['day'], 'type' => 'With Pay']; }, $empAbsDetails['with_pay_days']);
                                $empWopLogs = array_map(function($d) { return ['day' => $d['day'], 'type' => 'Without Pay']; }, $empAbsDetails['without_pay_days']);

                                $statusClass = match (true) {
                                    $status === 'ACTIVE' => 'dot-active',
                                    $status === 'INACTIVE' => 'dot-inactive',
                                    default => 'dot-other',
                                };
                            @endphp
                            <tr class="emp-row" data-search="{{ strtolower(($emp->last_name ?? '') . ' ' . ($emp->first_name ?? '') . ' ' . ($emp->middle_name ?? '') . ' ' . ($emp->emp_number ?? '') . ' ' . ($emp->station ?? '')) }}">

                                {{-- Employee Profile --}}
                                <td>
                                    @php
                                        $hasRemarks   = !empty(trim($emp->remarks ?? ''));
                                        $remarksDone  = (bool)($emp->remarks_done ?? false);
                                        $showHighlight = $hasRemarks && !$remarksDone;

                                        // Build copy data — only with/without pay absences with their reasons
                                        $copyWpDays  = array_values($empAbsDetails['with_pay_days']);    // [{day, reason}]
                                        $copyWopDays = array_values($empAbsDetails['without_pay_days']); // [{day, reason}]
                                        $copyAllData = json_encode([
                                            'with_pay'    => $copyWpDays,
                                            'without_pay' => $copyWopDays,
                                        ]);
                                    @endphp
                                    <div class="emp-profile-name {{ $showHighlight ? 'has-remarks' : '' }}" style="position:relative;">
                                        {{ $emp->last_name ?? '' }}, {{ $emp->first_name ?? '' }} {{ $emp->middle_name ? substr(trim($emp->middle_name), 0, 1) . '.' : '' }}
                                        @if($showHighlight)
                                            <i class="fas fa-comment-alt" style="font-size:0.65rem;margin-left:4px;color:#f59e0b;"></i>
                                            <span class="remarks-tooltip">{{ $emp->remarks }}</span>
                                        @endif
                                    </div>
                                    <div style="display:flex; align-items:center; gap:5px; margin-top:2px; flex-wrap:wrap;">
                                        <div class="emp-profile-id">ID: {{ $emp->emp_number ?? 'N/A' }}</div>
                                        @if($emp->station)
                                            <span class="emp-station-badge">{{ $emp->station }}</span>
                                        @endif
                                        <button type="button" class="copy-all-btn" title="Copy all attendance dates"
                                            data-copy-all='{{ $copyAllData }}'
                                            onclick="copyAllAttendance(this)">
                                            <i class="fas fa-copy"></i> Copy
                                            <span class="copy-all-tooltip">Copied!</span>
                                        </button>
                                    </div>
                                </td>

                                {{-- Status & Time --}}
                                <td>
                                    <select class="status-select" id="status_select_{{ $empId }}"
                                        data-emp-id="{{ $empId }}"
                                        onchange="handleStatusChange(this.dataset.empId, this)">
                                        @php
                                            $isPreset = in_array($status, ['ACTIVE', 'INACTIVE']);
                                        @endphp
                                        <option value="ACTIVE" {{ $status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                                        <option value="INACTIVE" {{ $status === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                                        @if(!$isPreset && $status && $status !== 'OTHERS')
                                            <option value="{{ $status }}" selected>{{ $status }}</option>
                                        @endif
                                        <option value="OTHERS">OTHERS</option>
                                    </select>
                                    <input type="text" id="status_input_{{ $empId }}"
                                        data-emp-id="{{ $empId }}"
                                        style="display:none; width:100%; padding:5px 8px; font-size:0.75rem; border-radius:8px; border:1.5px solid #e5e7eb; margin-bottom:5px; outline:none;"
                                        placeholder="Type Status & Enter"
                                        onkeydown="handleCustomStatusEnter(event, this.dataset.empId, this)">
                                    <div class="status-indicator" style="margin-bottom: 8px;">
                                        <span id="status_dot_{{ $empId }}" class="status-dot {{ $statusClass }}"></span>
                                        @php $statusColor = $status === 'ACTIVE' ? '#15803d' : '#ef4444'; @endphp
                                        <span id="status_text_{{ $empId }}"
                                            style="color:{{ $statusColor }}">{{ $status }}</span>
                                    </div>
                                        <select id="official_time_{{ $empId }}"
                                               style="width: 100%; padding: 3px 6px; font-size: 0.72rem; border-radius: 6px; border: 1.5px solid #e5e7eb; outline: none; background: #f8fafc; font-weight: 600; color: #475569; cursor: pointer;"
                                               onchange="queueOfficialTimeChange('{{ $empId }}', this.value)">
                                            <option value="">-- Time --</option>
                                            <option value="7:00-4:00" {{ ($emp->official_time ?? '') === '7:00-4:00' ? 'selected' : '' }}>7:00-4:00</option>
                                            <option value="8:00-5:00" {{ ($emp->official_time ?? '') === '8:00-5:00' ? 'selected' : '' }}>8:00-5:00</option>
                                            <option value="9:00-6:00" {{ ($emp->official_time ?? '') === '9:00-6:00' ? 'selected' : '' }}>9:00-6:00</option>
                                        </select>
                                    </div>
                                </td>

                                {{-- PRES --}}
                                <td style="text-align:center;">
                                    <span class="att-stat-val {{ $presCount > 0 ? 'has-data' : '' }}">{{ $presCount }}</span>
                                </td>

                                {{-- ABS --}}
                                <td style="text-align:center;">
                                    <div class="abs-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-id="{{ $empId }}"
                                         data-tardy='@json(array_merge($empWithPayLogs, $empWopLogs))'
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showAbsencePreview(this, 'Absence Records')"
                                         title="Click to view absence details"
                                         onmouseover="this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="att-stat-val {{ $absCount > 0 ? 'stat-red' : '' }}">{{ $absCount }}</span>
                                    </div>
                                </td>








                                {{-- UNDRTM --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-id="{{ $empId }}"
                                         data-tardy='@json($empUtLogs)'
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showTardyPreview(this, 'Undertime Records')"
                                         title="Click to view undertime details"
                                         onmouseover="this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="att-stat-val {{ $utMin > 0 ? 'stat-yellow' : '' }}">{{ $utMin }}m</span>
                                    </div>
                                </td>

                                {{-- LATE --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-id="{{ $empId }}"
                                         data-tardy='@json($empLateLogs)'
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showTardyPreview(this, 'Late Records')"
                                         title="Click to view late details"
                                         onmouseover="this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="att-stat-val {{ $lMin > 0 ? 'stat-yellow' : '' }}">{{ $lMin }}m</span>
                                    </div>
                                </td>

                                {{-- TARDY --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-id="{{ $empId }}"
                                         data-tardy='@json(array_merge($empLateLogs, $empUtLogs))'
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showTardyPreview(this, 'Total Minute Breakdown')"
                                         title="Click to view minute details"
                                         onmouseover="this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="att-stat-val {{ $totalMins > 0 ? 'stat-red' : '' }}">{{ $totalMins }}m</span>
                                    </div>
                                </td>

                                {{-- TOTAL CONVERTED --}}
                                <td style="text-align:center;">
                                    <span class="att-stat-val {{ $totalMins > 0 ? 'has-data' : '' }}" style="font-weight: 700; color: #475569;">
                                        {{ $totalConv }}
                                    </span>
                                </td>

                                {{-- Days W/ Pay --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-id="{{ $empId }}"
                                         data-tardy='@json($empWithPayLogs)'
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showAbsencePreview(this, 'With Pay Absences')"
                                         title="Click to view dates"
                                         onmouseover="if(this.dataset.tardy !== '[]') this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="days-circle">{{ $daysWithPay > 0 ? $daysWithPay : '—' }}</span>
                                    </div>
                                </td>

                                {{-- W.O.P. --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-id="{{ $empId }}"
                                         data-tardy='@json($empWopLogs)'
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showAbsencePreview(this, 'Without Pay Absences')"
                                         title="Click to view dates"
                                         onmouseover="if(this.dataset.tardy !== '[]') this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="wop-circle">{{ $wop > 0 ? $wop : '—' }}</span>
                                    </div>
                                </td>

                                {{-- Actions (Bento Menu) --}}
                                <td style="vertical-align: middle;">
                                    <div class="bento-menu-wrap">
                                        <button class="bento-btn" title="Actions">
                                            <i class="fas fa-th"></i>
                                        </button>
                                        <div class="bento-dropdown">
                                            <button class="bento-item bento-attendance" title="Attendance"
                                                data-name="{{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name ? substr(trim($emp->middle_name), 0, 1) . '.' : '' }}"
                                                data-remarks="{{ e($emp->remarks ?? '') }}"
                                                data-remarks-done="{{ ($emp->remarks_done ?? false) ? '1' : '0' }}"
                                                onclick="openActionsModal('{{ $empId }}', this.getAttribute('data-name'), this.getAttribute('data-remarks'), this.getAttribute('data-remarks-done'))">  
                                                <i class="fas fa-calendar-check"></i>
                                                <span>Attendance</span>
                                            </button>
                                            <button class="bento-item bento-print" title="Individual Report Preview"
                                                data-id="{{ $empId }}"
                                                data-name="{{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name ? substr(trim($emp->middle_name), 0, 1) . '.' : '' }}"
                                                onclick="openReportDateModal(this.getAttribute('data-id'), this.getAttribute('data-name'))">
                                                <i class="fas fa-file-invoice"></i>
                                                <span>Report</span>
                                            </button>
   
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="11">
                                    <i class="fas fa-users-slash"
                                        style="font-size:2.5rem;opacity:0.3;display:block;margin-bottom:10px;"></i>
                                    <p style="font-weight:600;margin:0;">No employees found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- .em-page --}}

    {{-- ══ MODALS ════════════════════════════════════════════════════ --}}

    {{-- Add Employee --}}
    <div id="addEmpModal" class="custom-overlay">
        <div class="custom-box" style="width:1000px; text-align:left; padding:30px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h2 style="margin:0;"><i class="fas fa-user-plus" style="color:#3b82f6;margin-right:8px;"></i>Add New Employee</h2>
                <button onclick="closeModal('addEmpModal')" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <p style="color:#94a3b8;font-size:0.8rem;margin-bottom:16px;">Manually fill in details or upload an Excel file.</p>

            {{-- Tabs --}}
            <div style="display:flex;gap:8px;margin-bottom:18px;border-bottom:2px solid #f1f5f9;padding-bottom:0;">
                <button type="button" id="tabManualBtn" onclick="switchAddTab('manual')"
                    style="padding:8px 18px;border:none;background:none;font-family:inherit;font-size:0.82rem;font-weight:700;color:#3b82f6;border-bottom:2px solid #3b82f6;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-user-edit"></i> Manual Entry
                </button>
                <button type="button" id="tabExcelBtn" onclick="switchAddTab('excel')"
                    style="padding:8px 18px;border:none;background:none;font-family:inherit;font-size:0.82rem;font-weight:700;color:#94a3b8;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-file-excel"></i> Upload Excel
                </button>
            </div>

            {{-- Manual Entry Tab --}}
            <div id="tabManual">
                <form id="addEmpForm" data-route="{{ route('admin.employee.add') }}" onsubmit="submitForm(event, this.getAttribute('data-route'), 'addEmpModal')">
                    <div class="form-row">
                        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required
                                placeholder="Dela Cruz"></div>
                        <div class="form-group"><label>First Name</label><input type="text" name="first_name" required
                                placeholder="Juan"></div>
                    </div>
                    <div class="form-group"><label>Middle Name</label><input type="text" name="middle_name"
                            placeholder="Santos"></div>
                    <div class="form-row">
                        <div class="form-group"><label>Employee No.</label><input type="text" name="emp_number" required
                                placeholder="EMP-001"></div>
                        <div class="form-group"><label>Station</label><input type="text" name="station"
                                placeholder="ICT Division"></div>
                        <div class="form-group" style="flex: 1;"><label>Official Time</label><input type="text" name="official_time"
                                placeholder="e.g. 08:00 - 17:00 (Leave blank if none)"></div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                        <button type="button" class="modal-btn modal-btn-cancel"
                            onclick="closeModal('addEmpModal')">Cancel</button>
                        <button type="submit" class="modal-btn modal-btn-primary"><i class="fas fa-save"></i> Save
                            Employee</button>
                    </div>
                </form>
            </div>

            {{-- Excel Upload Tab --}}
            <div id="tabExcel" style="display:none;">
                {{-- Info & Template Download --}}
                <div
                    style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:12px;">
                    <i class="fas fa-circle-info" style="color:#3b82f6;font-size:1.2rem;flex-shrink:0;"></i>
                    <div style="font-size:0.8rem;color:#1e40af;font-weight:600;line-height:1.5;">
                        Upload a <strong>.xlsx</strong> or <strong>.csv</strong> file.
                        Columns must be: <em>Last Name, First Name, Middle Name, Employee ID, Station</em>.
                    </div>
                </div>

                <a href="{{ route('admin.employee.template') }}" target="_blank"
                    style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:8px;font-size:0.8rem;font-weight:700;text-decoration:none;margin-bottom:16px;">
                    <i class="fas fa-download"></i> Download Template
                </a>

                {{-- Drop Zone --}}
                <div id="excelDropZone"
                    style="border:2px dashed #cbd5e1;border-radius:14px;padding:30px;text-align:center;color:#94a3b8;cursor:pointer;transition:all 0.2s;background:#f8fafc;"
                    onclick="document.getElementById('excelFileInput').click()"
                    ondragover="event.preventDefault();this.style.borderColor='#3b82f6';this.style.background='#eff6ff';"
                    ondragleave="this.style.borderColor='#cbd5e1';this.style.background='#f8fafc';"
                    ondrop="handleExcelDrop(event)">
                    <i class="fas fa-file-excel"
                        style="font-size:2.5rem;color:#22c55e;margin-bottom:10px;display:block;"></i>
                    <p style="font-weight:700;font-size:0.9rem;color:#374151;margin:0 0 4px;">Click or drag & drop your
                        Excel file here</p>
                    <p style="font-size:0.75rem;margin:0;">Supports .xlsx, .xls, .csv</p>
                </div>
                <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" style="display:none;"
                    onchange="handleExcelFile(this.files[0])">

                {{-- Selected File --}}
                <div id="excelFileInfo"
                    style="display:none;margin-top:12px;padding:10px 14px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:10px;">
                    <i class="fas fa-file-spreadsheet" style="color:#22c55e;font-size:1.1rem;"></i>
                    <span id="excelFileName" style="font-size:0.82rem;font-weight:700;color:#374151;flex:1;"></span>
                    <button type="button" onclick="clearExcelFile()"
                        style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:1rem;"><i
                            class="fas fa-times"></i></button>
                </div>

                {{-- Results --}}
                <div id="excelResults"
                    style="display:none;margin-top:12px;padding:12px 16px;border-radius:10px;font-size:0.82rem;font-weight:700;">
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px;">
                    <button type="button" class="modal-btn modal-btn-cancel"
                        onclick="closeModal('addEmpModal')">Cancel</button>
                    <button type="button" id="importExcelBtn" class="modal-btn modal-btn-green"
                        onclick="submitExcelImport()" style="opacity:0.5;pointer-events:none;">
                        <i class="fas fa-upload"></i> Import Employees
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Excel Preview Modal --}}
    <div id="excelPreviewModal" class="custom-overlay" style="display:none;">
        <div class="custom-box excel-preview-box">
            <div class="excel-preview-header">
                <div>
                    <h2 style="margin:0 0 4px;"><i class="fas fa-table" style="color:#22c55e;margin-right:8px;"></i>Preview Imported Employees</h2>
                    <p style="color:#64748b;font-size:0.8rem;margin:0;">Review and edit names before importing. All fields are editable.</p>
                </div>
                <button type="button" onclick="closeModal('excelPreviewModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;padding:4px;"><i class="fas fa-times"></i></button>
            </div>

            <div class="excel-preview-stats" id="previewStats"></div>

            <div class="excel-preview-table-wrap">
                <table class="excel-preview-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">#</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Employee ID</th>
                            <th>Station</th>
                            <th>Time</th>
                            <th style="width:36px;"></th>
                        </tr>
                    </thead>
                    <tbody id="previewTableBody"></tbody>
                </table>
            </div>

            <div class="excel-preview-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="backToUpload()">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <div style="flex:1; display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="modal-btn" style="background:#f1f5f9; color:#475569;" onclick="addPreviewRow()">
                        <i class="fas fa-plus"></i> Add Row
                    </button>
                    <button type="button" id="confirmImportBtn" class="modal-btn modal-btn-green" onclick="confirmExcelImport()">
                        <i class="fas fa-upload"></i> Confirm &amp; Import
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Result Modal --}}
    <div id="importResultModal" class="custom-overlay" style="display:none;">
        <div class="custom-box" style="max-width:440px;text-align:center;padding:48px 40px; position:relative;">
            <button onclick="closeModal('importResultModal')" style="position:absolute; top:20px; right:20px; border:none; background:#f1f5f9; width:30px; height:30px; border-radius:8px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>

            {{-- Loading State --}}
            <div id="importState_loading">
                <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#eff6ff,#dbeafe);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#3b82f6;"></i>
                </div>
                <h2 style="margin-bottom:8px;font-size:1.3rem;">Importing Employees…</h2>
                <p style="color:#64748b;font-size:0.85rem;margin:0;">Please wait while we process your file.</p>
            </div>

            {{-- Success State --}}
            <div id="importState_success" style="display:none;">
                <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#f0fdf4,#dcfce7);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;animation:fadeInUp 0.4s ease;">
                    <i class="fas fa-check-circle" style="font-size:2.5rem;color:#22c55e;"></i>
                </div>
                <h2 style="margin-bottom:8px;font-size:1.3rem;color:#15803d;">Import Successful!</h2>
                <p id="importSuccessMsg" style="color:#64748b;font-size:0.9rem;margin:0 0 16px;"></p>
                <div id="importSuccessErrors" style="display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 14px;text-align:left;font-size:0.78rem;color:#92400e;margin-bottom:16px;"></div>
                <div style="font-size:0.78rem;color:#94a3b8;">Page will refresh automatically…</div>
            </div>

            {{-- Failed State --}}
            <div id="importState_failed" style="display:none;">
                <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#fef2f2,#fee2e2);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;animation:fadeInUp 0.4s ease;">
                    <i class="fas fa-times-circle" style="font-size:2.5rem;color:#ef4444;"></i>
                </div>
                <h2 style="margin-bottom:8px;font-size:1.3rem;color:#b91c1c;">Import Failed</h2>
                <p id="importFailedMsg" style="color:#64748b;font-size:0.9rem;margin:0 0 20px;"></p>
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('importResultModal')" style="width:100%;">
                    Close
                </button>
            </div>

        </div>
    </div>

    {{-- Edit Employee --}}
    <div id="editEmpModal" class="custom-overlay">
        <div class="custom-box" style="width:800px; text-align:left; padding:30px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="margin:0;"><i class="fas fa-pen" style="color:#f59e0b;margin-right:8px;"></i>Edit Employee</h2>
                <button onclick="closeModal('editEmpModal')" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <form id="editEmpForm" data-route="{{ route('admin.employee.update') }}" onsubmit="submitForm(event, this.getAttribute('data-route'), 'editEmpModal')">
                <input type="hidden" name="id" id="edit_emp_id">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status" required>
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="INACTIVE">INACTIVE</option>
                        <option value="OTHERS">OTHERS…</option>
                    </select>
                </div>
                <div class="form-group" id="custom_status_div" style="display:none;">
                    <label>Specify Status</label>
                    <input type="text" name="custom_status" id="custom_status">
                </div>
                <div class="form-group">
                    <label>Official Time</label>
                    <input type="text" name="official_time" id="edit_official_time" placeholder="e.g. 08:00 - 17:00"
                        oninput="this.value = this.value.replace(/[a-zA-Z]/g, '')">
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                    <button type="button" class="modal-btn modal-btn-cancel"
                        onclick="closeModal('editEmpModal')">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn-primary"><i class="fas fa-check"></i> Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Attendance Actions (Late / Undertime / Absent / Present) --}}
    <div id="actionsModal" class="custom-overlay">
        <div class="custom-box" style="width:800px; height:600px; text-align:left; display:flex; flex-direction:column; padding:30px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <h2 style="margin:0;"><i class="fas fa-clock" style="color:#0369a1;margin-right:8px;"></i>Record Attendance</h2>
                <button type="button" onclick="closeModal('actionsModal')" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <p style="color:#64748b;font-weight:700;margin-bottom:16px;" id="action_emp_name"></p>
            
            <form id="unifiedAttendanceForm" onsubmit="submitUnifiedRecords(event)">
                <input type="hidden" name="emp_id" class="action_emp_id">
                
                <div class="att-tabs" style="margin-bottom: 20px;">
                    <button type="button" class="att-tab tab-late" onclick="showActionTab('late')"><i class="fas fa-clock"></i> Tardy</button>
                    <button type="button" class="att-tab tab-undertime" onclick="showActionTab('undertime')"><i class="fas fa-hourglass-half"></i> Undertime</button>
                    <button type="button" class="att-tab tab-absent" onclick="showActionTab('absent')"><i class="fas fa-user-slash"></i> Absent</button>
                    <button type="button" class="att-tab tab-present" onclick="showActionTab('present')"><i class="fas fa-check-double"></i> Mark All</button>
                    <button type="button" class="att-tab tab-remarks" onclick="showActionTab('remarks')"><i class="fas fa-comment-alt"></i> Remarks</button>
                </div>

                {{-- Late Section --}}
                <div id="lateSection" class="action-tab-content" style="display:flex; flex-direction:column;">
                    <div class="form-group">
                        <label style="display:flex;align-items:center;justify-content:space-between;">
                            <span>Date(s) & Minutes <span id="late_date_count" style="color:#64748b;font-weight:500;font-size:0.75rem;">(0 total mins)</span></span>
                            <button type="button" onclick="addLateDateRow()" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border:1.5px solid #e2e8f0;border-radius:6px;background:#f8fafc;color:#3b82f6;font-size:0.72rem;font-weight:700;cursor:pointer;">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </label>
                        <div id="lateDateRows" style="display:flex;flex-direction:column;gap:6px;max-height:140px;overflow-y:auto;padding-right:2px;"></div>
                    </div>
                    {{-- Tardy History --}}
                    <div style="border-top:1.5px dashed #fed7aa; padding-top:8px; margin-top:4px; flex:1; overflow:hidden; display:flex; flex-direction:column;">
                        <div style="font-size:0.68rem; font-weight:800; color:#b45309; text-transform:uppercase; margin-bottom:6px; display:flex; align-items:center; gap:5px;">
                            <i class="fas fa-history"></i> Tardy History
                        </div>
                        <div id="lateHistoryList" style="flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:5px; padding-right:2px; max-height:140px;">
                            <div style="color:#94a3b8;font-size:0.75rem;text-align:center;padding:10px 0;">Loading...</div>
                        </div>
                    </div>
                </div>

                {{-- Undertime Section --}}
                <div id="utSection" class="action-tab-content" style="display:none; flex-direction:column;">
                    <div class="form-group">
                        <label style="display:flex;align-items:center;justify-content:space-between;">
                            <span>Date(s) & Minutes <span id="ut_date_count" style="color:#64748b;font-weight:500;font-size:0.75rem;">(0 total mins)</span></span>
                            <button type="button" onclick="addUtDateRow()" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border:1.5px solid #e2e8f0;border-radius:6px;background:#f8fafc;color:#3b82f6;font-size:0.72rem;font-weight:700;cursor:pointer;">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </label>
                        <div id="utDateRows" style="display:flex;flex-direction:column;gap:6px;max-height:220px;overflow-y:auto;padding-right:2px;"></div>
                    </div>
                </div>

                {{-- Absent Section --}}
                <div id="absentSection" class="action-tab-content" style="display:none; flex-direction:column;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Pay Type</label>
                            <select name="pay_type">
                                <option value="" disabled selected>[Choose]</option>
                                <option value="Absence with pay">With Pay</option>
                                <option value="Absence without pay">Without Pay</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reason (Optional)</label>
                            <input type="text" name="reason" id="abs_reason_input" placeholder="e.g. Sick leave" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;justify-content:space-between;">
                            <span>Absence Date(s) <span id="abs_date_count" style="color:#64748b;font-weight:500;font-size:0.75rem;">(0 selected)</span></span>
                            <button type="button" onclick="openAbsCalModal()" style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border:1.5px solid #3b82f6;border-radius:8px;background:#eff6ff;color:#2563eb;font-size:0.75rem;font-weight:700;cursor:pointer;">
                                <i class="fas fa-calendar-alt"></i> Pick Dates
                            </button>
                        </label>
                        <div id="absHiddenDates"></div>
                        <div id="absSelectedChips" style="display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;min-height:36px;padding:6px;border:1.5px dashed #e2e8f0;border-radius:12px;background:#f8fafc;">
                            <span style="color:#94a3b8;font-size:0.78rem;align-self:center;">No dates selected yet</span>
                        </div>
                    </div>
                    {{-- Absent History --}}
                    <div style="border-top:1.5px dashed #fecaca; padding-top:8px; margin-top:4px; flex:1; overflow:hidden; display:flex; flex-direction:column;">
                        <div style="font-size:0.68rem; font-weight:800; color:#991b1b; text-transform:uppercase; margin-bottom:6px; display:flex; align-items:center; gap:5px;">
                            <i class="fas fa-history"></i> Absence History
                        </div>
                        <div id="absHistoryList" style="flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:5px; padding-right:2px; max-height:120px;">
                            <div style="color:#94a3b8;font-size:0.75rem;text-align:center;padding:10px 0;">Loading...</div>
                        </div>
                    </div>
                </div>

                {{-- Present Section --}}
                <div id="presentFormSection" class="action-tab-content" style="display:none; flex-direction:column;">
                    <div style="background:#f0f9ff; padding:15px; border-radius:12px; margin-bottom:16px; border:1px solid #bae6fd;">
                        <p style="color:#0369a1; font-size:0.85rem; margin:0; line-height:1.5; font-weight:600;">
                            <i class="fas fa-info-circle"></i> Marks employee present for all working days of the chosen month.
                        </p>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Year</label><input type="number" name="mark_present_year" min="2020" max="2099" value="{{ $year }}"></div>
                        <div class="form-group">
                            <label>Month</label>
                            <select name="mark_present_month" id="unifiedPresentMonth">
                                <option value="">Choose Month</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- All History --}}
                    <div style="border-top:1.5px dashed #d1d5db; padding-top:8px; margin-top:4px; flex:1; overflow:hidden; display:flex; flex-direction:column;">
                        <div style="font-size:0.68rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:6px; display:flex; align-items:center; gap:5px;">
                            <i class="fas fa-history"></i> All Attendance History
                        </div>
                        <div id="allHistoryList" style="flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:5px; padding-right:2px; max-height:160px;">
                            <div style="color:#94a3b8;font-size:0.75rem;text-align:center;padding:10px 0;">Loading...</div>
                        </div>
                    </div>
                </div>

                {{-- Remarks Section --}}
                <div id="remarksSection" class="action-tab-content" style="display:none; flex-direction:column; gap:0; overflow:hidden; flex:1;">

                    {{-- Active Remark Banner (shown when there's an active un-done remark) --}}
                    <div id="activeRemarkBanner" style="display:none; align-items:flex-start; gap:10px; background:#fff7ed; border:1.5px solid #fed7aa; border-radius:12px; padding:12px 14px; margin-bottom:10px;">
                        <i class="fas fa-exclamation-circle" style="color:#f59e0b; font-size:1rem; flex-shrink:0; margin-top:2px;"></i>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:0.7rem; font-weight:800; color:#b45309; text-transform:uppercase; margin-bottom:3px;">Active Remark</div>
                            <div id="activeRemarkText" style="font-size:0.82rem; color:#92400e; font-weight:600; line-height:1.5; word-break:break-word;"></div>
                        </div>
                        <button type="button" id="markDoneBtn" onclick="markRemarkDone()"
                            style="flex-shrink:0; display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:linear-gradient(135deg,#22c55e,#16a34a); color:white; border:none; border-radius:8px; font-size:0.72rem; font-weight:800; cursor:pointer; white-space:nowrap; box-shadow:0 2px 8px rgba(34,197,94,0.25); transition:all 0.2s;"
                            onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                            <i class="fas fa-check-circle"></i> Mark as Done
                        </button>
                    </div>

                    {{-- New Remark Input --}}
                    <div class="form-group" style="margin-bottom:8px;">
                        <label style="display:flex; align-items:center; gap:6px; color:#b45309; font-size:0.78rem; font-weight:800; text-transform:uppercase; margin-bottom:6px;">
                            <i class="fas fa-comment-medical"></i> Add New Remark
                        </label>
                        <textarea id="remarks_textarea" name="emp_remarks" rows="3"
                            placeholder="e.g. Under performance improvement plan, special monitoring required..."
                            style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid #fed7aa; outline:none; font-family:inherit; font-size:0.83rem; color:#1e293b; background:#fffbf5; resize:none; transition:border-color 0.2s, box-shadow 0.2s; box-sizing:border-box;"
                            onfocus="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 0 0 3px rgba(245,158,11,0.15)';"
                            onblur="this.style.borderColor='#fed7aa'; this.style.boxShadow='none';"></textarea>
                        <div style="display:flex; justify-content:flex-end; margin-top:6px;">
                            <button type="button" class="modal-btn modal-btn-amber" onclick="saveRemarks()" id="saveRemarksBtn" style="height:36px; font-size:0.78rem;">
                                <i class="fas fa-save"></i> Save Remark
                            </button>
                        </div>
                    </div>

                    {{-- History Timeline --}}
                    <div style="border-top:1.5px dashed #fed7aa; padding-top:10px; flex:1; overflow:hidden; display:flex; flex-direction:column;">
                        <div style="font-size:0.7rem; font-weight:800; color:#b45309; text-transform:uppercase; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-history"></i> Remarks History
                        </div>
                        <div id="remarksHistoryList" style="flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:6px; padding-right:2px;">
                            <div style="color:#94a3b8; font-size:0.78rem; text-align:center; padding:16px 0;">Loading...</div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:auto; padding-top:20px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #f1f5f9;">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('actionsModal')">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn-purple" id="saveAllAttBtn" style="min-width:240px; height:50px; font-size:1rem;">
                        <i class="fas fa-save" style="margin-right:8px;"></i> Save All Records
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- All Present Modal --}}
    <div id="allPresentModal" class="custom-overlay">
        <div class="custom-box" style="width:700px; height:600px; text-align:left; display:flex; flex-direction:column; justify-content:center; padding:40px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h2 style="margin:0;"><i class="fas fa-check-double" style="color:#8b5cf6;margin-right:8px;"></i>Mark All Present</h2>
                <button onclick="closeModal('allPresentModal')" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <p style="color:#64748b;font-size:0.82rem;margin-bottom:14px;">
                All <strong>active employees</strong> will be marked <strong>present</strong> for every working day of the
                selected month. Existing records will not be overwritten.
            </p>
            <div class="present-preview">
                <div class="present-preview-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="present-preview-info">
                    <strong id="allPresentMonthLabel">—</strong>
                    <span>All working days (Mon–Fri, excl. holidays)</span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Year</label><input type="number" id="allPresentYear" min="2020" max="2099"
                        value="{{ $year }}"></div>
                <div class="form-group">
                    <label>Month</label>
                    <select id="allPresentMonth">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:20px;">
                <button type="button" class="modal-btn modal-btn-purple" id="allPresentBtn" onclick="submitAllPresent()" style="width:100%; height:50px; font-size:1rem; font-weight:800; justify-content:center;">
                    <i class="fas fa-check-double"></i> Mark All &amp; View Calendar
                </button>
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('allPresentModal')" style="width:100%; height:45px; border:none; background:#f1f5f9; color:#64748b; font-weight:700;">
                    Dismiss &amp; Go Back
                </button>
            </div>
        </div>
    </div>

    {{-- Per-Employee Present Modal --}}
    <div id="empPresentModal" class="custom-overlay">
        <div class="custom-box" style="width:700px; height:600px; text-align:left; display:flex; flex-direction:column; justify-content:center; padding:40px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h2 style="margin:0;"><i class="fas fa-calendar-check" style="color:#10b981;margin-right:8px;"></i>Mark Present</h2>
                <button onclick="closeModal('empPresentModal')" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <p style="color:#64748b;font-weight:700;margin-bottom:10px;" id="empPresentName"></p>
            <p style="color:#64748b;font-size:0.8rem;margin-bottom:14px;">Marks employee present for all working days of the chosen month (existing records kept).</p>
            <input type="hidden" id="empPresentId">
            <div class="form-row">
                <div class="form-group"><label>Year</label><input type="number" id="empPresentYear" min="2020" max="2099"
                        value="{{ $year }}"></div>
                <div class="form-group">
                    <label>Month</label>
                    <select id="empPresentMonth">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:20px;">
                <button type="button" class="modal-btn modal-btn-green" id="empPresentBtn" onclick="submitEmpPresent()" style="width:100%; height:50px; font-size:1rem; font-weight:800; justify-content:center;">
                    <i class="fas fa-check"></i> Mark Present &amp; View Calendar
                </button>
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('empPresentModal')" style="width:100%; height:45px; border:none; background:#f1f5f9; color:#64748b; font-weight:700;">
                    Dismiss &amp; Go Back
                </button>
            </div>
        </div>
    </div>


    {{-- Absence Calendar Picker Modal --}}
    <div id="absCalModal" class="custom-overlay" style="z-index:9200; display:none;">
        <div class="custom-box" style="width:700px; height:540px; text-align:left; padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h3 style="margin:0;font-size:1.05rem;color:#1e293b;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-calendar-check" style="color:#ef4444;"></i> Select Absence Dates
                </h3>
                <button onclick="closeModal('absCalModal')" style="border:none;background:#f1f5f9;width:30px;height:30px;border-radius:8px;cursor:pointer;color:#64748b;font-size:1rem;"><i class="fas fa-times"></i></button>
            </div>
            {{-- Month Nav --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <button type="button" onclick="absCalPrev()" style="border:none;background:#f1f5f9;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#374151;font-size:0.85rem;"><i class="fas fa-chevron-left"></i></button>
                <span id="absCalMonthLabel" style="font-weight:800;font-size:0.95rem;color:#1e293b;"></span>
                <button type="button" onclick="absCalNext()" style="border:none;background:#f1f5f9;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#374151;font-size:0.85rem;"><i class="fas fa-chevron-right"></i></button>
            </div>
            {{-- Day headers --}}
            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:4px;">
                @foreach(['S','M','T','W','T','F','S'] as $dh)
                    <div style="text-align:center;font-size:0.65rem;font-weight:800;color:#94a3b8;padding:2px 0;">{{ $dh }}</div>
                @endforeach
            </div>
            {{-- Calendar grid --}}
            <div id="absCalGrid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;"></div>
            <div style="margin-top:14px;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('absCalModal')">Close</button>
                <button type="button" class="modal-btn modal-btn-red" onclick="applyAbsCalSelection()"><i class="fas fa-check"></i> Apply</button>
            </div>
        </div>
    </div>

    {{-- Tardy Details Modal (Standardized) --}}
    <div id="tardyPreviewModal" class="custom-overlay" style="z-index:9000; display:none;">
        <div class="custom-box" style="width: 800px; height: 600px; text-align:left; border-radius: 20px; padding: 25px; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 style="margin:0; color:#1e293b; display:flex; align-items:center; gap:10px; font-size: 1.2rem; font-weight: 850;">
                    <i class="fas fa-history" style="color:#ef4444;"></i> <span id="tardyPreviewTitle">Tardy Records</span>
                </h3>
                <button onclick="closeModal('tardyPreviewModal')" style="border:none; background:#f1f5f9; width:30px; height:30px; border-radius:8px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            
            <input type="hidden" id="tardyPreviewEmpId">
            <p id="tardyPreviewName" style="color:#64748b; font-size:0.85rem; margin-bottom:20px; font-weight:750; background:#f8fafc; padding:10px 16px; border-radius:10px; border:1px solid #f1f5f9;"></p>
            
            <div id="tardyPreviewList" style="flex:1; overflow-y:auto; padding-right:5px; margin-bottom:15px;">
                <!-- Rendered by JS -->
            </div>
            
            <div style="text-align:right; border-top:1px solid #f1f5f9; padding-top:15px;">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('tardyPreviewModal')" style="min-width:100px;">Close Window</button>
            </div>
        </div>
    </div>

    {{-- Individual Report Date Span Modal --}}
    <div id="reportDateModal" class="custom-overlay" style="z-index:9150; display:none;">
        <div class="custom-box" style="width: 420px; text-align:left; border-radius: 24px; padding: 30px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0; color:#1e293b; display:flex; align-items:center; gap:10px; font-size: 1.25rem; font-weight: 850;">
                    <i class="fas fa-calendar-alt" style="color:#6366f1;"></i> Select Date Span
                </h3>
                <button onclick="closeModal('reportDateModal')" style="border:none; background:#f8fafc; width:36px; height:36px; border-radius:12px; cursor:pointer; color:#94a3b8; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'; this.style.color='#ef4444';" onmouseout="this.style.background='#f8fafc'; this.style.color='#94a3b8';">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <input type="hidden" id="reportDateEmpId">
            <p id="reportDateEmpName" style="color:#64748b; font-size:0.85rem; margin-bottom:25px; font-weight:750; background:#f1f5f9; padding:12px 18px; border-radius:14px; border:1px solid #e2e8f0;"></p>
            
            <div style="display:flex; flex-direction:column; gap:20px; margin-bottom:30px;">
                <div class="form-group">
                    <label style="display:block; font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:8px; margin-left:4px;">Start Date</label>
                    <input type="date" id="reportStartDate" class="custom-input" 
                           style="width:100%; height:50px; border-radius:14px; border:2px solid #e2e8f0; padding:0 18px; font-weight:700; color:#1e293b; background:#fcfdfe; transition:all 0.2s;"
                           onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 4px rgba(99, 102, 241, 0.1)';"
                           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                           value="{{ date('Y-m-01') }}">
                </div>
                
                <div class="form-group">
                    <label style="display:block; font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:8px; margin-left:4px;">End Date</label>
                    <input type="date" id="reportEndDate" class="custom-input" 
                           style="width:100%; height:50px; border-radius:14px; border:2px solid #e2e8f0; padding:0 18px; font-weight:700; color:#1e293b; background:#fcfdfe; transition:all 0.2s;"
                           onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 4px rgba(99, 102, 241, 0.1)';"
                           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                           value="{{ date('Y-m-t') }}">
                </div>
            </div>
            
            <div style="display:flex; gap:12px;">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('reportDateModal')" style="flex:1; height:52px; border-radius:16px;">Cancel</button>
                <button type="button" class="modal-btn" onclick="previewIndividualReportSpan()" 
                        style="flex:2; height:52px; border-radius:16px; background:linear-gradient(135deg, #6366f1, #4f46e5); color:white; font-weight:850; border:none; box-shadow:0 10px 15px -3px rgba(79, 70, 229, 0.3); transition:all 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                    <i class="fas fa-eye" style="margin-right:8px;"></i> Preview Report
                </button>
            </div>
        </div>
    </div>

    {{-- Individual Report Preview Modal --}}
    <div id="reportPreviewModal" class="custom-overlay" style="z-index:9100; display:none;">
        <div class="custom-box" style="width: 800px; height: 650px; text-align:left; border-radius: 24px; padding: 25px; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 style="margin:0; color:#1e293b; display:flex; align-items:center; gap:10px; font-size: 1.35rem; font-weight: 850;">
                    <i class="fas fa-file-invoice" style="color:#8b5cf6;"></i> Report Preview
                </h3>
                <button onclick="closeModal('reportPreviewModal')" style="border:none; background:#f1f5f9; width:32px; height:32px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            
            <p id="preview_emp_name" style="color:#64748b; font-size:0.9rem; margin-bottom:20px; font-weight:750; background:#f8fafc; padding:10px 16px; border-radius:12px; border:1px solid #edf2f7;"></p>
            
            <div style="display:flex; flex-direction:column; gap:18px; max-height:450px; overflow-y:auto; padding-right:8px; margin-bottom:20px;">
                <!-- Present Section -->
                <div class="report-section">
                    <label style="display:flex; align-items:center; gap:8px; font-size: 0.75rem; font-weight: 850; color: #16a34a; text-transform: uppercase; margin-bottom: 8px;">
                        <i class="fas fa-check-circle"></i> Present Days (<span id="preview_pres_count">0</span>)
                    </label>
                    <div id="preview_pres_list" class="report-pills"></div>
                </div>

                <!-- Absent Section -->
                <div class="report-section">
                    <label style="display:flex; align-items:center; gap:8px; font-size: 0.75rem; font-weight: 850; color: #ef4444; text-transform: uppercase; margin-bottom: 8px;">
                        <i class="fas fa-user-slash"></i> Absences (<span id="preview_abs_count">0</span>)
                    </label>
                    <div id="preview_abs_list" class="report-pills"></div>
                </div>

                <!-- Tardy Section -->
                <div class="report-section">
                    <label style="display:flex; align-items:center; gap:8px; font-size: 0.75rem; font-weight: 850; color: #f59e0b; text-transform: uppercase; margin-bottom: 8px;">
                        <i class="fas fa-clock"></i> Tardy Records (<span id="preview_tardy_count">0</span>)
                    </label>
                    <div id="preview_tardy_list" style="display:flex; flex-direction:column; gap:6px;"></div>
                </div>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1.5fr; gap:10px; margin-top:20px;">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('reportPreviewModal')" style="height: 48px; font-weight: 800; border-radius:14px;">Cancel</button>
                <button onclick="openSignatoriesModal()" class="modal-btn" style="height: 48px; background: #eff6ff; border: 2px solid #dbeafe; color: #2563eb; font-weight: 850; border-radius:14px;">
                    <i class="fas fa-pen-nib"></i> Signatories
                </button>
                <button onclick="printReportFromPreview()" class="modal-btn" style="height: 48px; background: #f8fafc; border: 2px solid #e2e8f0; color: #475569; font-weight: 850; border-radius:14px;">
                    <i class="fas fa-print"></i> Print Preview
                </button>
                <button id="preview_print_btn" class="modal-btn modal-btn-purple" style="height: 48px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); font-weight: 850; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3); border-radius:14px;">
                    <i class="fas fa-file-pdf"></i> Generate PDF
                </button>
            </div>
        </div>
    </div>

    {{-- Custom Confirmation Modal --}}
    <div id="customConfirmModal" class="custom-overlay" style="z-index:15000; display:none;">
        <div class="custom-box" style="width:400px; text-align:left; padding:30px; border-radius:24px; position:relative;">
            <button onclick="closeModal('customConfirmModal')" style="position:absolute; top:20px; right:20px; border:none; background:#f1f5f9; width:30px; height:30px; border-radius:8px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            <div style="width:64px; height:64px; background:#fee2e2; color:#ef4444; border-radius:20px; display:flex; align-items:center; justify-content:center; margin-bottom:20px; font-size:1.8rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 id="confirmTitle" style="margin-bottom:10px; color:#1e293b; font-size:1.4rem; font-weight:850;">Are you sure?</h2>
            <p id="confirmMessage" style="color:#64748b; font-size:0.92rem; margin-bottom:30px; line-height:1.6; font-weight:700;">This action will permanently delete this record. This cannot be undone.</p>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <button id="confirmCancelBtn" class="modal-btn modal-btn-cancel" style="height:48px; border-radius:12px; font-weight:850; background:#f1f5f9; border:none; color:#64748b; cursor:pointer;">No, Cancel</button>
                <button id="confirmConfirmBtn" class="modal-btn modal-btn-red" style="height:48px; border-radius:12px; font-weight:850; background:linear-gradient(135deg, #ef4444, #dc2626); box-shadow:0 4px 15px rgba(239,68,68,0.3); border:none; color:white; cursor:pointer;">Yes, Delete</button>
            </div>
        </div>
    </div>

    {{-- Individual Report Signatories Modal --}}
    <div id="signatoriesModal" class="custom-overlay" style="z-index:9200; display:none;">
        <div class="custom-box" style="width:1000px; text-align:left; padding:30px; border-radius:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0; color:#1e293b; display:flex; align-items:center; gap:10px; font-size: 1.35rem; font-weight: 850;">
                    <i class="fas fa-pen-nib" style="color:#2563eb;"></i> Report Signatories
                </h3>
                <button onclick="closeModal('signatoriesModal')" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            
            <form id="signatoriesForm">
                <p style="color:#64748b; font-size:0.85rem; margin-bottom:20px;">Enter the names and positions for the signatures in this individual report.</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; border-top: 2px solid #f1f5f9; padding-top: 20px;">
                    {{-- Prepared By --}}
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Prepared By</div>
                        <div class="form-group">
                            <label>Signatory Name</label>
                            <input type="text" id="reportSigPrepName" placeholder="CHRISTINE JOY C. MAAPOY" style="font-weight:700; text-transform:uppercase;">
                        </div>
                        <div id="report-prep-pos1">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                Position Line 1
                                <button type="button" onclick="addPosField('report', 'prep')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                            </label>
                            <input type="text" id="reportSigPrepPos" placeholder="Administrative Assistant III">
                        </div>
                        <div id="report-prep-pos2" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                Position Line 2
                                <button type="button" onclick="removePosField('report', 'prep', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="reportSigPrepPos2" placeholder="E-Form7 In-Charge">
                        </div>
                        <div id="report-prep-pos3" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                Position Line 3
                                <button type="button" onclick="removePosField('report', 'prep', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="reportSigPrepPos3" placeholder="...">
                        </div>
                    </div>

                    {{-- Verified Correct By --}}
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Verified Correct By</div>
                        <div class="form-group">
                            <label>Signatory Name</label>
                            <input type="text" id="reportSigVerName" placeholder="ROSELYN B. SENCIL" style="font-weight:700; text-transform:uppercase;">
                        </div>
                        <div id="report-ver-pos1">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                Position Line 1
                                <button type="button" onclick="addPosField('report', 'ver')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                            </label>
                            <input type="text" id="reportSigVerPos" placeholder="HRMO V">
                        </div>
                        <div id="report-ver-pos2" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                Position Line 2
                                <button type="button" onclick="removePosField('report', 'ver', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="reportSigVerPos2" placeholder="Administrative Officer V">
                        </div>
                        <div id="report-ver-pos3" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                Position Line 3
                                <button type="button" onclick="removePosField('report', 'ver', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="reportSigVerPos3" placeholder="...">
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:30px;">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal('signatoriesModal')">Close</button>
                    <button type="button" class="modal-btn" onclick="closeModal('signatoriesModal')" style="background:linear-gradient(135deg, #22c55e, #16a34a); color:white; font-weight:850; border:none; box-shadow:0 10px 15px -3px rgba(34, 197, 74, 0.3); padding: 0 30px;">
                        <i class="fas fa-check"></i> Applied
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Success Popup Modal --}}
    <div id="successModal" class="custom-overlay" style="z-index:20000; display:none;">
        <div class="custom-box" style="max-width:320px; text-align:center; padding:30px; border-radius:24px; position:relative;">
            <button onclick="handleSuccessClose()" style="position:absolute; top:20px; right:20px; border:none; background:#f1f5f9; width:28px; height:28px; border-radius:8px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            <div style="width:80px; height:80px; background:#dcfce3; color:#16a34a; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:2.5rem; animation: bounceIn 0.5s ease;">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="margin-bottom:10px; color:#1e293b; font-size:1.5rem; font-weight:850;">Success!</h2>
            <p id="successMessage" style="color:#64748b; font-size:0.9rem; margin-bottom:25px; line-height:1.5;">Data has been successfully saved.</p>
            <button onclick="handleSuccessClose()" class="modal-btn modal-btn-green" style="width:100%; height:48px; font-weight:850; border-radius:14px; background:linear-gradient(135deg, #22c55e, #16a34a); box-shadow:0 4px 15px rgba(22,163,74,0.3);">
                Continue
            </button>
        </div>
    </div>

    <script type="application/json" id="empIdsData">
        {!! json_encode(collect($employees)->filter(function($emp) { return ($emp->status ?? 'ACTIVE') === 'ACTIVE'; })->pluck('id')->values()) !!}
    </script>

@endsection

@section('scripts')
    <script>
        let lastReportData = null;

        function openSignatoriesModal() {
            document.getElementById('signatoriesModal').style.display = 'flex';
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

        function printReportFromPreview() {
            if (!lastReportData) return;
            const data = lastReportData;
            const start = document.getElementById('reportStartDate').value;
            const end = document.getElementById('reportEndDate').value;
            
            const printWindow = window.open('', '_blank');
            
            // Collect all logs for a main table
            let allLogs = [];
            data.present.forEach(d => allLogs.push({ day: d, status: 'Present', details: '-' }));
            data.absent.forEach(log => allLogs.push({ day: log.day || log, status: 'Absent', details: log.type || 'No reason' }));
            data.tardy.forEach(log => allLogs.push({ day: log.day, status: log.type, details: log.mins + ' mins' }));
            
            // Sort by day
            allLogs.sort((a,b) => a.day - b.day);

            // Signatories setup
            const prepName = document.getElementById('reportSigPrepName').value || document.getElementById('reportSigPrepName').placeholder;
            const prepPos1 = document.getElementById('reportSigPrepPos').value || document.getElementById('reportSigPrepPos').placeholder;
            const prepPos2 = document.getElementById('reportSigPrepPos2').value;
            const prepPos3 = document.getElementById('reportSigPrepPos3').value;
            
            const certName = '';
            const certPos1 = '';
            const certPos2 = '';
            const certPos3 = '';

            const verName = document.getElementById('reportSigVerName').value || document.getElementById('reportSigVerName').placeholder;
            const verPos1 = document.getElementById('reportSigVerPos').value || document.getElementById('reportSigVerPos').placeholder;
            const verPos2 = document.getElementById('reportSigVerPos2').value;
            const verPos3 = document.getElementById('reportSigVerPos3').value;

            const html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Individual Attendance Report - ${data.name}</title>
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap');
                        body { font-family: 'Times New Roman', serif; padding: 50px; color: #000; line-height: 1.4; font-size: 12px; }
                        .report-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                        .report-header h1 { font-size: 18px; margin: 0 0 5px; text-transform: uppercase; font-weight: 800; }
                        .report-header p { margin: 0; font-size: 13px; font-weight: 700; }
                        
                        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; border: 1px solid #000; }
                        .info-table td { padding: 8px 12px; border: 1px solid #000; }
                        .info-label { background: #f2f2f2; font-weight: 700; width: 25%; }
                        
                        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border: 1px solid #000; margin-bottom: 25px; }
                        .summary-item { border: 1px solid #000; padding: 10px; text-align: center; }
                        .summary-val { font-size: 16px; font-weight: 800; display: block; }
                        .summary-lbl { font-size: 10px; text-transform: uppercase; font-weight: 700; }

                        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
                        .data-table th { padding: 8px; border: 1px solid #000; background: #f2f2f2; font-size: 11px; text-transform: uppercase; }
                        .data-table td { padding: 6px 10px; border: 1px solid #000; text-align: center; }
                        .status-present { color: #000; }
                        .status-absent { font-weight: 700; }
                        
                        .footer-section { margin-top: 50px; display: flex; justify-content: space-between; gap: 30px; }
                        .sig-block { flex: 1; text-align: center; }
                        .sig-title { margin-bottom: 30px; font-weight: 500; font-size: 11px; }
                        .sig-line { border-bottom: 1.5px solid #000; padding-bottom: 3px; font-weight: 800; text-transform: uppercase; font-size: 13px; margin-bottom: 4px; }
                        .sig-pos { font-size: 10px; font-weight: 500; color: #374151; line-height: 1.2; }
                        
                        @media print { 
                            body { padding: 20px; } 
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="report-header">
                        <h1>Individual Attendance Summary Report</h1>
                        <p>SDO Quezon City Personnel System</p>
                    </div>

                    <table class="info-table">
                        <tr>
                            <td class="info-label">Name of Employee</td>
                            <td colspan="3"><strong>${data.name.toUpperCase()}</strong></td>
                        </tr>
                        <tr>
                            <td class="info-label">Period Covered</td>
                            <td>${start} to ${end}</td>
                            <td class="info-label">Date Generated</td>
                            <td>${new Date().toLocaleDateString('en-US', { dateStyle: 'long' })}</td>
                        </tr>
                    </table>

                    <div class="summary-grid">
                        <div class="summary-item"><span class="summary-val">${data.present.length}</span><span class="summary-lbl">Total Present</span></div>
                        <div class="summary-item"><span class="summary-val">${data.absent.length}</span><span class="summary-lbl">Total Absences</span></div>
                        <div class="summary-item"><span class="summary-val">${data.tardy.filter(x=>x.type==='Late').length}</span><span class="summary-lbl">Lates</span></div>
                        <div class="summary-item"><span class="summary-val">${data.tardy.filter(x=>x.type==='Undertime').length}</span><span class="summary-lbl">Undertimes</span></div>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Day of Month</th>
                                <th style="width: 35%;">Attendance Status</th>
                                <th style="width: 50%;">Remarks / Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${allLogs.length ? allLogs.map(log => `
                                <tr>
                                    <td>Day ${log.day}</td>
                                    <td><strong>${log.status}</strong></td>
                                    <td>${log.details}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="3">No records found for this period.</td></tr>'}
                        </tbody>
                    </table>

                    <div class="footer-section">
                        <div class="sig-block">
                            <div class="sig-title">Prepared by:</div>
                            <div class="sig-line">${prepName.toUpperCase()}</div>
                            <div class="sig-pos">${prepPos1}</div>
                            ${prepPos2 ? `<div class="sig-pos">${prepPos2}</div>` : ''}
                            ${prepPos3 ? `<div class="sig-pos">${prepPos3}</div>` : ''}
                        </div>
                        <div class="sig-block">
                            <div class="sig-title">Verified Correct By:</div>
                            <div class="sig-line">${verName.toUpperCase()}</div>
                            <div class="sig-pos">${verPos1}</div>
                            ${verPos2 ? `<div class="sig-pos">${verPos2}</div>` : ''}
                            ${verPos3 ? `<div class="sig-pos">${verPos3}</div>` : ''}
                        </div>
                    </div>

                    <script>
                        window.onload = function() {
                            setTimeout(() => {
                                window.print();
                                window.close();
                            }, 500);
                        };
                    <\/script>
                </body>
                </html>
            `;
            printWindow.document.write(html);
            printWindow.document.close();
        }

        function openReportDateModal(empId, empName) {
            document.getElementById('reportDateEmpId').value = empId;
            document.getElementById('reportDateEmpName').innerText = 'Employee: ' + empName;
            openModal('reportDateModal');
        }

        async function previewIndividualReportSpan() {
            const empId = document.getElementById('reportDateEmpId').value;
            const start = document.getElementById('reportStartDate').value;
            const end = document.getElementById('reportEndDate').value;
            const name = document.getElementById('reportDateEmpName').innerText.replace('Employee: ', '');

            if (!start || !end) {
                alert("Please select both start and end dates.");
                return;
            }

            try {
                // Show loading state or just close modal
                closeModal('reportDateModal');
                
                const resp = await fetch(`{{ route('api.individual.summary') }}?emp_id=${empId}&start_date=${start}&end_date=${end}`);
                const result = await resp.json();

                if (result.success) {
                    const data = result.data;
                    const url = `{{ route('admin.calendar') }}?mode=individual&emp_id=${empId}&start_date=${start}&end_date=${end}`;
                    
                    renderReportPreview({
                        name: name,
                        present: data.present_days,
                        absent: data.absent_detailed,
                        tardy: data.late_logs.concat(data.ut_logs),
                        url: url
                    });
                } else {
                    alert("Error: " + result.message);
                }
            } catch (err) {
                console.error("FETCH REPORT ERROR:", err);
                alert("Failed to load report data.");
            }
        }

        function renderReportPreview(data) {
            document.getElementById('preview_emp_name').innerText = 'Employee: ' + data.name;
            document.getElementById('preview_pres_count').innerText = data.present.length;
            document.getElementById('preview_abs_count').innerText = data.absent.length;
            document.getElementById('preview_tardy_count').innerText = data.tardy.length;

            var presCont = document.getElementById('preview_pres_list');
            presCont.innerHTML = data.present.length ? '' : '<span style="color:#94a3b8; font-size:0.75rem;">None</span>';
            data.present.sort((a,b) => a - b).forEach(day => {
                presCont.innerHTML += '<span class="report-pill pill-pres">Day ' + day + '</span>';
            });

            var absCont = document.getElementById('preview_abs_list');
            absCont.innerHTML = (data.absent && data.absent.length) ? '' : '<span style="color:#94a3b8; font-size:0.75rem;">None</span>';
            if (data.absent) {
                data.absent.sort((a,b) => (a.day||a) - (b.day||b)).forEach(log => {
                    var day = log.day || log;
                    var typeLabel = log.type ? ' (' + log.type + ')' : '';
                    var titleAttr = log.type ? 'title="' + log.type + '"' : '';
                    absCont.innerHTML += '<span class="report-pill pill-abs" ' + titleAttr + '>Day ' + day + typeLabel + '</span>';
                });
            }

            var tardyCont = document.getElementById('preview_tardy_list');
            tardyCont.innerHTML = data.tardy.length ? '' : '<div style="color:#94a3b8; font-size:0.75rem; padding:10px;">None</div>';
            data.tardy.forEach(log => {
                var isLate = log.type === 'Late';
                var color = isLate ? '#b45309' : '#0369a1';
                var bg = isLate ? '#fef3c7' : '#e0f2fe';
                tardyCont.innerHTML += '<div style="background:'+bg+'; padding:8px 12px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">' +
                    '<span style="font-weight:800; color:'+color+'; font-size:0.75rem;">Day ' + log.day + ' (' + log.type + ')</span>' +
                    '<span style="font-weight:900; color:'+color+'; font-size:0.8rem;">' + log.mins + 'm</span>' +
                    '</div>';
            });

            document.getElementById('preview_print_btn').onclick = function() { window.open(data.url, '_blank'); };
            lastReportData = data;
            openModal('reportPreviewModal');
        }

        function openIndividualReportPreview(el) {
            try {
                var name = el.getAttribute('data-name') || 'Unknown';
                var tardy = JSON.parse(el.getAttribute('data-tardy') || '[]');
                var absent = JSON.parse(el.getAttribute('data-absent') || '[]');
                var present = JSON.parse(el.getAttribute('data-present') || '[]');
                var url = el.getAttribute('data-url');

                renderReportPreview({
                    name: name,
                    tardy: tardy,
                    absent: absent,
                    present: present,
                    url: url
                });
            } catch (err) {
                console.error("REPORT PREVIEW ERROR:", err);
                alert("Error loading report preview: " + err.message);
            }
        }

        function showTardyPreview(el, title = 'Tardy Records') {
            const jsonStr = el.dataset.tardy;
            if(!jsonStr || jsonStr === '[]') return;
            const empName = el.dataset.name;
            const empId = el.dataset.id;
            
            const empIdField = document.getElementById('tardyPreviewEmpId');
            if(empIdField) empIdField.value = empId;
            
            document.getElementById('tardyPreviewName').innerText = 'Employee: ' + empName;
            const titleEl = document.getElementById('tardyPreviewTitle');
            if(titleEl) titleEl.innerText = title;
            
            const listCont = document.getElementById('tardyPreviewList');
            listCont.innerHTML = '';
            
            try {
                const logs = JSON.parse(jsonStr);
                if(logs.length > 0) {
                    let html = '<div style="display:flex; flex-direction:column; gap:8px;">';
                    logs.forEach(log => {
                        const isLate = log.type === 'Late';
                        const color = isLate ? '#b45309' : '#0369a1';
                        const bg = isLate ? '#fef3c7' : '#e0f2fe';
                        const icon = isLate ? 'fa-sign-in-alt' : 'fa-sign-out-alt';
                        
                        html += `
                            <div style="display:flex; justify-content:space-between; align-items:center; background:${bg}; padding:10px 14px; border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,0.5); display:flex; justify-content:center; align-items:center; color:${color};">
                                        <i class="fas ${icon}"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:800; color:${color}; font-size:0.85rem;">Day ${log.day}</div>
                                        <div style="font-size:0.68rem; color:${color}; opacity:0.85; font-weight:700; text-transform: uppercase;">${log.type}</div>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="font-weight:900; color:${color}; font-size:0.95rem; font-family: monospace;">
                                        ${log.mins} <span style="font-size: 0.65rem; opacity: 0.8;">MINS</span>
                                    </div>
                                    <button type="button" onclick="deleteTardyLog('${log.id}', '${log.type}', this)"
                                            style="background:rgba(239, 68, 68, 0.1); color:#ef4444; border:none; width:28px; height:28px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; font-size:0.75rem;"
                                            onmouseover="this.style.background='#ef4444'; this.style.color='white';"
                                            onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444';"
                                            title="Delete Record">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    listCont.innerHTML = html;
                }
            } catch(e) {
                console.error(e);
                listCont.innerHTML = '<div style="color:red; font-size:0.8rem;">Error loading records.</div>';
            }
            
            document.getElementById('tardyPreviewModal').style.display = 'flex';
        }

        function showAbsencePreview(el, title = 'Absence Records') {
            const jsonStr = el.dataset.tardy;
            if(!jsonStr || jsonStr === '[]') return;
            const empName = el.dataset.name;
            const empId = el.dataset.id;
            
            const empIdField = document.getElementById('tardyPreviewEmpId');
            if(empIdField) empIdField.value = empId;

            document.getElementById('tardyPreviewName').innerText = 'Employee: ' + empName;
            const titleEl = document.getElementById('tardyPreviewTitle');
            if(titleEl) titleEl.innerText = title;
            
            const listCont = document.getElementById('tardyPreviewList');
            listCont.innerHTML = '';
            
            try {
                const logs = JSON.parse(jsonStr);
                if(!logs || logs.length === 0) return;
                let html = '<div style="display:flex; flex-direction:column; gap:8px;">';
                logs.forEach(log => {
                    const isWop = log.type === 'Without Pay';
                    const color = isWop ? '#991b1b' : '#166534';
                    const bg = isWop ? '#fee2e2' : '#dcfce3';
                    const dStr = `{{ $year }}-${String({{ $month }}).padStart(2,'0')}-${String(log.day).padStart(2,'0')}`;
                    
                    html += `
                        <div style="background:${bg}; border-radius:12px; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.4); display:flex; justify-content:center; align-items:center; color:${color};">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <div>
                                    <div style="font-weight:800; color:${color}; font-size:0.9rem;">Day ${log.day}</div>
                                    <div style="font-size:0.75rem; color:${color}; opacity:0.8; font-weight:700; text-transform: uppercase;">Absence</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:15px;">
                                <div style="font-weight:900; color:${color}; font-size:1.05rem;">${log.type}</div>
                                <button type="button" onclick="deleteAbsenceLog('${dStr}', this)"
                                        style="background:rgba(239, 68, 68, 0.1); color:#ef4444; border:none; width:28px; height:28px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s;"
                                        onmouseover="this.style.background='#ef4444'; this.style.color='white';"
                                        onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444';"
                                        title="Delete Absence">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                listCont.innerHTML = html + '</div>';
            } catch(e) { console.error(e); }
            
            document.getElementById('tardyPreviewModal').style.display = 'flex';
        }

        async function deleteAbsenceLog(dateStr, btn) {
            const empId = document.getElementById('tardyPreviewEmpId').value;
            showCustomConfirm('Delete Absence?', 'Are you sure you want to delete this absence record? This will mark the employee as Present.', async () => {
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                const fd = new FormData();
                fd.append('emp_id', empId);
                fd.append('abs_date', dateStr);

                try {
                    const res = await fetch('{{ route("admin.employee.absent.delete") }}', { 
                        method: 'POST', 
                        headers: { 'X-CSRF-TOKEN': getCsrfToken() }, 
                        body: fd 
                    });
                    const result = await res.json();
                    if(result.success) {
                        showSuccess('Absence has been deleted and employee marked as Present.', true);
                    } else {
                        alert('Error: ' + result.message);
                        btn.innerHTML = oldHtml;
                        btn.disabled = false;
                    }
                } catch (e) {
                    alert('Request failed');
                    btn.innerHTML = oldHtml;
                    btn.disabled = false;
                }
            });
        }

        async function deleteTardyLog(id, type, btn) {
            const empId = document.getElementById('tardyPreviewEmpId').value;
            showCustomConfirm('Delete ' + type + '?', 'Are you sure you want to delete this ' + type.toLowerCase() + ' record? This will mark the employee as Present.', async () => {
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                const route = type === 'Late' ? '{!! route("admin.employee.late.delete") !!}' : '{!! route("admin.employee.undertime.delete") !!}';
                const fd = new FormData();
                fd.append('id', id);
                fd.append('emp_id', empId);

                try {
                    const res = await fetch(route, { 
                        method: 'POST', 
                        headers: { 'X-CSRF-TOKEN': getCsrfToken() }, 
                        body: fd 
                    });
                    const result = await res.json();
                    if(result.success) {
                        showSuccess(type + ' record has been deleted.', true);
                    } else {
                        alert('Error: ' + result.message);
                        btn.innerHTML = oldHtml;
                        btn.disabled = false;
                    }
                } catch (e) {
                    alert('Request failed');
                    btn.innerHTML = oldHtml;
                    btn.disabled = false;
                }
            });
        }

        /* ── URL Navigation ───────────────────────────────────────────        */
        function goToMonth(val) {
            const [y, m] = val.split('-');
            window.location.href = "{!! url('admin/index') !!}?mode={{ $mode }}&year=" + y + "&month=" + parseInt(m);
        }

        function goToMode(mode) {
            window.location.href = "{!! url('admin/index') !!}?mode=" + mode + "&year={{ $year }}&month={{ $month }}";
        }

        /* ── Search & Filter ────────────────────────────────────────── */
        const searchInput = document.getElementById('searchInput');
        const suggestionsBox = document.getElementById('searchSuggestions');
        const rows = document.querySelectorAll('.emp-row');

        if (searchInput) {
            searchInput.addEventListener('input', filterRows);
        }

        function selectSuggestion(name, id = '') {
            if (!searchInput || !suggestionsBox) return;
            searchInput.value = name;
            suggestionsBox.style.display = 'none';
            if (id) {
                rows.forEach(row => {
                    const isMatch = row.dataset.search.includes(id.toLowerCase());
                    row.style.display = isMatch ? '' : 'none';
                });
                checkEmptyState();
            } else {
                filterRows();
            }
        }

        window.addEventListener('click', function (e) {
            if (suggestionsBox && !e.target.closest('.pill-container')) {
                suggestionsBox.style.display = 'none';
            }
        });

        function filterRows() {
            if (!searchInput || !suggestionsBox) return;
            const q = searchInput.value.toLowerCase().replace(/,/g, '').trim();
            const words = q.split(/\s+/).filter(w => w.length > 0);
            let visibleCount = 0;

            // Clear previous suggestions
            let suggestionsHtml = '';
            let suggestionsCount = 0;

            rows.forEach(row => {
                const searchData = row.dataset.search || '';
                // Multiple word matching (all words must be present)
                const isVisible = words.every(w => searchData.includes(w));
                
                row.style.display = isVisible ? '' : 'none';
                if (isVisible) {
                    visibleCount++;
                    
                    // Suggest only if user typed something and we need more suggestions
                    if (q.length > 1 && suggestionsCount < 5) {
                        const nameEl = row.querySelector('.emp-profile-name');
                        const idEl = row.querySelector('.emp-profile-id');
                        const name = nameEl?.innerText.trim() || 'Unknown';
                        const id = idEl?.innerText.replace('ID: ', '').trim() || '';
                        
                        suggestionsHtml += `<div class="suggestion-item" onclick="selectSuggestion('${name.replace(/'/g, "\\'")}', '${id}')">
                            <i class="fas fa-user-circle" style="color:#3b82f6; margin-right:8px; opacity:0.6;"></i>${name} 
                            <span style="font-size:0.7rem; color:#94a3b8; font-weight:400; margin-left:4px;">(${id})</span>
                        </div>`;
                        suggestionsCount++;
                    }
                }
            });

            if (suggestionsHtml) {
                suggestionsBox.innerHTML = suggestionsHtml;
                suggestionsBox.style.display = 'block';
            } else {
                suggestionsBox.style.display = 'none';
            }

            checkEmptyState(visibleCount);
        }

        function checkEmptyState(count) {
            const body = document.getElementById('empTableBody');
            if (!body) return;

            let emptyMsg = document.getElementById('noResultsRow');

            if (count === undefined) {
                count = 0;
                rows.forEach(row => {
                    if (row.style.display !== 'none') count++;
                });
            }

            if (count === 0 && rows.length > 0) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement('tr');
                    emptyMsg.id = 'noResultsRow';
                    emptyMsg.className = 'empty-row';
                    emptyMsg.innerHTML = `
                        <td colspan="11" style="text-align:center; padding:60px 20px; color:#9ca3af;">
                            <i class="fas fa-search-minus" style="font-size:2.5rem; opacity:0.3; display:block; margin-bottom:10px;"></i>
                            <p style="font-weight:600; margin:0;">No matching employees found.</p>
                        </td>
                    `;
                    body.appendChild(emptyMsg);
                }
                emptyMsg.style.display = '';
            } else if (emptyMsg) {
                emptyMsg.style.display = 'none';
            }
        }

        /* ── Status & Time Save All ─────────────────────────────────────── */
        function saveAllStatusAndTimes() {
            const updates = [];
            document.querySelectorAll('#empTableBody .emp-row').forEach(row => {
                const selectEl = row.querySelector('.status-select');
                if (!selectEl) return;
                const empId = selectEl.id.split('_').pop();

                let statusVal = selectEl.value;
                if (statusVal === 'OTHERS') {
                    const inputEl = document.getElementById('status_input_' + empId);
                    if (inputEl && inputEl.value.trim() !== '') {
                        statusVal = inputEl.value.trim().toUpperCase();
                    }
                }
                const timeEl = document.getElementById('official_time_' + empId);
                updates.push({
                    id: empId,
                    status: statusVal,
                    official_time: timeEl ? timeEl.value : ''
                });
            });

            if (updates.length === 0) return;

            const btn = document.getElementById('saveAllBtn');
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            const fd = new FormData();
            fd.append('updates', JSON.stringify(updates));

            const routeUrl = "{!! route('admin.employee.bulkUpdate') !!}";
            fetch(routeUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: fd
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    showSuccess('All employee statuses and official times have been successfully updated.');
                } else {
                    alert('Error: ' + res.message);
                }
            }).catch(() => {
                alert('Save failed. Please check your connection.');
            }).finally(() => {
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            });
        }

        function handleStatusChange(empId, selectEl) {
            const indText = document.getElementById('status_text_' + empId);
            const indDot = document.getElementById('status_dot_' + empId);
            if (selectEl.value === 'OTHERS') {
                selectEl.style.display = 'none';
                const inputEl = document.getElementById('status_input_' + empId);
                inputEl.style.display = 'block';
                inputEl.focus();
                if (indText) {
                    indText.innerText = 'OTHERS';
                    indText.style.color = '#ef4444';
                    indDot.className = 'status-dot dot-other';
                }
            } else {
                if (indText) {
                    indText.innerText = selectEl.value;
                    indText.style.color = selectEl.value === 'ACTIVE' ? '#15803d' : '#ef4444';
                    indDot.className = 'status-dot ' + (selectEl.value === 'ACTIVE' ? 'dot-active' : 'dot-inactive');
                }
            }
        }

        function handleCustomStatusEnter(event, empId, inputEl) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const indText = document.getElementById('status_text_' + empId);
                const indDot = document.getElementById('status_dot_' + empId);

                if (inputEl.value.trim()) {
                    if (indText) {
                        indText.innerText = inputEl.value.trim().toUpperCase();
                        indText.style.color = '#ef4444';
                        indDot.className = 'status-dot dot-other';
                    }
                    inputEl.blur();
                } else {
                    inputEl.style.display = 'none';
                    const selectEl = document.getElementById('status_select_' + empId);
                    selectEl.style.display = 'block';
                    selectEl.value = 'ACTIVE';
                    if (indText) {
                        indText.innerText = 'ACTIVE';
                        indText.style.color = '#15803d';
                        indDot.className = 'status-dot dot-active';
                    }
                }
            }
        }

        function queueOfficialTimeChange(empId, val) {
            const el = document.getElementById('official_time_' + empId);
            if (!el) return;
            // Flash border to indicate a pending change
            el.style.borderColor = '#3b82f6';
            el.style.background = '#eff6ff';
            setTimeout(() => {
                el.style.borderColor = '#e5e7eb';
                el.style.background = '#f8fafc';
            }, 1200);
        }

        /* ── Modals ──────────────────────────────────────────────────── */
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function openAddModal() {
            document.getElementById('addEmpForm').reset();
            document.getElementById('addEmpModal').style.display = 'flex';
        }

        function openEditModal(id, status, officialTime) {
            document.getElementById('edit_emp_id').value = id;
            document.getElementById('edit_official_time').value = officialTime;
            const sel = document.getElementById('edit_status');
            let found = false;
            for (let i = 0; i < sel.options.length; i++) {
                if (sel.options[i].value === status) { sel.selectedIndex = i; found = true; break; }
            }
            if (!found) {
                sel.value = 'OTHERS';
                document.getElementById('custom_status').value = status;
                document.getElementById('custom_status_div').style.display = 'block';
            } else {
                document.getElementById('custom_status').value = '';
                document.getElementById('custom_status_div').style.display = 'none';
            }
            document.getElementById('editEmpModal').style.display = 'flex';
        }

        document.getElementById('edit_status').addEventListener('change', function () {
            document.getElementById('custom_status_div').style.display = this.value === 'OTHERS' ? 'block' : 'none';
        });

        /* ── Action Modals Logic ─────────────────────────────────────────── */
        function showCustomConfirm(title, message, onConfirm) {
            const modal = document.getElementById('customConfirmModal');
            if(!modal) return;
            document.getElementById('confirmTitle').innerText = title;
            document.getElementById('confirmMessage').innerText = message;
            document.getElementById('confirmConfirmBtn').onclick = () => {
                closeModal('customConfirmModal');
                onConfirm();
            };
            document.getElementById('confirmCancelBtn').onclick = () => closeModal('customConfirmModal');
            modal.style.display = 'flex';
        }



        function openActionsModal(id, name, existingRemarks, remarksDone) {
            // Reset form UI
            const form = document.getElementById('unifiedAttendanceForm');
            if (form) form.reset();

            // Clear dynamic parts
            document.getElementById('lateDateRows').innerHTML = '';
            document.getElementById('utDateRows').innerHTML = '';
            document.getElementById('absHiddenDates').innerHTML = '';
            document.getElementById('absSelectedChips').innerHTML = '<span style="color:#94a3b8;font-size:0.78rem;align-self:center;">No dates selected yet</span>';
            document.getElementById('late_date_count').innerText = '(0 total mins)';
            document.getElementById('ut_date_count').innerText = '(0 total mins)';
            document.getElementById('abs_date_count').innerText = '(0 selected)';

            // Reset Absent selection Set
            if (typeof absCalState !== 'undefined') absCalState.selected.clear();

            // Setup
            document.getElementById('action_emp_name').innerText = name;
            document.querySelectorAll('.action_emp_id').forEach(el => el.value = id);

            // Remarks setup
            const remarksArea = document.getElementById('remarks_textarea');
            if (remarksArea) remarksArea.value = '';

            // Show/hide active remark banner
            const banner = document.getElementById('activeRemarkBanner');
            const activeText = document.getElementById('activeRemarkText');
            const isDone = remarksDone === '1';
            if (banner && activeText) {
                if (existingRemarks && !isDone) {
                    activeText.textContent = existingRemarks;
                    banner.style.display = 'flex';
                } else {
                    banner.style.display = 'none';
                }
            }

            // Store
            window._currentRemarksEmpId = id;
            window._currentRemarksDone  = isDone;

            // Initial rows
            addLateDateRow();
            addUtDateRow();

            document.getElementById('actionsModal').style.display = 'flex';
            showActionTab('late');
        }

        async function loadRemarksHistory(empId) {
            const list = document.getElementById('remarksHistoryList');
            if (!list) return;
            list.innerHTML = '<div style="color:#94a3b8;font-size:0.78rem;text-align:center;padding:12px 0;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

            try {
                const res = await fetch('{{ route("admin.employee.remarks.history") }}?emp_id=' + empId, {
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() }
                });
                const result = await res.json();

                if (!result.success || !result.history.length) {
                    list.innerHTML = '<div style="color:#94a3b8;font-size:0.78rem;text-align:center;padding:12px 0;"><i class="fas fa-inbox" style="opacity:0.4;display:block;font-size:1.4rem;margin-bottom:6px;"></i>No remarks history yet.</div>';
                    return;
                }

                list.innerHTML = result.history.map(h => {
                    const isDone = h.is_done == 1;
                    const createdDate = new Date(h.created_at).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit' });
                    const doneDate   = h.done_at ? new Date(h.done_at).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' }) : null;
                    return `
                        <div style="display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border-radius:10px;border:1px solid ${isDone ? '#d1fae5' : '#fed7aa'};background:${isDone ? '#f0fdf4' : '#fff7ed'};">
                            <div style="flex-shrink:0;width:30px;height:30px;border-radius:50%;background:${isDone ? '#22c55e' : '#f59e0b'};display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;margin-top:2px;">
                                <i class="fas ${isDone ? 'fa-check' : 'fa-comment-alt'}"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.82rem;color:${isDone ? '#065f46' : '#92400e'};font-weight:600;line-height:1.5;word-break:break-word;margin-bottom:4px;">${h.remark}</div>
                                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                                    <span style="font-size:0.65rem;color:#94a3b8;font-weight:600;"><i class="fas fa-clock" style="margin-right:3px;"></i>${createdDate}</span>
                                    ${isDone && doneDate ? `<span style="font-size:0.65rem;color:#16a34a;font-weight:700;background:#dcfce7;padding:1px 6px;border-radius:20px;"><i class="fas fa-check-circle" style="margin-right:3px;"></i>Done ${doneDate}</span>` : ''}
                                    ${!isDone ? '<span style="font-size:0.65rem;color:#b45309;font-weight:700;background:#fef3c7;padding:1px 6px;border-radius:20px;">Active</span>' : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } catch(e) {
                list.innerHTML = '<div style="color:#ef4444;font-size:0.78rem;text-align:center;padding:12px 0;">Failed to load history.</div>';
            }
        }

        // showActionTab – controls which tab panel is visible and loads history on demand
        function showActionTab(tab) {
            // Hide all sections
            document.querySelectorAll('.action-tab-content').forEach(el => el.style.display = 'none');

            const empId = window._currentRemarksEmpId;

            if (tab === 'late') {
                document.getElementById('lateSection').style.display = 'flex';
                if (empId) loadAttendanceHistory(empId, 'late', 'lateHistoryList');
            }
            else if (tab === 'undertime') {
                document.getElementById('utSection').style.display = 'flex';
                if (empId) loadAttendanceHistory(empId, 'undertime', 'utHistoryList');
            }
            else if (tab === 'absent') {
                document.getElementById('absentSection').style.display = 'flex';
                if (empId) loadAttendanceHistory(empId, 'absent', 'absHistoryList');
            }
            else if (tab === 'present') {
                document.getElementById('presentFormSection').style.display = 'flex';
                if (empId) loadAttendanceHistory(empId, 'all', 'allHistoryList');
            }
            else if (tab === 'remarks') {
                const sec = document.getElementById('remarksSection');
                sec.style.display = 'flex';
                if (empId) loadRemarksHistory(empId);
            }

            // Save All button visibility
            const saveAllBtn = document.getElementById('saveAllAttBtn');
            if (saveAllBtn) saveAllBtn.style.display = (tab === 'remarks') ? 'none' : '';

            document.querySelectorAll('.att-tab').forEach(btn => btn.classList.remove('active'));
            const targetTab = document.querySelector('.tab-' + tab);
            if (targetTab) targetTab.classList.add('active');
        }

        async function loadAttendanceHistory(empId, filter, listId) {
            const list = document.getElementById(listId);
            if (!list) return;
            list.innerHTML = '<div style="color:#94a3b8;font-size:0.75rem;text-align:center;padding:8px 0;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

            const month = {{ $month }};
            const year  = {{ $year }};
            const pad = n => String(n).padStart(2, '0');
            const lastDay = new Date(year, month, 0).getDate();
            const start = `${year}-${pad(month)}-01`;
            const end   = `${year}-${pad(month)}-${pad(lastDay)}`;

            try {
                const res = await fetch(`{{ route('api.individual.summary') }}?emp_id=${empId}&start_date=${start}&end_date=${end}`);
                const result = await res.json();
                if (!result.success) throw new Error(result.message);

                const data = result.data;
                let entries = [];

                if (filter === 'absent' || filter === 'all') {
                    (data.absent_detailed || []).forEach(a => {
                        const clean = (a.type || '')
                            .replace(/^absence\s+(with|without)\s+pay\s*[-–]?\s*/i, '')
                            .replace(/^(with|without)\s+pay\s*[-–]?\s*/i, '')
                            .trim();
                        const isWop = /without/i.test(a.type || '');
                        entries.push({ day: a.day, label: isWop ? 'Without Pay' : 'With Pay', detail: clean || '—', color: isWop ? '#991b1b' : '#166534', bg: isWop ? '#fee2e2' : '#dcfce7', icon: 'fa-user-slash' });
                    });
                }
                if (filter === 'late' || filter === 'all') {
                    (data.late_logs || []).forEach(l => {
                        entries.push({ day: l.day, label: 'Tardy', detail: `${l.mins} mins`, color: '#b45309', bg: '#fef3c7', icon: 'fa-sign-in-alt' });
                    });
                }
                if (filter === 'undertime' || filter === 'all') {
                    (data.ut_logs || []).forEach(u => {
                        entries.push({ day: u.day, label: 'Undertime', detail: `${u.mins} mins`, color: '#0369a1', bg: '#e0f2fe', icon: 'fa-sign-out-alt' });
                    });
                }

                if (!entries.length) {
                    list.innerHTML = '<div style="color:#94a3b8;font-size:0.75rem;text-align:center;padding:10px 0;"><i class="fas fa-check-circle" style="opacity:0.35;margin-right:4px;"></i>No records this month.</div>';
                    return;
                }

                entries.sort((a, b) => a.day - b.day);

                list.innerHTML = entries.map(e => `
                    <div style="display:flex;gap:8px;align-items:center;padding:7px 10px;border-radius:8px;background:${e.bg};">
                        <div style="flex-shrink:0;width:26px;height:26px;border-radius:50%;background:${e.color};display:flex;align-items:center;justify-content:center;color:white;font-size:0.65rem;">
                            <i class="fas ${e.icon}"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <span style="font-size:0.78rem;color:${e.color};font-weight:800;">Day ${e.day}</span>
                            <span style="font-size:0.72rem;color:${e.color};font-weight:600;margin-left:6px;opacity:0.85;">${e.label}</span>
                            ${e.detail ? `<span style="font-size:0.68rem;color:${e.color};opacity:0.7;margin-left:6px;">— ${e.detail}</span>` : ''}
                        </div>
                    </div>
                `).join('');

            } catch(err) {
                list.innerHTML = '<div style="color:#ef4444;font-size:0.75rem;text-align:center;padding:8px 0;">Failed to load.</div>';
            }
        }

        async function saveRemarks() {
            const empId = window._currentRemarksEmpId;
            if (!empId) return;
            const remark = document.getElementById('remarks_textarea').value.trim();
            if (!remark) { alert('Please enter a remark before saving.'); return; }

            const btn = document.getElementById('saveRemarksBtn');
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            const fd = new FormData();
            fd.append('emp_id', empId);
            fd.append('remarks', remark);

            try {
                const res = await fetch('{{ route("admin.employee.remarks") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: fd
                });
                const result = await res.json();
                if (result.success) {
                    // Clear textarea, update banner, refresh history (no page reload)
                    document.getElementById('remarks_textarea').value = '';
                    window._currentRemarksDone = false;

                    // Show active banner with new remark
                    const banner   = document.getElementById('activeRemarkBanner');
                    const activeText = document.getElementById('activeRemarkText');
                    if (banner && activeText) {
                        activeText.textContent = remark;
                        banner.style.display   = 'flex';
                    }

                    btn.innerHTML = '<i class="fas fa-check"></i> Saved!';
                    setTimeout(() => { btn.innerHTML = oldHtml; btn.disabled = false; }, 1500);

                    // Reload history list inline
                    loadRemarksHistory(empId);
                } else {
                    alert('Error: ' + result.message);
                    btn.innerHTML = oldHtml;
                    btn.disabled  = false;
                }
            } catch(e) {
                alert('Request failed');
                btn.innerHTML = oldHtml;
                btn.disabled  = false;
            }
        }

        async function markRemarkDone() {
            const empId = window._currentRemarksEmpId;
            if (!empId) return;

            const btn     = document.getElementById('markDoneBtn');
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            btn.disabled  = true;

            const fd = new FormData();
            fd.append('emp_id', empId);

            try {
                const res = await fetch('{{ route("admin.employee.remarks.done") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: fd
                });
                const result = await res.json();
                if (result.success) {
                    closeModal('actionsModal');
                    showSuccess('Remark has been marked as Done. Name is back to normal.', true);
                } else {
                    alert('Error: ' + result.message);
                    btn.innerHTML = oldHtml;
                    btn.disabled  = false;
                }
            } catch(e) {
                alert('Request failed');
                btn.innerHTML = oldHtml;
                btn.disabled  = false;
            }
        }

        /* ── Late Multi-Date Helpers ───────────────────────────────── */
        function updateLateDateCount() {
            const rows = document.querySelectorAll('#lateDateRows .late-date-row');
            const totalMins = Array.from(document.querySelectorAll('input[name="late_mins_arr[]"]'))
                .reduce((sum, el) => sum + (parseInt(el.value) || 0), 0);
            document.getElementById('late_date_count').textContent = '(' + totalMins + ' total mins)';
        }

        function addLateDateRow() {
            const container = document.getElementById('lateDateRows');
            const row = document.createElement('div');
            row.className = 'late-date-row';
            row.style.cssText = 'display:flex;gap:6px;align-items:center;';
            row.innerHTML = `
                <input type="date" name="late_dates[]" 
                    style="flex:1;padding:8px 10px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:0.82rem;outline:none;font-family:inherit;">
                <input type="number" name="late_mins_arr[]" min="1" placeholder="Mins" oninput="updateLateDateCount()"
                    style="width:80px;padding:8px 10px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:0.82rem;outline:none;font-family:inherit;text-align:center;">
                <button type="button" onclick="removeLateDateRow(this)" title="Remove"
                    style="width:28px;height:28px;border:none;background:#fee2e2;color:#ef4444;border-radius:6px;cursor:pointer;font-size:0.8rem;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-times"></i>
                </button>`;
            container.appendChild(row);
            updateLateDateCount();
        }

        function removeLateDateRow(btn) {
            const rows = document.querySelectorAll('#lateDateRows .late-date-row');
            if (rows.length <= 1) return;
            btn.closest('.late-date-row').remove();
            updateLateDateCount();
        }

        /* ── Undertime Multi-Date Helpers ────────────────────────────── */
        function updateUtDateCount() {
            const rows = document.querySelectorAll('#utDateRows .ut-date-row');
            const totalMins = Array.from(document.querySelectorAll('input[name="ut_mins_arr[]"]'))
                .reduce((sum, el) => sum + (parseInt(el.value) || 0), 0);
            document.getElementById('ut_date_count').textContent = '(' + totalMins + ' total mins)';
        }

        function addUtDateRow() {
            const container = document.getElementById('utDateRows');
            if(!container) return;
            const row = document.createElement('div');
            row.className = 'ut-date-row';
            row.style.cssText = 'display:flex;gap:6px;align-items:center;';
            row.innerHTML = `
                <input type="date" name="ut_dates[]" 
                    style="flex:1;padding:8px 10px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:0.82rem;outline:none;font-family:inherit;">
                <input type="number" name="ut_mins_arr[]" min="1" placeholder="Mins" oninput="updateUtDateCount()"
                    style="width:80px;padding:8px 10px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:0.82rem;outline:none;font-family:inherit;text-align:center;">
                <button type="button" onclick="removeUtDateRow(this)" title="Remove"
                    style="width:28px;height:28px;border:none;background:#fee2e2;color:#ef4444;border-radius:6px;cursor:pointer;font-size:0.8rem;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-times"></i>
                </button>`;
            container.appendChild(row);
            updateUtDateCount();
        }

        function removeUtDateRow(btn) {
            const rows = document.querySelectorAll('#utDateRows .ut-date-row');
            if (rows.length <= 1) return;
            btn.closest('.ut-date-row').remove();
            updateUtDateCount();
        }

        async function submitUnifiedRecords(event) {
            event.preventDefault();
            const form = event.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            const oldHtml = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving All Records...';
            submitBtn.disabled = true;

            const fd = new FormData(form);

            try {
                const res = await fetch('{{ route("admin.employee.attendanceActions") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: fd
                });
                const result = await res.json();
                if (result.success) {
                    closeModal('actionsModal');
                    showSuccess('Attendance records have been successfully saved.', true);
                } else {
                    alert('Error: ' + result.message);
                    submitBtn.innerHTML = oldHtml;
                    submitBtn.disabled = false;
                }
            } catch (e) {
                alert('Request failed');
                submitBtn.innerHTML = oldHtml;
                submitBtn.disabled = false;
            }
        }

        /* ── Absent Calendar Picker ──────────────────────────────────── */
        const absCalState = {
            year:  {{ $year }},
            month: {{ $month }},
            selected: new Set()
        };
        const _absMonthNames = ['January','February','March','April','May','June',
                                'July','August','September','October','November','December'];

        function openAbsCalModal() {
            absCalState.year  = {{ $year }};
            absCalState.month = {{ $month }};
            absCalState.selected.clear();
            document.querySelectorAll('#absHiddenDates input').forEach(inp => {
                if (inp.value) absCalState.selected.add(inp.value);
            });
            renderAbsCal();
            document.getElementById('absCalModal').style.display = 'flex';
        }

        function absCalPrev() {
            absCalState.month--;
            if (absCalState.month < 1) { absCalState.month = 12; absCalState.year--; }
            renderAbsCal();
        }
        function absCalNext() {
            absCalState.month++;
            if (absCalState.month > 12) { absCalState.month = 1; absCalState.year++; }
            renderAbsCal();
        }

        function renderAbsCal() {
            const { year, month, selected } = absCalState;
            document.getElementById('absCalMonthLabel').textContent =
                _absMonthNames[month - 1] + ' ' + year;
            const grid = document.getElementById('absCalGrid');
            grid.innerHTML = '';
            const firstDay = new Date(year, month - 1, 1).getDay();
            const daysInMonth = new Date(year, month, 0).getDate();
            for (let i = 0; i < firstDay; i++) {
                const blank = document.createElement('div');
                blank.style.height = '34px';
                grid.appendChild(blank);
            }
            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = year + '-' + String(month).padStart(2,'0') + '-' + String(d).padStart(2,'0');
                const dow = new Date(year, month - 1, d).getDay();
                const isWeekend = (dow === 0 || dow === 6);
                const isSelected = selected.has(dateStr);
                const cell = document.createElement('div');
                cell.dataset.date = dateStr;
                cell.onclick = function() { toggleAbsCalDate(this.dataset.date); };
                cell.style.cssText = 'height:34px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.82rem;font-weight:700;transition:all 0.15s;' +
                    (isSelected  ? 'background:#ef4444;color:white;box-shadow:0 2px 6px rgba(239,68,68,0.4);' :
                     isWeekend   ? 'background:#fef9c3;color:#b45309;' :
                                   'background:#f1f5f9;color:#1e293b;');
                cell.textContent = d;
                grid.appendChild(cell);
            }
        }

        function toggleAbsCalDate(dateStr) {
            if (absCalState.selected.has(dateStr)) absCalState.selected.delete(dateStr);
            else absCalState.selected.add(dateStr);
            renderAbsCal();
        }

        function applyAbsCalSelection() {
            const dates = Array.from(absCalState.selected).sort();
            const hiddenCont = document.getElementById('absHiddenDates');
            hiddenCont.innerHTML = '';
            dates.forEach(d => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'abs_dates[]'; inp.value = d;
                hiddenCont.appendChild(inp);
            });
            const chipsCont = document.getElementById('absSelectedChips');
            chipsCont.innerHTML = dates.length === 0
                ? '<span style="color:#94a3b8;font-size:0.78rem;align-self:center;">No dates selected yet</span>'
                : dates.map(d => {
                    const parts = d.split('-');
                    const label = _absMonthNames[parseInt(parts[1])-1].slice(0,3) + ' ' + parseInt(parts[2]);
                    return `<span onclick="removeAbsChip('${d}')" title="Click to remove"
                        style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;background:#fee2e2;color:#b91c1c;border-radius:20px;font-size:0.72rem;font-weight:700;cursor:pointer;">
                        ${label} <i class="fas fa-times" style="font-size:0.6rem;"></i></span>`;
                  }).join('');
            document.getElementById('abs_date_count').textContent =
                '(' + dates.length + ' date' + (dates.length !== 1 ? 's' : '') + ')';
            closeModal('absCalModal');
        }

        function removeAbsChip(dateStr) {
            absCalState.selected.delete(dateStr);
            applyAbsCalSelection();
        }

        async function submitAbsentMulti(event) {
            event.preventDefault();
            const form = event.target;
            const empId = form.querySelector('.action_emp_id').value;
            const payType = document.getElementById('abs_pay_type').value;
            const reason = document.getElementById('abs_reason').value;
            const dateInputs = form.querySelectorAll('input[name="abs_dates[]"]');
            const dates = Array.from(dateInputs).map(i => i.value).filter(v => v);
            if (!dates.length) { alert('Please select at least one absence date from the calendar.'); return; }
            const submitBtn = form.querySelector('button[type="submit"]');
            const oldHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
            submitBtn.disabled = true;
            const url = form.getAttribute('data-route');
            let saved = 0, failed = [];
            for (const date of dates) {
                const fd = new FormData();
                fd.append('emp_id', empId); fd.append('abs_date', date);
                fd.append('pay_type', payType); fd.append('reason', reason);
                try {
                    const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: fd });
                    const result = await res.json();
                    if (result.success) saved++;
                    else failed.push(date + ': ' + result.message);
                } catch (e) { failed.push(date + ': request error'); }
            }
            submitBtn.innerHTML = oldHtml;
            submitBtn.disabled = false;
            if (failed.length) {
                alert('Saved ' + saved + '/' + dates.length + ' date(s).\nFailed:\n' + failed.join('\n'));
                window.location.reload();
            } else {
                closeModal('actionsModal');
                showSuccess('Absence records have been successfully saved for ' + dates.length + ' date(s).');
            }
        }

        /* ── All Present (bulk) ──────────────────────────────────────── */
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        function updateAllPresentLabel() {
            const y = document.getElementById('allPresentYear').value;
            const m = parseInt(document.getElementById('allPresentMonth').value);
            document.getElementById('allPresentMonthLabel').innerText = monthNames[m - 1] + ' ' + y;
        }

        function openAllPresentModal() {
            updateAllPresentLabel();
            document.getElementById('allPresentModal').style.display = 'flex';
        }

        document.getElementById('allPresentYear').addEventListener('input', updateAllPresentLabel);
        document.getElementById('allPresentMonth').addEventListener('change', updateAllPresentLabel);

        async function submitAllPresent() {
            const year = parseInt(document.getElementById('allPresentYear').value);
            const month = parseInt(document.getElementById('allPresentMonth').value);
            if (!year || !month) return;
            const btn = document.getElementById('allPresentBtn');
            btn.disabled = true;
            const empIds = JSON.parse(document.getElementById('empIdsData').textContent);
            let done = 0;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> 0/${empIds.length}…`;
            for (const id of empIds) {
                const fd = new FormData();
                fd.append('emp_id', id); fd.append('year', year); fd.append('month', month);
                await fetch('{!! route("admin.employee.presentAll") !!}', { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: fd });
                done++;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + done + '/' + empIds.length + '…';
            }
            showSuccess('All active employees have been marked present for the selected month.', false);
            setTimeout(() => {
                window.location.href = '{!! url("admin/calendar") !!}?mode=batch&year=' + year + '&month=' + month;
            }, 1500);
        }

        /* ── Per-Employee Present ────────────────────────────────────── */
        function openEmpPresentModal(id, name) {
            document.getElementById('empPresentId').value = id;
            document.getElementById('empPresentName').innerText = name;
            document.getElementById('empPresentModal').style.display = 'flex';
        }

        async function submitEmpPresent() {
            const id = document.getElementById('empPresentId').value;
            const year = parseInt(document.getElementById('empPresentYear').value);
            const month = parseInt(document.getElementById('empPresentMonth').value);
            if (!id || !year || !month) return;
            const btn = document.getElementById('empPresentBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';
            const fd = new FormData();
            fd.append('emp_id', id); fd.append('year', year); fd.append('month', month);
            try {
                const res = await fetch('{!! route("admin.employee.presentAll") !!}', { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: fd });
                const result = await res.json();
                if (result.success) {
                    showSuccess('Employee has been marked present for the selected month.', false);
                    setTimeout(() => {
                        window.location.href = '{!! url("admin/calendar") !!}?mode=batch&year=' + year + '&month=' + month;
                    }, 1500);
                } else {
                    alert('Error: ' + (result.message || 'Unknown'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Mark Present & View';
                }
            } catch (err) {
                console.error(err);
                alert('Network error. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Mark Present & View';
            }
        }

        async function submitEmpPresentForm(event) {
            event.preventDefault();
            const form = event.target;
            const id = form.querySelector('.action_emp_id').value;
            const year = parseInt(document.getElementById('presentFormYear').value);
            const month = parseInt(document.getElementById('presentFormMonth').value);
            const btn = document.getElementById('presentFormBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';
            const fd = new FormData();
            fd.append('emp_id', id); fd.append('year', year); fd.append('month', month);
            const res = await fetch('{!! route("admin.employee.presentAll") !!}', { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: fd });
            const result = await res.json();
            if (result.success) {
                showSuccess('Employee has been marked present for the selected month.', false);
                setTimeout(() => {
                    window.location.href = '{!! url("admin/calendar") !!}?mode=batch&year=' + year + '&month=' + month;
                }, 1500);
            } else {
                alert('Error: ' + (result.message || 'Unknown'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Mark Present & View';
            }
        }

        /* ── AJAX Helpers ────────────────────────────────────────────── */
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        async function performFetch(url, fd, modalId) {
            try {
                const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: fd });
                const result = await res.json();
                if (result.success) { 
                    closeModal(modalId); 
                    showSuccess('Information has been successfully updated.');
                }
                else alert('Error: ' + result.message);
            } catch (e) { alert('Request failed: ' + e); }
        }

        let successReload = true;
        function showSuccess(msg, reload = true) {
            const modal = document.getElementById('successModal');
            if(!modal) {
                alert(msg);
                if(reload) window.location.reload();
                return;
            }
            document.getElementById('successMessage').innerText = msg;
            successReload = reload;
            modal.style.display = 'flex';
        }
        function handleSuccessClose() {
            closeModal('successModal');
            if(successReload) window.location.reload();
        }

        function submitForm(event, url, modalId) {
            event.preventDefault();
            const form = event.target;
            const btn = form.querySelector('button[type="submit"]');
            const old = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
            btn.disabled = true;
            performFetch(url, new FormData(form), modalId).finally(() => {
                btn.innerHTML = old; btn.disabled = false;
            });
        }

        function confirmDelete(id, name) {
            if (!confirm("Delete " + name + "? This will remove all their attendance records.")) return;
            const fd = new FormData(); fd.append('id', id);
            fetch('{!! route("admin.employee.delete") !!}', { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: fd })
                .then(r => r.json()).then(res => {
                    if (res.success) {
                        showSuccess('Employee has been deleted.');
                    }
                    else alert('Error: ' + res.message);
                }).catch(e => alert('Failed: ' + e));
        }

        function confirmClearAllEmployees() {
            if (!confirm("Are you sure you want to clear ALL employees? This action cannot be undone and will remove all attendance records.")) return;
            
            const btn = document.getElementById('clearAllBtn');
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
            btn.disabled = true;

            fetch('{!! route("admin.employee.clearAll") !!}', { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() } })
                .then(r => r.json()).then(res => {
                    if (res.success) {
                        showSuccess('All employee records and attendance history have been cleared.');
                    } else {
                        alert('Error: ' + res.message);
                        btn.innerHTML = oldHtml;
                        btn.disabled = false;
                    }
                }).catch(e => {
                    alert('Failed: ' + e);
                    btn.innerHTML = oldHtml;
                    btn.disabled = false;
                });
        }
        /* ── Excel Import Tab ─────────────────────────────────────── */
        let selectedExcelFile = null;
        let parsedExcelRows = [];

        function switchAddTab(tab) {
            const isManual = tab === 'manual';
            document.getElementById('tabManual').style.display = isManual ? 'block' : 'none';
            document.getElementById('tabExcel').style.display = isManual ? 'none' : 'block';

            const manualBtn = document.getElementById('tabManualBtn');
            const excelBtn = document.getElementById('tabExcelBtn');
            manualBtn.style.color = isManual ? '#3b82f6' : '#94a3b8';
            manualBtn.style.borderBottom = isManual ? '2px solid #3b82f6' : '2px solid transparent';
            excelBtn.style.color = isManual ? '#94a3b8' : '#3b82f6';
            excelBtn.style.borderBottom = isManual ? '2px solid transparent' : '2px solid #3b82f6';
        }

        function handleExcelFile(file) {
            if (!file) return;
            selectedExcelFile = file;
            document.getElementById('excelFileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            document.getElementById('excelFileInfo').style.display = 'flex';
            document.getElementById('excelResults').style.display = 'none';
            // Parse with SheetJS and open preview modal
            parseAndPreviewExcel(file);
        }

        function parseAndPreviewExcel(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const sheet = workbook.Sheets[workbook.SheetNames[0]];
                    const json = XLSX.utils.sheet_to_json(sheet, { defval: '', raw: false });

                    // Normalize column keys: lowercase, strip punctuation, collapse spaces to underscore
                    const normalizeKey = (k) => k.trim().toLowerCase()
                        .replace(/[^a-z0-9\s]/g, '')   // remove periods, #, etc.
                        .replace(/\s+/g, '_')            // spaces → underscore
                        .replace(/_+/g, '_')             // collapse multiple underscores
                        .replace(/^_|_$/g, '');          // trim leading/trailing underscores

                    const normalize = (obj) => {
                        const norm = {};
                        for (const k in obj) norm[normalizeKey(k)] = obj[k];
                        return norm;
                    };

                    // Map to our expected columns — very broad aliases
                    // Note: aliases are already in normalized form (no punctuation)
                    const colAliases = {
                        last_name:   ['last_name','lastname','last name','surname','family name','familyname',
                                      'apellido','lname','l_name','last'],
                        first_name:  ['first_name','firstname','first name','given name','givenname',
                                      'fname','f_name','pangalan','first','given'],
                        middle_name: ['middle_name','middlename','middle name','mi','middle initial',
                                      'middle','mname','m_name'],
                        emp_number:  ['emp_number','employee_id','employee id','emp_id','empid',
                                      'employee no','employee_no','emp no','empno',
                                      'employee number','employee_number',
                                      'personnel_id','personnel id','id','number','no'],
                        station:     ['station','division','office','department','dept','unit',
                                      'assignment','work_station','workstation','assigned'],
                        official_time: ['official_time', 'time', 'sched', 'schedule', 'shift', 'official time']
                    };

                    function findVal(norm, aliases) {
                        for (const a of aliases) {
                            const k = normalizeKey(a);
                            if (norm[k] !== undefined && String(norm[k]).trim() !== '') return String(norm[k]).trim();
                        }
                        return '';
                    }

                    parsedExcelRows = json.map((row, i) => {
                        const norm = normalize(row);
                        let ln = findVal(norm, colAliases.last_name);
                        let fn = findVal(norm, colAliases.first_name);
                        const mn = findVal(norm, colAliases.middle_name);
                        const en = findVal(norm, colAliases.emp_number);
                        const st = findVal(norm, colAliases.station);
                        const ot = findVal(norm, colAliases.official_time);

                        // Fallback: if no last/first matched, check for a generic 'name' or 'full name' column
                        if (!ln && !fn) {
                            const fullName = norm['name'] || norm['full_name'] || norm['fullname'] ||
                                             norm['employee_name'] || norm['emp_name'] || '';
                            if (fullName) {
                                // Try splitting: "Last, First Middle" or "First Middle Last"
                                const cleaned = String(fullName).trim();
                                if (cleaned.includes(',')) {
                                    const parts = cleaned.split(',');
                                    ln = parts[0].trim();
                                    const rest = (parts[1] || '').trim().split(/\s+/);
                                    fn = rest[0] || '';
                                } else {
                                    const parts = cleaned.split(/\s+/);
                                    fn = parts[0] || '';
                                    ln = parts[parts.length - 1] || '';
                                }
                            }
                        }

                        return { idx: i, last_name: ln, first_name: fn, middle_name: mn, emp_number: en, station: st, official_time: ot };
                    }).filter(r => r.last_name || r.first_name); // skip completely empty rows

                    openExcelPreview();
                } catch(err) {
                    alert('Could not parse file: ' + err.message);
                }
            };
            reader.readAsArrayBuffer(file);
        }

        function openExcelPreview() {
            // Close the main Add modal to focus on preview
            closeModal('addEmpModal');

            const tbody = document.getElementById('previewTableBody');
            tbody.innerHTML = '';

            if (parsedExcelRows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;"><i class="fas fa-inbox" style="font-size:2rem;margin-bottom:10px;display:block;"></i>No valid rows found in file.</td></tr>';
            } else {
                parsedExcelRows.forEach((row, i) => {
                    appendPreviewRow(row, i);
                });
            }

            updatePreviewStats();
            document.getElementById('excelPreviewModal').style.display = 'flex';
        }

        function appendPreviewRow(row, i) {
            const tbody = document.getElementById('previewTableBody');
            const tr = document.createElement('tr');
            tr.className = 'preview-row';
            tr.innerHTML = `
                <td class="preview-idx">${i + 1}</td>
                <td><input class="preview-input" type="text" value="${escHtml(row.last_name)}" data-field="last_name" placeholder="Last Name"></td>
                <td><input class="preview-input" type="text" value="${escHtml(row.first_name)}" data-field="first_name" placeholder="First Name"></td>
                <td><input class="preview-input" type="text" value="${escHtml(row.middle_name)}" data-field="middle_name" placeholder="Middle Name"></td>
                <td><input class="preview-input" type="text" value="${escHtml(row.emp_number)}" data-field="emp_number" placeholder="Employee ID"></td>
                <td><input class="preview-input" type="text" value="${escHtml(row.station)}" data-field="station" placeholder="Station"></td>
                <td><input class="preview-input" type="text" value="${escHtml(row.official_time)}" data-field="official_time" placeholder="Time"></td>
                <td><button type="button" class="preview-del-btn" onclick="removePreviewRow(this)" title="Remove row"><i class="fas fa-trash-alt"></i></button></td>
            `;
            tbody.appendChild(tr);
        }

        function addPreviewRow() {
            const tbody = document.getElementById('previewTableBody');
            // If it was empty message
            if (tbody.querySelector('td[colspan]')) tbody.innerHTML = '';
            
            const count = tbody.querySelectorAll('.preview-row').length;
            appendPreviewRow({last_name:'', first_name:'', middle_name:'', emp_number:'', station:'', official_time:''}, count);
            updatePreviewStats();
            
            // Scroll to bottom
            const wrap = document.querySelector('.excel-preview-table-wrap');
            setTimeout(() => wrap.scrollTop = wrap.scrollHeight, 10);
        }

        function removePreviewRow(btn) {
            btn.closest('tr').remove();
            reindexPreviewRows();
            updatePreviewStats();
        }

        function reindexPreviewRows() {
            document.querySelectorAll('#previewTableBody .preview-idx').forEach((td, i) => {
                td.textContent = i + 1;
            });
        }

        function updatePreviewStats() {
            const count = document.querySelectorAll('#previewTableBody .preview-row').length;
            document.getElementById('previewStats').innerHTML =
                `<span><i class="fas fa-users" style="color:#3b82f6;"></i> <strong>${count}</strong> employee${count !== 1 ? 's' : ''} detected</span>`;
        }

        function backToUpload() {
            closeModal('excelPreviewModal');
            document.getElementById('addEmpModal').style.display = 'flex';
        }

        function escHtml(str) {
            return String(str || '')
                .replace(/&/g,'&amp;').replace(/"/g,'&quot;')
                .replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        function handleExcelDrop(event) {
            event.preventDefault();
            const zone = document.getElementById('excelDropZone');
            zone.style.borderColor = '#cbd5e1';
            zone.style.background = '#f8fafc';
            const file = event.dataTransfer.files[0];
            if (file) handleExcelFile(file);
        }

        function clearExcelFile() {
            selectedExcelFile = null;
            parsedExcelRows = [];
            document.getElementById('excelFileInput').value = '';
            document.getElementById('excelFileInfo').style.display = 'none';
            document.getElementById('excelResults').style.display = 'none';
            const btn = document.getElementById('importExcelBtn');
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
        }

        function showImportModal(state) {
            // state: 'loading' | 'success' | 'failed'
            ['loading','success','failed'].forEach(s =>
                document.getElementById('importState_' + s).style.display = (s === state ? 'block' : 'none')
            );
            document.getElementById('importResultModal').style.display = 'flex';
        }

        async function confirmExcelImport() {
            // Collect current values from the preview table inputs
            const rows = [];
            document.querySelectorAll('#previewTableBody tr').forEach(tr => {
                const inputs = tr.querySelectorAll('.preview-input');
                if (!inputs.length) return;
                const r = {};
                inputs.forEach(inp => r[inp.dataset.field] = inp.value.trim());
                if (r.last_name || r.first_name) rows.push(r);
            });

            if (!rows.length) {
                document.getElementById('importFailedMsg').textContent = 'No employee rows found to import. Please add at least one row.';
                showImportModal('failed');
                return;
            }

            const btn = document.getElementById('confirmImportBtn');
            const oldHtml = btn.innerHTML;
            btn.disabled = true;

            // Close preview, show loading spinner modal
            closeModal('excelPreviewModal');
            showImportModal('loading');

            const fd = new FormData();
            fd.append('rows', JSON.stringify(rows));

            try {
                const res = await fetch('{{ route("admin.employee.import") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: fd
                });
                const result = await res.json();

                if (result.success) {
                    // Build success message
                    document.getElementById('importSuccessMsg').textContent =
                        `${result.inserted} employee${result.inserted !== 1 ? 's' : ''} added successfully` +
                        (result.skipped ? `, ${result.skipped} skipped (duplicates or invalid).` : '.');

                    // Show partial errors as warnings
                    const errDiv = document.getElementById('importSuccessErrors');
                    if (result.errors && result.errors.length) {
                        errDiv.style.display = 'block';
                        errDiv.innerHTML = '<strong>⚠ Skipped rows:</strong><br>' +
                            result.errors.slice(0, 5).map(e => `• ${e}`).join('<br>');
                    } else {
                        errDiv.style.display = 'none';
                    }

                    showImportModal('success');
                    clearExcelFile();
                    // Auto-reload after 2.5 seconds
                    setTimeout(() => window.location.reload(), 2500);
                } else {
                    document.getElementById('importFailedMsg').textContent =
                        result.message || 'An unknown error occurred during import.';
                    showImportModal('failed');
                    btn.innerHTML = oldHtml;
                    btn.disabled = false;
                }
            } catch (e) {
                document.getElementById('importFailedMsg').textContent =
                    'Network error or server unavailable. Please try again.';
                showImportModal('failed');
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        }

        // Legacy: keep submitExcelImport as fallback (no longer called from UI)
        async function submitExcelImport() {
            if (!selectedExcelFile) return;
            parseAndPreviewExcel(selectedExcelFile);
        }
        /* ── Live Clock & Date ────────────────────── */
        function updateClock() {
            const now = new Date();
            let h = now.getHours();
            let m = now.getMinutes();
            let s = now.getSeconds();
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;

            document.getElementById('liveTime').innerHTML = `${h}:${m} <span style="font-size:0.75rem; color:#64748b;">${s} ${ampm}</span>`;

            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            document.getElementById('liveDate').innerText = now.toLocaleDateString('en-US', options);
        }
        setInterval(updateClock, 1000);
        updateClock();

        /* ── Live Weather (Quezon City via Open-Meteo) ────────────────────── */
        async function fetchWeather() {
            try {
                // QC Coordinates: 14.676, 121.0437
                const res = await fetch('https://api.open-meteo.com/v1/forecast?latitude=14.676&longitude=121.0437&current_weather=true');
                const data = await res.json();
                if (data && data.current_weather) {
                    const temp = Math.round(data.current_weather.temperature);
                    const code = data.current_weather.weathercode;

                    document.getElementById('weatherTemp').innerText = `${temp}°C`;

                    // Simple WMO code mapping
                    let iconClass = 'fas fa-sun';
                    let color = '#f59e0b';
                    let desc = 'Clear';

                    if (code === 0) { desc = 'Clear Sky'; iconClass = 'fas fa-sun'; color = '#f59e0b'; }
                    else if (code >= 1 && code <= 3) { desc = 'Partly Cloudy'; iconClass = 'fas fa-cloud-sun'; color = '#94a3b8'; }
                    else if (code >= 45 && code <= 48) { desc = 'Foggy'; iconClass = 'fas fa-smog'; color = '#94a3b8'; }
                    else if (code >= 51 && code <= 67) { desc = 'Rainy'; iconClass = 'fas fa-cloud-rain'; color = '#3b82f6'; }
                    else if (code >= 71 && code <= 82) { desc = 'Snow/Hail'; iconClass = 'fas fa-snowflake'; color = '#38bdf8'; }
                    else if (code >= 95) { desc = 'Thunderstorm'; iconClass = 'fas fa-bolt'; color = '#ca8a04'; }
                    else { desc = 'Cloudy'; iconClass = 'fas fa-cloud'; color = '#64748b'; }

                    const iconEl = document.getElementById('weatherIcon');
                    iconEl.className = iconClass;
                    iconEl.style.color = color;
                    document.getElementById('weatherWidget').title = desc;
                }
            } catch (err) {
                console.error("Failed to fetch weather", err);
                document.getElementById('weatherTemp').innerText = '--°C';
            }
        }
        fetchWeather();
        // Refresh weather every 30 minutes
        setInterval(fetchWeather, 30 * 60 * 1000);

        /* ── Copy Absence Dates (With Pay + Without Pay only, with reasons) ────── */
        function copyAllAttendance(btn) {
            let data;
            try { data = JSON.parse(btn.dataset.copyAll || '{}'); } catch(e) { return; }

            const currentMonth = {{ $month }};
            const currentYear  = {{ $year }};
            const shortYear = String(currentYear).slice(-2);

            const lines = [];

            // Helper: format a group of {day, reason} entries
            // First occurrence of a new reason gets  REASON-DATE, subsequent same-reason entries get bare DATE
            function formatGroup(label, entries) {
                if (!entries || entries.length === 0) return;
                const sorted = [...entries].sort((a, b) => a.day - b.day);

                let lastReason = null;
                const parts = sorted.map(e => {
                    const dateStr = `${currentMonth}/${e.day}/${shortYear}`;
                    const reason = (e.reason || '')
                        .replace(/^absence\s+(with|without)\s+pay\s*[-–]?\s*/i, '')
                        .replace(/^(with|without)\s+pay\s*[-–]?\s*/i, '')
                        .trim();

                    let token;
                    if (reason && reason !== lastReason) {
                        // New reason — show it hyphenated to the date
                        token = `${reason}-${dateStr}`;
                        lastReason = reason;
                    } else {
                        // Same reason (or no reason) — bare date only
                        token = dateStr;
                    }
                    return token;
                });

                lines.push(`${label}: ${parts.join('; ')}`);
            }

            formatGroup('With Pay', data.with_pay || []);
            formatGroup('Without Pay', data.without_pay || []);

            if (!lines.length) return;
            const text = lines.join('\n');

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => flashCopied(btn));
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.cssText = 'position:fixed;opacity:0;';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                flashCopied(btn);
            }
        }

        function flashCopied(btn) {
            btn.classList.add('copied');
            setTimeout(() => btn.classList.remove('copied'), 1400);
        }
    </script>
    <script type="application/json" id="empIdsData">
        {!! json_encode(collect($employees)->filter(function($emp) { return ($emp->status ?? 'ACTIVE') === 'ACTIVE'; })->pluck('id')->values()) !!}
    </script>
@endsection
