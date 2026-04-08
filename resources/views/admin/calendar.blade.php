@extends('layouts.app')

@section('title', 'Attendance Calendar | SDO QC')

@section('styles')
<style>
    .cal-page {
        display: flex;
        flex-direction: column;
        font-size: 0.85rem;
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

    .view-tabs-pill {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 14px;
        display: flex;
        gap: 0;
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .view-tab {
        padding: 8px 18px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 750;
        text-decoration: none !important;
        color: #64748b;
        background: transparent;
        transition: color 0.3s ease, transform 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 2;
        flex: 1;
        justify-content: center;
        min-width: 120px;
    }

    .view-tab:active {
        transform: scale(0.96);
    }

    .view-tab.active {
        color: #3b82f6;
    }

    .tab-indicator {
        position: absolute;
        height: calc(100% - 8px);
        top: 4px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.12), 0 2px 5px rgba(0, 0, 0, 0.05);
        z-index: 1;
        left: 4px;
        width: 0;
        /* Snake Motion: Use different transitions for left and right edges */
        transition: left 0.4s cubic-bezier(0.8, 0.1, 0.2, 1), 
                    width 0.35s cubic-bezier(0.8, 0.1, 0.2, 1);
    }

    /* Create the snake-like "gooey" stretch effect */
    .view-tabs-pill.moving-right .tab-indicator {
        transition-delay: 0s, 0.1s; /* Width delayed when moving right */
    }
    .view-tabs-pill.moving-left .tab-indicator {
        transition-delay: 0.1s, 0s; /* Left delayed when moving left */
    }

    .view-tab:hover {
        color: #3b82f6;
    }

    /* ── Nav & Actions ── */
    .cal-nav-pill {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .date-nav-group-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        padding: 4px 12px;
        border-radius: 50px;
        border: 1.5px solid #edf2f7;
    }

    .cal-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .options-wrap {
        position: relative;
    }

    .options-menu.show {
        display: block !important;
        animation: fadeInScale 0.2s ease-out forwards;
    }

    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Rest deleted to avoid duplicates */

    /* Right nav group */
    .cal-nav-right-pill {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 50px;
        padding: 5px 8px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        gap: 15px;
    }

    .mode-tog-group {
        display: flex;
        border-radius: 50px;
        background: transparent;
    }

    .mode-tog-btn {
        padding: 6px 14px;
        font-size: 0.75rem;
        font-weight: 700;
        border: none;
        background: transparent;
        border-radius: 50px;
        cursor: pointer;
        font-family: inherit;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: all 0.18s;
    }

    .mode-tog-btn.active {
        background: #f8fafc;
        color: #111827;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .date-nav-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .month-nav-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #f1f5f9;
        background: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #111827;
        text-decoration: none;
        font-size: 0.8rem;
        transition: all 0.18s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .month-nav-btn:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    .month-display-label {
        font-size: 0.95rem;
        font-weight: 800;
        color: #111827;
        min-width: 90px;
        text-align: center;
    }

    /* ══════ CONTENT AREA LAYOUT ══════════════════════════════════ */
    .cal-content-area {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .cal-main { flex: 1; min-width: 0; }

    /* ══════ CONTENT HEADER ════════════════════════════════════════ */
    .cal-header-row {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 12px;
    }

    .cal-title-area h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 0 4px;
    }

    .cal-title-area h1 i { color: #3b82f6; }

    .cal-title-area p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
        font-weight: 500;
    }

    .cal-controls {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .cal-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 50px;
        padding: 8px 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        transition: all 0.2s;
        position: relative;
        min-width: 380px;
        max-width: 480px;
        justify-self: center;
    }

    .cal-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1.5px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 16px 16px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 1001;
        display: none;
        max-height: 200px;
        overflow-y: auto;
    }

    .cal-suggestion-item {
        padding: 10px 16px;
        cursor: pointer;
        font-size: 0.82rem;
        transition: background 0.15s;
    }

    .cal-suggestion-item:hover { background: #f8fafc; }
    .cal-suggestion-item:last-child { border-radius: 0 0 16px 16px; }

    .cal-search-box:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }

    .cal-search-box i { color: #9ca3af; font-size: 0.85rem; }
    .cal-search-box input {
        border: none; outline: none; font-family: inherit;
        font-size: 0.82rem; background: transparent; width: 100%; color: #111827;
    }
    .cal-search-box input::placeholder { color: #9ca3af; }

    /* Options button */
    .options-wrap { position: relative; }

    .options-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 18px;
        background: #3b82f6; /* Solid Blue */
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: background 0.2s, transform 0.18s;
    }

    .options-btn:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .options-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        background: white;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        min-width: 210px;
        z-index: 1000;
        overflow: hidden;
        animation: fadeIn 0.15s ease;
        padding: 6px;
    }

    .options-menu.show { display: block; }

    .opt-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 14px;
        background: none;
        border: none;
        border-radius: 8px;
        font-family: inherit;
        font-size: 0.82rem;
        font-weight: 700;
        color: #374151;
        cursor: pointer;
        text-decoration: none;
        text-align: left;
        transition: background 0.15s;
    }

    .opt-item:hover { background: #f8fafc; }
    .opt-item i { width: 15px; text-align: center; }
    .opt-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }

    .att-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.07);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        animation: fadeInUp 0.5s ease;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 200px);
    }

    .att-table-wrap {
        overflow-x: auto;
        overflow-y: auto;
        flex: 1;
        font-size: 0.72rem; /* Global font-size reduction for entire table area */
    }

    /* Custom elegant scrollbar for the table */
    .att-table-wrap::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .att-table-wrap::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 0 0 16px 16px;
    }
    .att-table-wrap::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .att-table-wrap::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    table.att-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: inherit;
        table-layout: fixed;
    }

    /* Table Head */
    table.att-table thead {
        position: sticky;
        top: 0;
        z-index: 20;
    }

    table.att-table thead tr.head-main th {
        padding: 3px 2px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0;
        color: #3b82f6;
        background: white;
        white-space: nowrap;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 21;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    table.att-table thead tr.head-main th.col-name { text-align: left; padding-left: 6px; }

    table.att-table thead tr.head-sub th {
        padding: 2px 1px;
        font-size: 0.55rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        background: white;
        text-align: center;
        position: sticky;
        top: 26px;
        z-index: 20;
    }
    
    table.att-table thead tr:last-child th {
        border-bottom: 2px solid #94a3b8;
    }

    /* Day header coloring */
    th.day-weekend { background: #fef9c3 !important; }
    th.day-weekend span, th.day-weekend { color: #b45309 !important; }
    th.day-holiday { background: #fde047 !important; }
    th.day-holiday span, th.day-holiday { color: #854d0e !important; }

    /* Table Rows — Excel-style grid on every cell */
    table.att-table tbody td, table.att-table thead th { 
        border: none; 
        border-right: 1px solid #d1d5db;
        border-bottom: 1px solid #d1d5db; 
    }
    
    table.att-table thead th {
        border-top: 1px solid #d1d5db;
    }

    table.att-table tbody td:first-child, table.att-table thead th:first-child { 
        border-left: 1px solid #d1d5db; 
    }

    /* Station header row */
    tr.station-row td {
        padding: 6px 10px 6px 40px !important;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        text-align: left !important;
    }

    .station-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 10px;
        background: #dbeafe;
        color: #1d4ed8;
        border-radius: 20px;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.3px;
        box-shadow: 0 1px 2px rgba(29, 78, 216, 0.1);
    }

    /* Employee row */
    tr.emp-row:hover { background: #fafbff; }

    table.att-table td {
        padding: 6px 2px;
        color: #111827;
        text-align: center;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    td.td-no {
        width: 30px;
        font-size: 0.8rem;
        color: #9ca3af;
        font-weight: 700;
        padding-left: 6px;
        text-align: center;
    }

    td.td-name {
        text-align: left !important;
        padding-left: 10px;
        font-weight: 800;
        font-size: 0.85rem;
        width: 160px;
        white-space: nowrap;
        color: #111827;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Day cells */
    td.td-day {
        width: 28px;
        font-weight: 700;
        color: #374151;
        font-size: 0.85rem;
        padding: 0; 
    }

    td.td-day.cell-weekend { background: #fef9c3 !important; color: #b45309 !important; font-weight: 700; }
    td.td-day.cell-holiday { background: #fde047 !important; color: #854d0e !important; font-weight: 700; }

    td.td-abs {
        width: 35px;
        font-size: 0.82rem;
        font-weight: 800;
        text-align: center;
    }

    /* Batch mode input */
    .cell-input {
        width: 100%;
        height: 100%;
        min-height: 40px;
        text-align: center;
        border: none;
        border-radius: 0;
        background: transparent;
        font-weight: 800;
        font-size: 0.9rem;
        outline: none;
        padding: 0;
        color: #111827;
        font-family: inherit;
        cursor: pointer;
        transition: background 0.2s;
    }

    .cell-input:focus { background: #eef2ff; box-shadow: inset 0 0 0 2px #3b82f6; }

    /* Cell text — no boxes, plain Excel-style colored text */
    .badge-present { color: #374151; font-weight: 800; font-size: 1.0rem; }

    .badge-absent {
        color: #dc2626;
        font-weight: 800;
        font-size: 1.0rem;
        background: none;
    }
    
    
    .text-holiday { color: #e11d48 !important; }
    .text-normal { color: #9ca3af !important; }

    /* ── Selection & Merge Styles ── */
    .cell-selected {
        background: #e0f2fe !important;
        box-shadow: inset 0 0 0 2px #3b82f6;
    }
    
    .merge-tooltip {
        position: absolute;
        background: #1e293b;
        color: white;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 6px;
        animation: fadeIn 0.2s ease;
    }
    .merge-tooltip:hover { background: #334155; }
    
    .merged-span, .cal-grid-cell.holiday, td.td-day.cell-holiday {
        cursor: pointer;
    }
    .cal-grid-cell.holiday:hover, td.td-day.cell-holiday:hover {
        filter: brightness(0.95);
    }
    .merged-span {
        width: 100%;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        position: relative;
        cursor: pointer;
        flex-direction: column;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .merged-span:hover {
        filter: brightness(0.95);
    }

    .badge-late {
        color: #b45309;
        font-weight: 800;
        font-size: 1.0rem;
        background: none;
    }

    .badge-ut {
        color: #4338ca;
        font-weight: 800;
        font-size: 1.0rem;
        background: none;
    }

    /* Absence summary columns */
    td.td-abs {
        font-weight: 800;
        font-size: 1.0rem;
        min-width: 52px;
    }

    /* ══════ LEGEND ════════════════════════════════════════════════ */
    .att-legend {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 12px 16px;
        border-top: 1px solid #f1f5f9;
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        flex-wrap: wrap;
        flex-shrink: 0;
        background: white; /* Ensure it stays opaque */
    }

    .att-legend span { display: flex; align-items: center; gap: 5px; }

    /* ══════ INSTRUCTIONS PANEL ════════════════════════════════════ */
    .instructions-panel {
        width: 210px;
        flex-shrink: 0;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.07);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        animation: fadeInUp 0.5s ease;
    }

    .instructions-header {
        padding: 12px 14px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: white;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        line-height: 1.4;
    }

    .instructions-body {
        padding: 12px 14px;
    }

    .instructions-body ol {
        margin: 0;
        padding-left: 18px;
    }

    .instructions-body li {
        font-size: 0.7rem;
        color: #374151;
        margin-bottom: 9px;
        line-height: 1.5;
        font-weight: 500;
    }

    .instructions-body li:last-child { margin-bottom: 0; }

    /* ══════ MODAL HELPERS ═════════════════════════════════════════ */
    .form-group { margin-bottom: 13px; }
    .form-group label { display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.8rem; color: #475569; }
    .form-group input, .form-group select {
        width: 100%; padding: 9px 12px; border-radius: 10px;
        border: 1.5px solid #e2e8f0; outline: none; font-family: inherit;
        font-size: 0.85rem; color: #111827; background: white;
        transition: border-color 0.2s; box-sizing: border-box;
    }
    .form-group input:focus, .form-group select:focus {
        border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .mbtn { padding: 9px 18px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; font-size: 0.82rem; font-family: inherit; display: inline-flex; align-items: center; gap: 6px; transition: all 0.18s; }
    .mbtn:hover { transform: translateY(-1px); filter: brightness(1.05); }
    .mbtn-cancel  { background: #f1f5f9; color: #475569; }
    .mbtn-primary { background: linear-gradient(135deg,#3b82f6,#2563eb); color: white; box-shadow: 0 3px 10px rgba(59,130,246,0.3); }
    .mbtn-amber   { background: linear-gradient(135deg,#f59e0b,#d97706); color: white; box-shadow: 0 3px 10px rgba(245,158,11,0.3); }
    .mbtn-red     { background: linear-gradient(135deg,#ef4444,#dc2626); color: white; box-shadow: 0 3px 10px rgba(239,68,68,0.3); }
    .mbtn-purple  { background: linear-gradient(135deg,#8b5cf6,#6d28d9); color: white; box-shadow: 0 3px 10px rgba(139,92,246,0.3); }

    /* ══════ INDIVIDUAL VIEW SELECTOR ══════════════════════════════ */
    .indiv-selector-wrap {
        text-align: center;
        margin-top: 40px;
    }

    .indiv-selector-wrap h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .indiv-selector-wrap p {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 30px;
    }

    .station-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .emp-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
    }

    @media (max-width: 1280px) {
        .station-container { max-width: 1000px; }
        .emp-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 900px) {
        .emp-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 600px) {
        .emp-grid { grid-template-columns: repeat(2, 1fr); }
    }

    .emp-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .emp-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        border-color: #3b82f6;
    }

    .emp-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #eef2ff;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .emp-card-name {
        font-weight: 800;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .emp-card-id {
        font-size: 0.75rem;
        color: #94a3b8;
        background: #f8fafc;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 600;
    }

    /* ══════ INDIVIDUAL CALENDAR GRID ══════════════════════════════ */
    .indiv-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .indiv-emp-info h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 5px 0;
    }

    .indiv-emp-info span {
        background: #e0f2fe;
        color: #0369a1;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 800;
    }

    .indiv-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-outline-red {
        background: white;
        color: #ef4444;
        border: 1px solid #fee2e2;
        padding: 9px 18px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.82rem;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-outline-red:hover {
        background: #fee2e2;
    }

    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        grid-template-rows: repeat(6, 1fr);
        gap: 15px;
    }

    .cal-grid-cell {
        background: white;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        padding: 12px;
        min-height: 90px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s ease;
    }
    
    .cal-grid-cell.empty {
        background: transparent;
        border: none;
    }

    .cal-grid-cell.present { background: #dcfce7; border-color: #bbf7d0; }
    .cal-grid-cell.absent { background: #fee2e2; border-color: #fecaca; }
    .cal-grid-cell.late { background: #fef3c7; border-color: #fde68a; }
    .cal-grid-cell.holiday { background: #fde047; border-color: #facc15; }

    .cell-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .cell-dow {
        font-size: 0.65rem;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
    }

    .cell-date {
        font-size: 1.1rem;
        font-weight: 800;
        color: #cbd5e1;
    }

    .cell-select {
        width: 100%;
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-family: inherit;
        font-size: 0.8rem;
        font-weight: 600;
        color: #1e293b;
        background: white;
        outline: none;
        cursor: pointer;
    }
    
    .cell-select:focus {
        border-color: #3b82f6;
    }
    
    .holiday-label {
        color: #854d0e;
        font-weight: 800;
        font-size: 0.72rem;
        text-align: center;
        text-transform: uppercase;
        padding: 0 4px;
        line-height: 1.2;
    }

    @media (max-width: 768px) {
        .cal-topbar { flex-direction: column; }
        .instructions-panel { display: none; }
        .cal-search-box input { width: 110px; }
    }

    /* ══════ PRINT VIEW ════════════════════════════════════════════ */
    .print-header { display: none; } /* Hidden on screen */
    .print-signatories { display: none; }
    
    @media print {
        @page { size: landscape; margin: 8mm; }
        
        /* Force white background and reset fonts */
        html, body { 
            background: white !important; 
            margin: 0 !important; 
            padding: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            zoom: 1 !important;
            transform: none !important;
            height: auto !important;
            min-height: unset !important;
        }
        
        /* Reset the viewport zoom fix used on screen */
        .cal-page {
            height: auto !important;
            overflow: visible !important;
        }

        /* Hide EVERY application UI element */
        .sidebar, 
        .mobile-topbar, 
        .scene-layer, 
        .sticky-top-section,
        .unified-header-card > *:not(.cal-main), 
        .unified-header-divider,
        .cal-topbar, 
        .cal-header-row, 
        .cal-controls, 
        .instructions-panel, 
        .att-legend,
        .indiv-head,
        #actionDropdown,
        button, 
        .options-wrap,
        a[href] { display: none !important; }

        /* Reset the main card to be transparent and full width */
        .unified-header-card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Main container fixes */
        .main-content { 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            overflow: visible !important; 
        }
        
        .cal-main { 
            margin: 0 !important; 
            width: 100% !important; 
            display: block !important;
        }
        
        /* Show and format print header */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 0px !important;
            font-size: 11pt;
            line-height: 1.3;
            color: black !important;
            position: relative;
        }
        .print-header img { width: 75px; height: 75px; margin-bottom: 8px; }
        .print-header-toptext { font-family: Arial, sans-serif; margin-bottom: 12px; }
        .print-header-toptext strong { font-size: 14pt; display: block; margin-top: 3px; }
        
        .print-header-meta {
            display: flex;
            justify-content: space-between;
            text-align: left;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 3px solid #000;
            border-bottom: none !important;
            padding: 6px 12px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .cal-content-area, .cal-main {
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Table fixes for printing */
        .att-table-card { 
            border: none !important; 
            box-shadow: none !important; 
            border-radius: 0 !important; 
            background: white !important; 
            padding: 0 !important;
            overflow: visible !important;
        }
        
        .att-table-wrap {
            max-height: none !important;
            overflow: visible !important;
        }
        
        .att-table { 
            border-collapse: collapse !important; 
            width: 100% !important; 
            table-layout: auto !important;
            border: 3px solid black !important;
            box-sizing: border-box !important;
        }
        
        .att-table th, .att-table td { 
            border: 1px solid black !important; 
            color: black !important; 
            padding: 2px !important;
            font-size: 8pt !important;
            position: static !important;
            box-sizing: border-box !important;
        }
        
        .att-table tr:last-child td {
            border-bottom: 3px solid black !important;
        }

        .producers-col {
            position: relative !important;
            background: white !important;
            color: black !important;
            border: 1px solid black !important; 
            border-right: 3px solid black !important;
            box-shadow: none !important;
            min-width: auto !important;
        }
        
        td.producers-col {
            border-bottom: 3px solid black !important;
        }

        .producers-sidebar-print { 
            display: block !important; 
            position: fixed;
            top: 285px;
            right: 0;
            width: 110px;
            padding: 6px 8px;
            text-align: left;
            white-space: normal;
            word-break: break-word;
            font-size: 0.48rem;
            color: #374151;
            line-height: 1.3;
            font-weight: 500;
            box-sizing: border-box;
            z-index: 1000;
        }
        
        /* Hide the original text in the tbody during print to avoid doubling */
        tbody .producers-col > div {
            display: none !important;
        }



        
        /* Reset header colors */
        .head-main th { 
            background: #f8fafc !important; 
            color: black !important; 
            font-weight: 800 !important;
            border: 1px solid black !important;
        }
        
        .head-sub th { 
            border: 1px solid black !important;
        }
        
        .head-att th {
            background: white !important;
            font-size: 7pt !important;
            border: 1px solid black !important;
        }

        .station-header th { 
            background: #f1f5f9 !important; 
            color: black !important; 
            font-size: 9pt !important; 
            text-align: left !important;
            padding-left: 10px !important;
        }
        
        /* Badge and Input sanitation for print */
        .badge-present, .badge-absent, .badge-late, .badge-ut { 
            background: transparent !important; 
            color: black !important; 
            border: none !important;
            padding: 0 !important;
            font-weight: bold !important;
        }
        
        .cell-input { 
            border: none !important; 
            background: transparent !important; 
            color: black !important;
            text-align: center !important;
            width: 100% !important;
            font-weight: bold !important;
        }

        /* Hide weekend backgrounds */
        .td-day.cell-weekend { background: #fafafa !important; }
        .td-day.cell-holiday { background: #fdf2f2 !important; }

        /* Signatories */
        .print-signatories { 
            display: flex !important; 
            margin-top: 40px; 
            justify-content: space-between; 
            width: 100%; 
            page-break-inside: avoid; 
            color: black !important; 
            font-family: Arial, sans-serif; 
            font-size: 11pt; 
            padding: 0 50px;
        }
        .sig-block { 
            text-align: center; 
        }
        .sig-title { 
            font-weight: bold; 
            margin-bottom: 40px; 
            text-align: left; 
        }
        .sig-line { 
            border-bottom: 1px solid black; 
            width: 300px; 
            margin-bottom: 5px; 
        }
    }

    /* ── Screen Responsive ── */
    @media screen and (max-width: 768px) {
        .unified-header-card {
            padding: 12px 16px;
        }

    @media screen and (max-width: 768px) {
        .cal-page {
            max-height: none;
            overflow: auto;
        }

        .cal-topbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .cal-header-row {
            grid-template-columns: 1fr;
        }

        .cal-controls {
            justify-content: flex-start;
        }

        .cal-search-box {
            min-width: 100%;
            max-width: 100%;
        }

        /* Table: horizontal scroll */
        .att-table-card {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .att-table {
            min-width: 700px;
        }
    }

    @media screen and (max-width: 480px) {
        .cal-topbar {
            gap: 8px;
        }

        .date-nav-group {
            flex-wrap: wrap;
        }

        .options-btn,
        .mode-tog-btn {
            font-size: 0.72rem;
            padding: 6px 12px;
        }
    }
</style>
@endsection

@section('content')
@php
    /* ── Month Navigation ── */
    $prevMonth = $month - 1; $prevYear = $year;
    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
    $nextMonth = $month + 1; $nextYear = $year;
    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

    /* ── Group employees by station ── */
    $grouped = collect($employees)->groupBy(fn($e) => trim($e->station ?: '—'));

    /* ── Total tbody rows = employee rows (for rowspan) ── */
    $totalRows = count($employees);
    $firstEmployeeRow = true;
@endphp

<div class="cal-page">
    <div class="sticky-top-section animate-fade">
        <div class="page-header">
            {{-- Left: Title & Subtitle --}}
            <div style="flex-shrink: 0; min-width: 200px;">
                <h1>Attendance Matrix</h1>
                <p style="font-size: 0.8rem; color: #64748b; margin: 2px 0 0; font-weight: 500;">
                    Manage personnel logs for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                </p>
            </div>

            {{-- Center: View Selector --}}
            <div class="view-tabs-pill" id="viewTabsPill">
                <div class="tab-indicator" id="tabIndicator"></div>
                <a href="{{ route('admin.calendar', ['mode' => 'batch', 'year' => $year, 'month' => $month]) }}"
                    class="view-tab {{ $mode === 'batch' ? 'active' : '' }}">
                    <i class="fas fa-table-cells" style="margin-right: 4px;"></i> Batch
                </a>
                <a href="{{ route('admin.calendar', ['mode' => 'individual', 'year' => $year, 'month' => $month]) }}"
                    class="view-tab {{ $mode === 'individual' ? 'active' : '' }}">
                    <i class="fas fa-user" style="margin-right: 4px;"></i> Individual
                </a>
            </div>

            {{-- Right: Navigation Pill --}}
            <div class="cal-nav-pill">
                <div class="date-nav-group-pill">
                    <a href="{{ route('admin.calendar', ['mode' => $mode, 'year' => $prevYear, 'month' => $prevMonth]) }}"
                        class="month-nav-btn" style="color: #94a3b8; text-decoration: none;"><i class="fas fa-chevron-left" style="font-size: 0.75rem;"></i></a>
                    <span class="month-display-label" style="font-size: 0.85rem; font-weight: 800; min-width: 90px; text-align: center;">{{ date('M Y', mktime(0, 0, 0, $month, 1, $year)) }}</span>
                    <a href="{{ route('admin.calendar', ['mode' => $mode, 'year' => $nextYear, 'month' => $nextMonth]) }}"
                        class="month-nav-btn" style="color: #94a3b8; text-decoration: none;"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i></a>
                </div>

                <div style="height: 24px; width: 1.5px; background: #e2e8f0; margin: 0 4px;"></div>

                <div class="options-wrap">
                    <button class="options-btn" onclick="toggleDropdown()" style="display: inline-flex; align-items: center; gap: 7px; padding: 8px 18px; background: #3b82f6; color: white; border: none; border-radius: 50px; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                        <i class="fas fa-bolt"></i> Actions <i class="fas fa-chevron-down" style="font-size:0.65rem; margin-left: 2px;"></i>
                    </button>
                    <div id="actionDropdown" class="options-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #f1f5f9; z-index: 1000; min-width: 200px; padding: 8px;">
                        @if($mode === 'batch')
                            <button onclick="submitBatch()" class="opt-item" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; color: #22c55e; cursor: pointer;">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        @endif
                        <button onclick="openPrintModal()" class="opt-item" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; color: #475569; cursor: pointer;">
                            <i class="fas fa-print"></i> Print Records
                        </button>
                        <button onclick="openExportModal()" class="opt-item" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; color: #10b981; cursor: pointer;">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                        <div style="height: 1px; background: #f1f5f9; margin: 6px 10px;"></div>
                        <button onclick="openHolidayModal()" class="opt-item" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; color: #f59e0b; cursor: pointer;">
                            <i class="fas fa-calendar-plus"></i> Set Holidays
                        </button>
                        <button onclick="removeAllHolidays()" class="opt-item" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; color: #ef4444; cursor: pointer;">
                            <i class="fas fa-calendar-minus"></i> Remove Holidays
                        </button>
                        <button onclick="confirmClear()" class="opt-item" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 14px; border: none; background: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                            <i class="fas fa-trash-alt"></i> Reset Month
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters Row --}}
        <div style="display: flex; gap: 14px; margin-top: 14px;">
            <div id="calSearchBox"
                style="flex: 1; display: flex; align-items: center; background: white; border: 1.5px solid #e5e7eb; border-radius: 50px; padding: 0 16px; transition: all 0.2s;">
                <i class="fas fa-search" style="color: #94a3b8; font-size: 0.85rem;"></i>
                <input type="text" id="searchInput" placeholder="Search employee name..."
                    style="border: none; outline: none; background: transparent; font-size: 0.82rem; font-family: inherit; color: #1e293b; flex: 1; padding: 10px 12px; font-weight: 500;"
                    autocomplete="off">
            </div>

            <div style="background: white; border-radius: 50px; border: 1.5px solid #edf2f7; padding: 2px 14px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-filter" style="color: #94a3b8; font-size: 0.8rem;"></i>
                <select id="calStationFilter" onchange="filterCalTable()" 
                    style="border: none; background: transparent; font-size: 0.82rem; font-weight: 700; color: #475569; outline: none; cursor: pointer; padding: 8px 0;">
                    <option value="">All Stations</option>
                    @foreach($grouped->keys() as $st)
                        <option value="{{ strtolower($st) }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="cal-content-area" style="flex: 1; min-height: 0; padding: 0 16px 16px;">
        <div class="cal-main" style="display: flex; flex-direction: column; height: 100%;">

        @if($mode === 'batch' || ($mode === 'individual' && $empId))

            {{-- PROCEDURES SIDEBAR FOR PRINT (Repeats on every page flawlessly) --}}
            <div class="producers-sidebar-print" style="display:none;">
                1. Cross out unnecessary dates e.g.<br>
                2. All entries shall be based on the individual DTR or Time Card;<br>
                3. Indicate the presence of the employee by a (/) mark in the date column;<br>
                4. Enter absences in terms of days in the date column either 1/2 or 1 whole day. Leave absence shall be attached to the attendance report form;<br>
                5. Enter tardiness in the date column in terms of minutes, halfdays or under times. Enclose the late/halfday/undertime entries by parenthesis; etc.
            </div>

            {{-- PRINT ONLY HEADER (S.P. FORM A) --}}
            <div class="print-header">
                <div style="position: absolute; top: 0; right: 0; font-family: Arial, sans-serif; font-size: 10pt; font-weight: bold; font-style: italic;">S.P. FORM A</div>
                <img src="{{ asset('logo.png') }}" alt="SDO-QC Logo" onerror="this.style.display='none'">
                <div class="print-header-toptext">
                    Republic of the Philippines<br>
                    Department of Education<br>
                    National Capital Region<br>
                    <strong>SCHOOLS DIVISION OFFICE, QUEZON CITY</strong>
                </div>
                
                <div class="print-header-meta">
                    <div class="print-header-meta-left" style="display:flex; flex-direction:column; gap:6px; justify-content:center;">
                        <div style="font-weight: 800; font-size: 11pt;">NAME OF SCHOOL: <span style="font-weight:normal;">SCHOOLS DIVISION OFFICE OF QUEZON CITY</span></div>
                        <div style="font-size: 11pt; font-weight: 800;">STATION: <span style="font-weight:normal;" id="printStationNameDisplay">DIVISION OFFICE - STATION X</span></div>
                    </div>
                    <div class="print-header-meta-center" style="letter-spacing: 1px; margin-top: 15px;">
                        ATTENDANCE REPORT
                    </div>
                    <div class="print-header-meta-right">
                        <div>PERIOD COVERED:</div>
                        <div style="font-size: 11pt;">{{ strtoupper(date('F 1-t, Y', mktime(0, 0, 0, $month, 1, $year))) }}</div>
                    </div>
                </div>
            </div>
        @endif

        @if($mode === 'individual' && !$empId)
            {{-- EMPLOYEE SELECTOR GRID --}}
            <div class="indiv-selector-wrap">
                <h2>Select an Employee to Manage</h2>
                <p>Choose an employee from the list below to view their individual attendance calendar.</p>
                
                @foreach($grouped as $station => $stationEmps)
                <div class="indiv-station-section" data-station="{{ strtolower($station) }}" style="margin-bottom: 35px;">
                    <div class="station-container">
                        <h3 style="font-size:1.1rem; color:#1e293b; margin-bottom: 16px; text-align:center; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;"><i class="fas fa-map-marker-alt" style="color:#3b82f6; margin-right:8px;"></i>{{ $station }}</h3>
                        <div class="emp-grid">
                            @foreach($stationEmps as $emp)
                                <a href="{{ route('admin.calendar', ['mode' => 'individual', 'year' => $year, 'month' => $month, 'emp_id' => $emp->id]) }}" class="emp-card" data-name="{{ strtolower(($emp->last_name ?? '').' '.($emp->first_name ?? '')) }}" data-station="{{ strtolower($station) }}">
                                    <div class="emp-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="emp-card-name">{{ strtoupper($emp->last_name ?? '') }}, {{ $emp->first_name ?? '' }}</div>
                                        <div class="emp-card-id">{{ str_pad($emp->emp_number ?? $emp->id, 3, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @elseif($mode === 'individual' && $empId)
            {{-- INDIVIDUAL CALENDAR GRID --}}
            <div class="indiv-head">
                <div class="indiv-emp-info">
                    <h2>{{ $selectedEmp ? strtoupper($selectedEmp->last_name) . ', ' . $selectedEmp->first_name : 'Unknown Employee' }}</h2>
                    <span><i class="fas fa-id-badge"></i> ID: {{ str_pad($empId, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="indiv-actions">
                    <button class="btn-outline-red" data-emp="{{ $empId }}" data-date="{{ date('Y-m-d') }}" onclick="quickAbsent(this.getAttribute('data-emp'), this.getAttribute('data-date'))">
                        <i class="fas fa-user-xmark"></i> Mark Absent
                    </button>
                    <button class="mbtn mbtn-primary" onclick="alert('Feature coming soon: Bulk save individual calendar')">
                        <i class="fas fa-save"></i> Save Calendar
                    </button>
                </div>
            </div>

            <div class="cal-grid">
                @php
                    $firstDayOfMonthTs = mktime(0, 0, 0, $month, 1, $year);
                    $startingDow = (int) date('w', $firstDayOfMonthTs);
                    // Add empty cells for padding
                    for ($i = 0; $i < $startingDow; $i++) {
                        echo '<div class="cal-grid-cell empty"></div>';
                    }
                @endphp

                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $ts        = mktime(0, 0, 0, $month, $d, $year);
                        $dow       = (int) date('w', $ts);
                        $dowStr    = strtoupper(substr(date('D', $ts), 0, 3));
                        $isWeekend = ($dow === 0 || $dow === 6);
                        $isHoliday = isset($holidays[$d]);
                        $status    = $attendance[$empId][$d] ?? '';
                        
                        $cellCls = '';
                        if ($status === 'present') $cellCls = 'present';
                        elseif ($status === 'absent' || $status === 'halfday') $cellCls = 'absent';
                        elseif ($status === 'late' || $status === 'undertime') $cellCls = 'late';
                    @endphp

                    @if($isHoliday)
                        <div class="cal-grid-cell holiday" onclick="openHolidayModal({{ $d }}, '{{ addslashes($holidays[$d]) }}')">
                            <div class="cell-head">
                                <div class="cell-dow" style="color: #ca8a04;">{{ $dowStr }}</div>
                                <div class="cell-date" style="color: #854d0e;">{{ $d }}</div>
                            </div>
                            <div style="text-align: center; padding-bottom: 5px;">
                                <i class="fas fa-umbrella-beach" style="color: #854d0e; font-size: 1.5rem; margin-bottom: 5px;"></i>
                                <div class="holiday-label">{{ $holidays[$d] }}</div>
                            </div>
                        </div>
                    @else
                        @php
                            $absReason = $reasons[$empId][$d] ?? '';
                        @endphp
                        <div class="cal-grid-cell {{ $cellCls }}" id="cell-{{ $d }}">
                            <div class="cell-head">
                                <div class="cell-dow">{{ $dowStr }}</div>
                                <div class="cell-date">{{ $d }}</div>
                            </div>
                            <div>
                                <select class="cell-select" onchange="updateCellColor(this, '{{ $empId }}', '{{ $d }}', '{{ $ym }}')">
                                    @if($isWeekend)
                                        <option value="" {{ $status === '' ? 'selected' : '' }}>-</option>
                                    @endif
                                    <option value="present" {{ ($status === 'present' || (!$isWeekend && $status === '')) ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ $status === 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="halfday" {{ $status === 'halfday' ? 'selected' : '' }}>Halfday</option>
                                    <option value="late" {{ $status === 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="undertime" {{ $status === 'undertime' ? 'selected' : '' }}>Undertime</option>
                                </select>
                                @if($absReason && ($status === 'absent' || $status === 'halfday'))
                                    <div style="margin-top:4px; text-align:center; font-size:0.62rem; font-weight:800; color:#dc2626; text-transform:uppercase; letter-spacing:0.3px; line-height:1.2; word-break:break-word;">
                                        {{ $absReason }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endfor

                @php
                    $totalCellsUsed = $startingDow + $daysInMonth;
                    $remainingCells = 42 - $totalCellsUsed; // Always 6 rows of 7 columns
                    for ($i = 0; $i < $remainingCells; $i++) {
                        echo '<div class="cal-grid-cell empty"></div>';
                    }
                @endphp
            </div>

        @elseif($mode === 'batch')
            {{-- BATCH Attendance Table --}}
            <div class="att-table-card">
            {{-- MAIN ATTENDANCE TABLE --}}
            <div class="att-table-wrap">
                <table class="att-table" id="batchTable">
                    <thead>
                        <tr class="head-main">
                            <th style="width: 25px;" rowspan="2" class="col-no">NO.</th>
                            <th style="width: 145px;" rowspan="2" class="col-name">NAME OF EMPLOYEE</th>
                            @for($d=1; $d<=$daysInMonth; $d++)
                                @php
                                    $ts = mktime(0,0,0,$month,$d,$year);
                                    $dow = (int)date('w',$ts);
                                    $isWeekend = ($dow === 0 || $dow === 6);
                                    $isHoliday = isset($holidays[$d]);
                                    $cls = $isHoliday ? 'day-holiday' : ($isWeekend ? 'day-weekend' : '');
                                @endphp
                                <th style="width: 23px;" class="day-head {{ $cls }}">
                                    {{ $d }}
                                </th>
                            @endfor
                            <th style="width: 60px;" colspan="2" class="att-group-header">ABSENCE</th>
                            <th rowspan="2" class="producers-col" style="width:110px; min-width:110px; max-width:110px; position:sticky; right:0; z-index:30; background:linear-gradient(135deg,#3b82f6,#2563eb); color:white; font-size:0.48rem; text-align:left; vertical-align:top; font-weight:800; text-transform:uppercase; letter-spacing:0.2px; line-height:1.15; padding:6px 8px; border-left:2px solid #2563eb; border-right:none; box-shadow:-2px 0 5px rgba(0,0,0,0.05);">
                                PROCEDURES IN <br>ACCOMPLISHING<br>ATTENDANCE REPORT(AR)
                            </th>
                        </tr>
                        <tr class="head-sub">
                            @for($d=1; $d<=$daysInMonth; $d++)
                                @php
                                    $ts = mktime(0,0,0,$month,$d,$year);
                                    $dow = (int)date('w',$ts);
                                    $isWeekend = ($dow === 0 || $dow === 6);
                                    $isHoliday = isset($holidays[$d]);
                                    $cls = $isHoliday ? 'day-holiday' : ($isWeekend ? 'day-weekend' : '');
                                    $dowStr = strtoupper(date('D',$ts));
                                @endphp
                                <th style="width: 23px;" class="{{ $cls }}">
                                    <span>{{ $dowStr }}</span>
                                </th>
                            @endfor
                            <th style="width: 30px;" title="Absence With Pay">WP</th>
                            <th style="width: 30px;" title="Absence Without Pay">WOP</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTbody">
                        @foreach($grouped as $station => $stationEmps)
                            {{-- Employee rows --}}
                            @foreach($stationEmps as $j => $emp)
                                @php
                                    /* Per-employee absence summary */
                                    $absWithPay    = 0;
                                    $absWithoutPay = 0;
                                    $empWithPayLogs = []; $empWopLogs = [];
                                    for ($d2 = 1; $d2 <= $daysInMonth; $d2++) {
                                        // Skip weekends for summary counts
                                        $ts_check = mktime(0, 0, 0, $month, $d2, $year);
                                        $dow_check = (int) date('w', $ts_check);
                                        if ($dow_check === 0 || $dow_check === 6) continue;

                                        $rsn = $reasons[$emp->id][$d2] ?? '';
                                        if ($rsn) {
                                            $logEntry = ['day' => $d2, 'type' => $rsn];
                                            if (stripos($rsn, 'without') !== false) { $absWithoutPay++; $empWopLogs[] = $logEntry; }
                                            else { $absWithPay++; $empWithPayLogs[] = $logEntry; }
                                        }
                                    }
                                @endphp
                                <tr class="emp-row" data-name="{{ strtolower(($emp->last_name ?? '').' '.($emp->first_name ?? '')) }}" data-station="{{ strtolower($station) }}">
                                    <td class="td-no">{{ $j + 1 }}</td>
                                    <td class="td-name" ondblclick="const s = this.querySelector('.station-info'); s.style.display = (s.style.display==='none'?'block':'none');">
                                        <div style="font-weight: 800; font-size: 0.82rem; color: #111827;">
                                            {{ strtoupper($emp->last_name ?? '') }}, {{ $emp->first_name ?? '' }} {{ $emp->middle_name ? substr(trim($emp->middle_name), 0, 1) . '.' : '' }}
                                        </div>
                                        <div class="station-info" style="font-size: 0.58rem; color: #64748b; font-weight: 600; margin-top: 3px; display: none;">
                                            <i class="fas fa-map-marker-alt" style="color: #3b82f6; margin-right: 2px;"></i>{{ $emp->station ?: 'No Station Assigned' }}
                                        </div>
                                    </td>

                                    @php
                                        // Build lookup: which days are covered by a merge for this employee?
                                        $empMerges   = $merges[$emp->id] ?? [];
                                        $skipUntil   = 0; // skip days <= this value (inside a merge)
                                    @endphp

                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            // ── Is this day the START of a saved merge? ──────────
                                            $merge = $empMerges[$d] ?? null;

                                            if ($merge) {
                                                // Count how many table columns this merge spans
                                                // (all days from start_day to end_day inclusive)
                                                $mergeColspan = $merge['end_day'] - $merge['start_day'] + 1;
                                                $skipUntil    = $merge['end_day'];

                                                // Style maps (must mirror the JS STATUS_ maps)
                                                $bgMap = [
                                                    '/'   => '#dcfce7', 'HOL' => '#ffe4e6', 'A' => '#fee2e2',
                                                    'SL'  => '#fef3c7', 'VL' => '#e0f2fe', 'AL' => '#f3e8ff',
                                                ];
                                                $colorMap = [
                                                    '/'   => '#15803d', 'HOL' => '#e11d48', 'A' => '#dc2626',
                                                    'SL'  => '#b45309', 'VL' => '#0369a1', 'AL' => '#7c3aed',
                                                ];
                                                $mBg    = $bgMap[$merge['label']]    ?? '#f1f5f9';
                                                $mColor = $colorMap[$merge['label']] ?? '#475569';

                                                // Extract emp_id & start_day for JS unmerge reference
                                                $mEmpId    = $emp->id;
                                                $mStartDay = $merge['start_day'];
                                                $mLabel    = $merge['label'];
                                                $mReason   = $merge['reason'] ?? '';
                                            } elseif ($d <= $skipUntil) {
                                                // This day is inside a merge range — skip it entirely
                                                continue;
                                            } else {
                                                $merge = null; // normal day
                                            }

                                            // Normal day calculations (used when $merge is null)
                                            $status    = $attendance[$emp->id][$d] ?? '';
                                            $ts        = mktime(0, 0, 0, $month, $d, $year);
                                            $dow       = (int) date('w', $ts);
                                            $isWeekend = ($dow === 0 || $dow === 6);
                                            $isSat     = ($dow === 6);
                                            $isHoliday = isset($holidays[$d]);

                                            if ($isHoliday) {
                                                $cellCls = 'cell-holiday'; $sym = $holidays[$d]; $fixed = true;
                                            } elseif ($isSat) {
                                                $cellCls = 'cell-weekend'; $sym = 'SAT'; $fixed = true;
                                            } elseif ($dow === 0) {
                                                $cellCls = 'cell-weekend'; $sym = 'SUN'; $fixed = true;
                                            } else {
                                                $cellCls = '';
                                                $statusMapForSym = ['present' => '/', 'absent' => '1', 'late' => 'L', 'undertime' => 'U', 'halfday' => 'H'];
                                                $sym = $statusMapForSym[$status] ?? '/';
                                                $fixed = false;
                                            }
                                        @endphp

                                        @if($merge)
                                            @php
                                                $mergeDblClick = "unmergeServerCell(this, {$mEmpId}, {$mStartDay})";
                                                $mShadowColor  = $mColor . '40';
                                            @endphp
                                            {{-- ── Persisted merged cell ─────────────────────── --}}
                                            <td class="td-day" colspan="{{ $mergeColspan }}"
                                                style="padding:0;"
                                                data-day="{{ $mStartDay }}"
                                                data-merge-emp="{{ $mEmpId }}"
                                                data-merge-start="{{ $mStartDay }}">
                                                <div class="merged-span"
                                                    style="background: {{ $mBg }}; color: {{ $mColor }}; box-shadow: inset 0 0 0 1px {{ $mShadowColor }};"
                                                    title="Double-click to unmerge"
                                                    ondblclick="{{ $mergeDblClick }}">
                                                    <span style="font-size:1.0rem;font-weight:800;">{{ $mLabel }}</span>
                                                    @if($mReason)
                                                        <span style="font-size:0.58rem;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;margin-top:2px;max-width:90%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $mReason }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                        @else
                                            <td class="td-day {{ $cellCls }}" data-day="{{ $d }}">
                                                @if($mode === 'batch' && !$fixed)
                                                    <input type="text"
                                                        name="attendance[{{ $emp->id }}][{{ $d }}]"
                                                        value="{{ $sym }}"
                                                        class="cell-input"
                                                        onchange="handleCellChange(this, '{{ $emp->id }}', '{{ $d }}')">
                                                @else
                                                    @php
                                                        $dayReason = $reasons[$emp->id][$d] ?? '';
                                                    @endphp
                                                    @if($fixed)
                                                        @if($isHoliday)
                                                            <div onclick="openHolidayModal({{ $d }}, '{{ addslashes($sym) }}')" style="display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; width: 100%; height: 100%;">
                                                                <span style="font-size: 0.45rem; font-weight: 800; color: #854d0e; display: block; white-space: normal; line-height: 1.1; word-break: break-word; text-align: center;">{{ $sym }}</span>
                                                            </div>
                                                        @else
                                                            <span style="font-size:0.6rem;font-weight:700;" class="text-normal">{{ $sym }}</span>
                                                        @endif
                                                    @elseif($status === 'present')
                                                        <span class="badge-present">/</span>
                                                    @elseif($status === 'absent')
                                                        <span class="badge-absent" @if($dayReason) title="{{ $dayReason }}" @endif>1</span>
                                                    @elseif($status === 'late')
                                                        <span class="badge-late">L</span>
                                                    @elseif($status === 'undertime')
                                                        <span class="badge-ut">U</span>
                                                    @elseif($status === 'halfday')
                                                        <span class="badge-absent" @if($dayReason) title="{{ $dayReason }}" @endif>½</span>
                                                    @else
                                                        <span style="color:#d1d5db;">·</span>
                                                    @endif
                                                @endif
                                            </td>
                                        @endif
                                    @endfor

                                    {{-- Absence summary --}}
                                    <td class="td-abs" style="border-left:2px solid #f1f5f9; cursor:pointer;" 
                                        data-tardy="{{ json_encode($empWithPayLogs) }}"
                                        data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                        onclick="showAbsencePreview(this, 'With Pay Absences')"
                                        title="Click to view dates">
                                        @if($absWithPay > 0)
                                            <span class="badge-absent">{{ $absWithPay }}</span>
                                        @else
                                            <span style="color:#d1d5db;">—</span>
                                        @endif
                                    </td>
                                    <td class="td-abs" style="cursor:pointer;"
                                        data-tardy="{{ json_encode($empWopLogs) }}"
                                        data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                        onclick="showAbsencePreview(this, 'Without Pay Absences')"
                                        title="Click to view dates">
                                        @if($absWithoutPay > 0)
                                            <span class="badge-absent">{{ $absWithoutPay }}</span>
                                        @else
                                            <span style="color:#d1d5db;">—</span>
                                        @endif
                                    </td>

                                    @if($firstEmployeeRow)
                                        @php $firstEmployeeRow = false; @endphp
                                        <td rowspan="{{ $totalRows }}" class="producers-col"
                                            style="width:110px; min-width:110px; max-width:110px; position:sticky; right:0; z-index:10; border-left:2px solid #e5e7eb; border-right:none; vertical-align:top; padding:0; background:#fafbff; box-shadow:-2px 0 5px rgba(0,0,0,0.03);">
                                            <div style="margin:0; padding:4px 6px; text-align:left; white-space:normal; word-break:break-word; font-size:0.48rem; color:#374151; line-height:1.3; font-weight:500;">
                                                1. Cross out unnecessary dates e.g.<br>
                                                2. All entries shall be based on the individual DTR or Time Card;<br>
                                                3. Indicate the presence of the employee by a (/) mark in the date column;<br>
                                                4. Enter absences in terms of days in the date column either 1/2 or 1 whole day. Leave absence shall be attached to the attendance report form;<br>
                                                5. Enter tardiness in the date column in terms of minutes, halfdays or under times. Enclose the late/halfday/undertime entries by parenthesis; etc.
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="att-legend">
                <i class="fas fa-circle-info" style="color:#3b82f6;"></i>
                <span><span class="badge-present" style="font-size:0.75rem;">/</span> Present</span>
                <span><span class="badge-absent">1</span> Absent</span>
                <span><span class="badge-late">L</span> Late</span>
                <span><span class="badge-ut">U</span> Under Time</span>
                <span style="font-size:0.65rem;color:#94a3b8;font-style:italic;">SAT / SUN = Weekends &nbsp;·&nbsp; HOL = Holiday</span>
            </div>

            {{-- Signatories (Print Only) --}}
            <div class="print-signatories" style="display: none; justify-content: space-between; gap: 40px; margin-top: 30px; text-align: center;">
                <div class="sig-block" style="flex: 1;">
                    <div class="sig-title" style="margin-bottom: 30px; font-weight: 500;">Prepared by:</div>
                    <div id="sig-prepared-name" class="sig-line" style="font-weight: 800; border-bottom: 2px solid #000; margin-bottom: 5px; height: auto; min-height: 22px; text-transform: uppercase;"></div>
                    <div id="sig-prepared-pos" style="font-size: 0.72rem; font-weight: 600; color: #4b5563;">Signature over Printed Name</div>
                    <div id="sig-prepared-pos2" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                    <div id="sig-prepared-pos3" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                </div>
                <div class="sig-block" style="flex: 1;">
                    <div class="sig-title" style="margin-bottom: 30px; font-weight: 500;">Certified Correct by:</div>
                    <div id="sig-certified-name" class="sig-line" style="font-weight: 800; border-bottom: 2px solid #000; margin-bottom: 5px; height: auto; min-height: 22px; text-transform: uppercase;"></div>
                    <div id="sig-certified-pos" style="font-size: 0.72rem; font-weight: 600; color: #4b5563;">Human Resource Management Officer II</div>
                    <div id="sig-certified-pos2" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                    <div id="sig-certified-pos3" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                </div>
                <div class="sig-block" style="flex: 1;">
                    <div class="sig-title" style="margin-bottom: 30px; font-weight: 500;">Verified Correct by:</div>
                    <div id="sig-verified-name" class="sig-line" style="font-weight: 800; border-bottom: 2px solid #000; margin-bottom: 5px; height: auto; min-height: 22px; text-transform: uppercase;"></div>
                    <div id="sig-verified-pos" style="font-size: 0.72rem; font-weight: 600; color: #4b5563;">Human Resource Management Officer V</div>
                    <div id="sig-verified-pos2" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                    <div id="sig-verified-pos3" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>{{-- content area --}}

{{-- ══ MODALS ══════════════════════════════════════════════════════ --}}

{{-- Add Holiday Modal --}}
<div id="holidayModal" class="custom-overlay">
    <div class="custom-box" style="max-width:400px;text-align:left;">
        <h2 style="margin-bottom:18px;"><i class="fas fa-umbrella-beach" style="color:#f59e0b;margin-right:8px;"></i>Add Special Day / Holiday</h2>
        <form id="holidayForm" data-route="{{ route('admin.calendar.addHoliday') }}" onsubmit="submitForm(event, this.getAttribute('data-route'), 'holidayModal')">
            <div class="form-group"><label>Date</label><input type="date" name="holiday_date" required></div>
            <div class="form-group"><label>Description / Reason</label><input type="text" name="holiday_reason" required placeholder="e.g. Regular Holiday"></div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;">
                <button type="button" id="holidayRemoveBtn" class="mbtn" style="background:#fef2f2;color:#ef4444;border:1px solid #fee2e2;display:none;" onclick="removeHolidayFromModal()">
                    <i class="fas fa-trash-alt"></i> Remove
                </button>
                <div style="display:flex;gap:8px;margin-left:auto;">
                    <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('holidayModal')">Cancel</button>
                    <button type="submit" class="mbtn mbtn-amber"><i class="fas fa-save"></i> Save Holiday</button>
                </div>
            </div>
        </form>
    </div>
</div>


{{-- Print Selection Modal --}}
<div id="printModal" class="custom-overlay">
    <div class="custom-box" style="width:1000px; text-align:left; padding:30px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h2 style="margin:0;"><i class="fas fa-print" style="color:#0f766e;margin-right:8px;"></i>Print Options</h2>
            <button onclick="closeModal('printModal')" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
        </div>
        <form id="printForm">
            <p style="color:#64748b;font-size:0.85rem;margin-bottom:12px;">Select which station's records you want to print.</p>
            <div class="form-group">
                <label>Select Station</label>
                <select id="printStationSelect">
                    <option value="all">All Stations</option>
                    @foreach($grouped->keys() as $st)
                        <option value="{{ strtolower($st) }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px; border-top: 2px solid #f1f5f9; padding-top: 20px;">
                {{-- Prepared By --}}
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Prepared By</div>
                    <div class="form-group">
                        <label>Signatory Name</label>
                        <input type="text" id="printSigPrepName" placeholder="CHRISTINE JOY C. MAAPOY" style="font-weight:700; text-transform:uppercase;">
                    </div>
                    <div id="print-prep-pos1">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 1
                            <button type="button" onclick="addPosField('print', 'prep')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                        </label>
                        <input type="text" id="printSigPrepPos" placeholder="Administrative Assistant III">
                    </div>
                    <div id="print-prep-pos2" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 2
                            <button type="button" onclick="removePosField('print', 'prep', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="printSigPrepPos2" placeholder="E-Form7 In-Charge">
                    </div>
                    <div id="print-prep-pos3" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 3
                            <button type="button" onclick="removePosField('print', 'prep', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="printSigPrepPos3" placeholder="...">
                    </div>
                </div>

                {{-- Certified By --}}
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Certified Correct By</div>
                    <div class="form-group">
                        <label>Signatory Name</label>
                        <input type="text" id="printSigCertName" placeholder="MICHELLE A. MAL-IN" style="font-weight:700; text-transform:uppercase;">
                    </div>
                    <div id="print-cert-pos1">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 1
                            <button type="button" onclick="addPosField('print', 'cert')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                        </label>
                        <input type="text" id="printSigCertPos" placeholder="HRMO II">
                    </div>
                    <div id="print-cert-pos2" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 2
                            <button type="button" onclick="removePosField('print', 'cert', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="printSigCertPos2" placeholder="Administrative Officer IV">
                    </div>
                    <div id="print-cert-pos3" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 3
                            <button type="button" onclick="removePosField('print', 'cert', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="printSigCertPos3" placeholder="...">
                    </div>
                </div>

                {{-- Verified By --}}
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Verified Correct By</div>
                    <div class="form-group">
                        <label>Signatory Name</label>
                        <input type="text" id="printSigVerName" placeholder="ROSELYN B. SENCIL" style="font-weight:700; text-transform:uppercase;">
                    </div>
                    <div id="print-ver-pos1">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 1
                            <button type="button" onclick="addPosField('print', 'ver')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                        </label>
                        <input type="text" id="printSigVerPos" placeholder="HRMO V">
                    </div>
                    <div id="print-ver-pos2" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 2
                            <button type="button" onclick="removePosField('print', 'ver', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="printSigVerPos2" placeholder="Administrative Officer V">
                    </div>
                    <div id="print-ver-pos3" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 3
                            <button type="button" onclick="removePosField('print', 'ver', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="printSigVerPos3" placeholder="...">
                    </div>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('printModal')">Cancel</button>
                <button type="button" onclick="executePrint()" class="mbtn mbtn-primary" style="background: linear-gradient(135deg, #0f766e, #115e59); border:none; box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);"><i class="fas fa-print"></i> Print Now</button>
            </div>
        </form>
    </div>
</div>

{{-- Export to Excel Modal --}}
<div id="exportModal" class="custom-overlay">
    <div class="custom-box" style="width:1000px; text-align:left; padding:30px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h2 style="margin:0;"><i class="fas fa-file-excel" style="color:#10b981;margin-right:8px;"></i>Export to Excel</h2>
            <button onclick="closeModal('exportModal')" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
        </div>
        <form id="exportForm">
            <p style="color:#64748b;font-size:0.85rem;margin-bottom:12px;">Select which station's records you want to export.</p>
            <div class="form-group">
                <label>Select Station</label>
                <select id="exportStationSelect">
                    <option value="All">All Stations</option>
                    @foreach($grouped->keys() as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px; border-top: 2px solid #f1f5f9; padding-top: 20px;">
                {{-- Prepared By --}}
                <div style="background:#f0fdf4; border:1px solid #dcfce7; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="font-size:0.75rem; font-weight:800; color:#166534; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #bbf7d0; padding-bottom:8px;">Prepared By</div>
                    <div class="form-group">
                        <label>Signatory Name</label>
                        <input type="text" id="exportSigPrepName" placeholder="CHRISTINE JOY C. MAAPOY" style="font-weight:700; text-transform:uppercase;">
                    </div>
                    <div id="export-prep-pos1">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 1
                            <button type="button" onclick="addPosField('export', 'prep')" style="background:#16a34a; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                        </label>
                        <input type="text" id="exportSigPrepPos" placeholder="Administrative Assistant III">
                    </div>
                    <div id="export-prep-pos2" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 2
                            <button type="button" onclick="removePosField('export', 'prep', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="exportSigPrepPos2" placeholder="E-Form7 In-Charge">
                    </div>
                    <div id="export-prep-pos3" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 3
                            <button type="button" onclick="removePosField('export', 'prep', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="exportSigPrepPos3" placeholder="...">
                    </div>
                </div>

                {{-- Certified By --}}
                <div style="background:#f0fdf4; border:1px solid #dcfce7; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="font-size:0.75rem; font-weight:800; color:#166534; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #bbf7d0; padding-bottom:8px;">Certified Correct By</div>
                    <div class="form-group">
                        <label>Signatory Name</label>
                        <input type="text" id="exportSigCertName" placeholder="MICHELLE A. MAL-IN" style="font-weight:700; text-transform:uppercase;">
                    </div>
                    <div id="export-cert-pos1">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 1
                            <button type="button" onclick="addPosField('export', 'cert')" style="background:#16a34a; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                        </label>
                        <input type="text" id="exportSigCertPos" placeholder="HRMO II">
                    </div>
                    <div id="export-cert-pos2" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 2
                            <button type="button" onclick="removePosField('export', 'cert', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="exportSigCertPos2" placeholder="Administrative Officer IV">
                    </div>
                    <div id="export-cert-pos3" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 3
                            <button type="button" onclick="removePosField('export', 'cert', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="exportSigCertPos3" placeholder="...">
                    </div>
                </div>

                {{-- Verified By --}}
                <div style="background:#f0fdf4; border:1px solid #dcfce7; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="font-size:0.75rem; font-weight:800; color:#166534; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #bbf7d0; padding-bottom:8px;">Verified Correct By</div>
                    <div class="form-group">
                        <label>Signatory Name</label>
                        <input type="text" id="exportSigVerName" placeholder="ROSELYN B. SENCIL" style="font-weight:700; text-transform:uppercase;">
                    </div>
                    <div id="export-ver-pos1">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 1
                            <button type="button" onclick="addPosField('export', 'ver')" style="background:#16a34a; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                        </label>
                        <input type="text" id="exportSigVerPos" placeholder="HRMO V">
                    </div>
                    <div id="export-ver-pos2" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 2
                            <button type="button" onclick="removePosField('export', 'ver', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="exportSigVerPos2" placeholder="Administrative Officer V">
                    </div>
                    <div id="export-ver-pos3" style="display:none; margin-top:10px; animation:fadeIn 0.2s;">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            Position Line 3
                            <button type="button" onclick="removePosField('export', 'ver', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                        </label>
                        <input type="text" id="exportSigVerPos3" placeholder="...">
                    </div>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('exportModal')">Cancel</button>
                <button type="button" onclick="executeExport()" class="mbtn" style="background: linear-gradient(135deg, #10b981, #059669); color:white; border:none; box-shadow: 0 4px 12px rgba(16,185,129,0.3);"><i class="fas fa-file-excel"></i> Download Excel</button>
            </div>
        </form>
    </div>
</div>

{{-- Download Progress Modal --}}
<style>
.circular-loader {
  animation: rotate 2s linear infinite;
  height: 60px;
  width: 60px;
  margin: 0 auto 16px;
}
.circular-loader circle {
  stroke: #10b981;
  stroke-width: 4;
  stroke-dasharray: 1, 200;
  stroke-dashoffset: 0;
  animation: dash 1.5s ease-in-out infinite;
  stroke-linecap: round;
  fill: none;
}
@keyframes rotate { 100% { transform: rotate(360deg); } }
@keyframes dash {
  0% { stroke-dasharray: 1, 200; stroke-dashoffset: 0; }
  50% { stroke-dasharray: 89, 200; stroke-dashoffset: -35px; }
  100% { stroke-dasharray: 89, 200; stroke-dashoffset: -124px; }
}
</style>
<div id="downloadProgressModal" class="custom-overlay" style="z-index:9999;">
    <div class="custom-box" style="max-width:320px;text-align:center;padding:30px;">
        <svg class="circular-loader" id="dlSpinner" viewBox="25 25 50 50">
            <circle cx="50" cy="50" r="20"></circle>
        </svg>
        <div id="dlSuccessIcon" style="display:none; animation: fadeInScale 0.3s ease-out;">
            <i class="fas fa-check-circle" style="font-size:3.5rem;color:#10b981;margin-bottom:16px;"></i>
        </div>
        <h3 id="dlProgressTitle" style="color:#1e293b;margin-bottom:8px;">Exporting Excel...</h3>
        <p id="dlProgressText" style="color:#64748b;font-size:0.85rem;margin:0;">Generating your report, please wait</p>
    </div>
</div>

{{-- Absence Reason Modal --}}
<div id="absenceReasonModal" class="custom-overlay" style="z-index:3000;">
    <div class="custom-box" style="max-width:380px;text-align:left;">
        <h2 style="margin-bottom:18px;"><i class="fas fa-user-slash" style="color:#ef4444;margin-right:8px;"></i>Specify Absence Reason</h2>
        <p id="absenceDetails" style="margin-bottom:14px;color:#64748b;font-weight:700;font-size:0.85rem;background:#f8fafc;padding:10px;border-radius:8px;border:1px solid #f1f5f9;"></p>
        <form id="absenceReasonForm" onsubmit="submitAbsenceReason(event)">
            <input type="hidden" id="abs_emp_id" name="emp_id">
            <input type="hidden" id="abs_day"    name="day">
            <input type="hidden" name="ym"        value="{{ $ym }}">
            <div class="form-group">
                <label>Pay Type</label>
                <select name="pay_type" id="abs_pay_type" required>
                    <option value="Absence with pay">With Pay</option>
                    <option value="Absence without pay">Without Pay</option>
                </select>
            </div>
            <div class="form-group">
                <label>Reason (Optional)</label>
                <input type="text" name="custom_reason" id="abs_custom_reason" placeholder="e.g. Sick Leave, Vacation, etc.">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('absenceReasonModal')">Cancel</button>
                <button type="submit" class="mbtn mbtn-red"><i class="fas fa-save"></i> Save Reason</button>
            </div>
        </form>
    </div>
</div>

{{-- Late Minutes Modal --}}
<div id="lateMinutesModal" class="custom-overlay" style="z-index:3000;">
    <div class="custom-box" style="max-width:380px;text-align:left;">
        <h2 style="margin-bottom:18px;"><i class="fas fa-clock" style="color:#f59e0b;margin-right:8px;"></i>Log Tardy Minutes</h2>
        <p id="lateDetails" style="margin-bottom:14px;color:#64748b;font-weight:700;font-size:0.85rem;background:#f8fafc;padding:10px;border-radius:8px;border:1px solid #f1f5f9;"></p>
        <form id="lateMinutesForm" onsubmit="submitLateMinutes(event)">
            <input type="hidden" id="late_emp_id" name="emp_id">
            <input type="hidden" id="late_day"    name="day">
            <input type="hidden" name="ym"        value="{{ $ym }}">
            <input type="hidden" id="late_date"   name="late_date">
            <div class="form-group">
                <label>Minutes Late</label>
                <input type="number" name="late_mins" id="late_mins" min="1" required placeholder="Enter minutes">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('lateMinutesModal')">Cancel</button>
                <button type="submit" class="mbtn mbtn-amber"><i class="fas fa-save"></i> Save Minutes</button>
            </div>
        </form>
    </div>
</div>

{{-- Undertime Minutes Modal --}}
<div id="undertimeMinutesModal" class="custom-overlay" style="z-index:3000;">
    <div class="custom-box" style="max-width:380px;text-align:left;">
        <h2 style="margin-bottom:18px;"><i class="fas fa-hourglass-half" style="color:#3b82f6;margin-right:8px;"></i>Log Undertime Minutes</h2>
        <p id="utDetails" style="margin-bottom:14px;color:#64748b;font-weight:700;font-size:0.85rem;background:#f8fafc;padding:10px;border-radius:8px;border:1px solid #f1f5f9;"></p>
        <form id="undertimeMinutesForm" onsubmit="submitUndertimeMinutes(event)">
            <input type="hidden" id="ut_emp_id" name="emp_id">
            <input type="hidden" id="ut_day"    name="day">
            <input type="hidden" name="ym"        value="{{ $ym }}">
            <input type="hidden" id="ut_date"   name="ut_date">
            <div class="form-group">
                <label>Minutes Undertime</label>
                <input type="number" name="ut_mins" id="ut_mins" min="1" required placeholder="Enter minutes">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('undertimeMinutesModal')">Cancel</button>
                <button type="submit" class="mbtn mbtn-primary"><i class="fas fa-save"></i> Save Minutes</button>
            </div>
        </form>
    </div>
</div>

{{-- Present Confirmation Modal (Individual) --}}
<div id="presentConfirmModal" class="custom-overlay" style="z-index:3000;">
    <div class="custom-box" style="max-width:380px;text-align:left;">
        <h2 style="margin-bottom:18px;"><i class="fas fa-calendar-check" style="color:#10b981;margin-right:8px;"></i>Mark Present</h2>
        <p id="presentDetails" style="margin-bottom:14px;color:#64748b;font-weight:700;font-size:0.85rem;background:#f8fafc;padding:10px;border-radius:8px;border:1px solid #f1f5f9;"></p>
        <p style="font-size:0.8rem; color:#64748b; margin-bottom:20px;">Would you like to mark this employee <strong>Present</strong> for all working days of this month? Existing records will not be overwritten.</p>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <button type="button" class="mbtn mbtn-green" onclick="executeSingleEmpPresent()" style="width:100%; justify-content:center; height:45px;">
                <i class="fas fa-check-double"></i> Yes, Mark Whole Month
            </button>
            <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('presentConfirmModal')" style="width:100%; justify-content:center; height:40px; border:none; background:#f1f5f9; color:#64748b;">
                Just This Day
            </button>
        </div>
    </div>
</div>

{{-- Merge Cells Modal --}}
<div id="mergeModal" class="custom-overlay" style="z-index:3000;">
    <div class="custom-box" style="max-width:420px;text-align:left;">
        <h2 style="margin-bottom:18px;"><i class="fas fa-link" style="color:#3b82f6;margin-right:8px;"></i>Merge Cells</h2>
        <p id="mergeDetails" style="margin-bottom:14px;color:#64748b;font-weight:700;font-size:0.85rem;background:#f8fafc;padding:10px;border-radius:8px;border:1px solid #f1f5f9;"></p>
        <form id="mergeForm" onsubmit="submitMerge(event)">
            
            <div class="form-group">
                <label>Status Symbol to Apply</label>
                <select id="mergeStatus" required onchange="onMergeStatusChange(this.value)">
                    <option value="A">Absent (A)</option>
                    <option value="/">Present (/)</option>
                    <option value="HOL">Holiday (HOL)</option>
                    <option value="SL">Sick Leave (SL)</option>
                    <option value="VL">Vacation Leave (VL)</option>
                    <option value="AL">Absence Leave (AL)</option>
                    <option value="OT">Others (OT)</option>
                </select>
            </div>

            {{-- Custom label — shown only when "Others" is selected --}}
            <div class="form-group" id="mergeCustomLabelGroup"
                style="display:none;animation:fadeIn 0.2s ease;">
                <label>Custom Symbol / Label <span style="color:#ef4444;">*</span></label>
                <input type="text" id="mergeCustomLabel"
                    placeholder="e.g. CL, SP, OB, ML…"
                    maxlength="6"
                    style="text-transform:uppercase;font-weight:800;letter-spacing:1px;"
                    oninput="this.value = this.value.toUpperCase()">
                <small style="color:#94a3b8;font-size:0.72rem;display:block;margin-top:4px;">
                    Short abbreviation (max 6 chars) shown inside the merged cell.
                </small>
            </div>

            <div class="form-group">
                <label>Reason / Note</label>
                <input type="text" id="mergeReason" placeholder="e.g. Official Business, Maternity Leave (optional)">
            </div>
            
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
                <button type="button" class="mbtn mbtn-cancel" onclick="closeMergeModal()">Cancel</button>
                <button type="submit" class="mbtn mbtn-primary"><i class="fas fa-link"></i> Merge Selected</button>
            </div>
        </form>
    </div>
</div>

{{-- Unmerge Cells Confirmation Modal --}}
<div id="unmergeModal" class="custom-overlay" style="z-index:3000;">
    <div class="custom-box" style="max-width:400px;text-align:center;">
        <div style="width:64px;height:64px;border-radius:50%;background:#eff6ff;color:#3b82f6;font-size:1.8rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fas fa-link-slash"></i>
        </div>
        <h2 style="margin-bottom:10px;color:#111827;font-size:1.3rem;">Unmerge Cells?</h2>
        <p id="unmergeDetails" style="color:#64748b;font-size:0.88rem;margin-bottom:8px;line-height:1.6;"></p>
        <p style="color:#94a3b8;font-size:0.78rem;margin-bottom:22px;">
            <i class="fas fa-circle-info" style="color:#93c5fd;"></i>
            Saved attendance values will remain. Edit individual cells to change them.
        </p>
        <div style="display:flex;justify-content:center;gap:12px;">
            <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('unmergeModal')" style="padding:9px 20px;">Cancel</button>
            <button type="button" class="mbtn mbtn-primary" id="confirmUnmergeBtn" onclick="executeUnmerge()" style="padding:9px 20px;">
                <i class="fas fa-link-slash"></i> Yes, Unmerge
            </button>
        </div>
    </div>
</div>

{{-- Clear Month Confirmation Modal --}}
<div id="clearMonthModal" class="custom-overlay">
    <div class="custom-box" style="max-width:400px;text-align:center;">
        <div style="width:60px;height:60px;border-radius:50%;background:#fee2e2;color:#ef4444;font-size:1.8rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h2 style="margin-bottom:12px;color:#111827;font-size:1.4rem;">Clear All Data?</h2>
        <p style="color:#64748b;font-size:0.95rem;margin-bottom:24px;line-height:1.5;">
            Are you sure you want to clear ALL attendance records for <strong>{{ date("F Y", mktime(0,0,0,$month,1,$year)) }}</strong>? 
            <br><span style="color:#ef4444;font-weight:700;">This action cannot be undone.</span>
        </p>
        <div style="display:flex;justify-content:center;gap:12px;">
            <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('clearMonthModal')" style="padding:10px 20px;">Cancel</button>
            <button type="button" class="mbtn mbtn-red" id="confirmClearBtn" onclick="executeClearMonth()" style="padding:10px 20px;">Yes, Clear Data</button>
        </div>
    </div>
</div>

{{-- Mark All Present Confirmation Modal --}}
<div id="markAllPresentModal" class="custom-overlay">
    <div class="custom-box" style="max-width:420px;text-align:center;">

        {{-- Confirm State --}}
        <div id="mapConfirmView">
            <div style="width:60px;height:60px;border-radius:50%;background:#dcfce7;color:#16a34a;font-size:1.8rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-check-double"></i>
            </div>
            <h2 style="margin-bottom:12px;color:#111827;font-size:1.4rem;">Mark All Present</h2>
            <p style="color:#64748b;font-size:0.9rem;margin-bottom:8px;line-height:1.6;">
                This will mark <strong>all employees</strong> as present for every working day (Mon–Fri, excluding holidays) in:
            </p>
            <p style="font-size:1.1rem;font-weight:800;color:#2563eb;margin-bottom:16px;">
                {{ date('F Y', mktime(0,0,0,$month,1,$year)) }}
            </p>
            <p style="color:#94a3b8;font-size:0.8rem;margin-bottom:24px;">
                <i class="fas fa-info-circle"></i> Existing records will <strong>not</strong> be overwritten.
            </p>
            <div style="display:flex;justify-content:center;gap:12px;">
                <button type="button" class="mbtn mbtn-cancel" onclick="closeModal('markAllPresentModal')" style="padding:10px 20px;">Cancel</button>
                <button type="button" class="mbtn" id="confirmMarkAllPresentBtn" onclick="executeMarkAllPresent()" style="padding:10px 20px;background:linear-gradient(135deg,#22c55e,#16a34a);color:white;box-shadow:0 3px 10px rgba(34,197,94,0.3);">
                    <i class="fas fa-check-double"></i> Yes, Mark All Present
                </button>
            </div>
        </div>

        {{-- Loading State (hidden initially) --}}
        <div id="mapLoadingView" style="display:none;">
            <div style="width:70px;height:70px;border-radius:50%;background:#eff6ff;color:#3b82f6;font-size:2rem;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <h2 style="margin-bottom:8px;color:#111827;font-size:1.3rem;">Processing…</h2>
            <p id="mapProgressText" style="color:#64748b;font-size:0.9rem;margin-bottom:20px;">Starting…</p>
            <div style="background:#e2e8f0;border-radius:50px;height:8px;overflow:hidden;margin-bottom:8px;">
                <div id="mapProgressBar" style="height:100%;width:0%;background:linear-gradient(90deg,#22c55e,#16a34a);border-radius:50px;transition:width 0.3s ease;"></div>
            </div>
            <p id="mapProgressCount" style="font-size:0.75rem;color:#94a3b8;font-weight:600;">0 / 0 employees</p>
        </div>

        {{-- Done State (hidden initially) --}}
        <div id="mapDoneView" style="display:none;">
            <div style="width:70px;height:70px;border-radius:50%;background:#dcfce7;color:#16a34a;font-size:2rem;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="fas fa-circle-check"></i>
            </div>
            <h2 style="margin-bottom:8px;color:#15803d;font-size:1.3rem;">All Done!</h2>
            <p style="color:#64748b;font-size:0.9rem;margin-bottom:4px;">All employees have been marked present.</p>
            <p style="color:#94a3b8;font-size:0.8rem;">Reloading page…</p>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script id="empIdsData" type="application/json">
    {!! json_encode(collect($employees)->pluck('id')->values()) !!}
</script>

<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    /* ── Actions Dropdown ────────────────────────────────────────── */
    function toggleDropdown() {
        const dropdown = document.getElementById('actionDropdown');
        if (dropdown) {
            if (dropdown.style.display === 'none') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }
    }

    window.addEventListener('click', function (e) {
        if (!e.target.closest('.options-wrap') && !e.target.closest('#calSearchBox')) {
            const dropdown = document.getElementById('actionDropdown');
            if (dropdown) dropdown.style.display = 'none';
            const suggestions = document.getElementById('searchSuggestions');
            if (suggestions) suggestions.style.display = 'none';
        }
    });

    const searchInput = document.getElementById('searchInput');
    const calStationFilter = document.getElementById('calStationFilter');
    const suggestionsBox = document.getElementById('searchSuggestions');
    const employeeRows = document.querySelectorAll('#attendanceTbody .emp-row');
    const stationRows = document.querySelectorAll('#attendanceTbody .station-row');
    
    // For individual view employee cards
    const empCards = document.querySelectorAll('.indiv-selector-wrap .emp-card');
    const stationSections = document.querySelectorAll('.indiv-selector-wrap .indiv-station-section');

    function filterCalTable() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const station = calStationFilter ? calStationFilter.value.toLowerCase() : '';

        employeeRows.forEach(row => {
            const nameMatch = !query || (row.dataset.name || '').includes(query);
            const stationMatch = !station || (row.dataset.station || '').includes(station);
            row.style.display = (nameMatch && stationMatch) ? '' : 'none';
        });

        stationRows.forEach(sRow => {
            if (!station) { sRow.style.display = ''; return; }
            const chip = sRow.querySelector('.station-chip');
            const sName = chip ? chip.textContent.trim().toLowerCase() : '';
            sRow.style.display = sName.includes(station) ? '' : 'none';
        });

        // Filter cards (Individual Selector View)
        empCards.forEach(card => {
            const nameMatch = !query || (card.dataset.name || '').includes(query);
            const stationMatch = !station || (card.dataset.station || '').includes(station);
            card.style.display = (nameMatch && stationMatch) ? '' : 'none';
        });

        // Hide entirely empty station blocks visually in indiv selector
        if (empCards.length > 0) {
            stationSections.forEach(section => {
                const sectStationMatch = !station || (section.dataset.station || '').includes(station);
                if(!sectStationMatch) {
                    section.style.display = 'none';
                } else {
                    const visibleCards = section.querySelectorAll('.emp-card[style=""]');
                    section.style.display = visibleCards.length > 0 ? '' : 'none';
                }
            });
        }

        if (query.length >= 2) {
            const matches = [];
            employeeRows.forEach(row => {
                if (row.style.display === 'none') return;
                const nameEl = row.querySelector('.td-name');
                if (nameEl) {
                    const name = nameEl.textContent.trim();
                    if (name.toLowerCase().includes(query)) matches.push(name);
                }
            });
            const unique = [...new Set(matches)].slice(0, 6);
            if (unique.length) {
                if (suggestionsBox) {
                    suggestionsBox.innerHTML = unique.map(n => {
                        const cleanName = n.replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                        return `<div class="cal-suggestion-item" onclick="selectSuggestion('${cleanName}')">${n}</div>`;
                    }).join('');
                    suggestionsBox.style.display = 'block';
                }
            } else {
                if (suggestionsBox) suggestionsBox.style.display = 'none';
            }
        } else {
            if (suggestionsBox) suggestionsBox.style.display = 'none';
        }
    }

    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = searchInput.value.toLowerCase().trim();
            if (query.length === 0) {
                if(suggestionsBox) suggestionsBox.style.display = 'none';
            }
            searchTimeout = setTimeout(() => {
                filterCalTable();
            }, 150);
        });
    }


    function selectSuggestion(name) {
        if (searchInput) searchInput.value = name;
        if (suggestionsBox) suggestionsBox.style.display = 'none';
        filterCalTable();
    }

    function filterTable() { filterCalTable(); }

    /* ── Modals ────────────────────────────────────────────────── */
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function openHolidayModal(day = null, reason = '') {
        document.getElementById('actionDropdown').style.display = 'none';
        document.getElementById('holidayForm').reset();
        const removeBtn = document.getElementById('holidayRemoveBtn');
        if (day) {
            const year = '{{ $year }}';
            const month = String('{{ $month }}').padStart(2, '0');
            const dayStr = String(day).padStart(2, '0');
            document.querySelector('#holidayForm [name="holiday_date"]').value = `${year}-${month}-${dayStr}`;
            if(reason) {
                removeBtn.style.display = 'inline-block';
            } else {
                removeBtn.style.display = 'none';
            }
        } else {
            removeBtn.style.display = 'none';
        }
        if (reason) {
            document.querySelector('#holidayForm [name="holiday_reason"]').value = reason;
        }
        document.getElementById('holidayModal').style.display = 'flex';
    }

    async function removeAllHolidays() {
        document.getElementById('actionDropdown').style.display = 'none';
        if (!confirm('Are you sure you want to remove ALL holidays for this month? Cells will return to default.')) return;
        
        const fd = new FormData();
        fd.append('ym', '{{ $ym }}');
        
        try {
            const res = await fetch('{{ route("admin.calendar.removeHoliday") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: fd
            });
            const result = await res.json();
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.message || 'Failed to remove holidays');
            }
        } catch (e) {
            alert('Request failed.');
        }
    }

    async function removeHolidayFromModal() {
        if (!confirm('Are you sure you want to remove this holiday?')) return;
        const dateInput = document.querySelector('#holidayForm [name="holiday_date"]').value;
        if (!dateInput) return;
        
        const fd = new FormData();
        fd.append('holiday_date', dateInput);
        
        try {
            const res = await fetch('{{ route("admin.calendar.removeHoliday") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: fd
            });
            const result = await res.json();
            if (result.success) {
                closeModal('holidayModal');
                window.location.reload();
            } else {
                alert(result.message || 'Failed to remove holiday');
            }
        } catch (e) {
            alert('Request failed.');
        }
    }

    function openPrintModal() {
        document.getElementById('actionDropdown').style.display = 'none';
        
        // Sync the print modal station selection with the current page filter
        const calFilter = document.getElementById('calStationFilter');
        const printSelect = document.getElementById('printStationSelect');
        if (calFilter && printSelect) {
            printSelect.value = calFilter.value || 'all';
        }

        document.getElementById('printModal').style.display = 'flex';
        // Reset extra fields visibility
        ['prep', 'cert', 'ver'].forEach(type => {
            document.getElementById(`print-${type}-pos2`).style.display = 'none';
            document.getElementById(`print-${type}-pos3`).style.display = 'none';
        });
    }

    function openExportModal() {
        document.getElementById('actionDropdown').style.display = 'none';
        document.getElementById('exportStationSelect').value = 'All';
        document.getElementById('exportModal').style.display = 'flex';
        // Reset extra fields visibility
        ['prep', 'cert', 'ver'].forEach(type => {
            document.getElementById(`export-${type}-pos2`).style.display = 'none';
            document.getElementById(`export-${type}-pos3`).style.display = 'none';
        });
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
        const inputId = sigType.charAt(0).toUpperCase() + sigType.slice(1); // Prep -> Prep
        const fullInputId = `${modalType}Sig${inputId}Pos${lineNum}`;
        
        // Reset and hide
        const input = document.getElementById(fullInputId);
        if (input) input.value = '';
        field.style.display = 'none';
        
        // If we removed L2 but L3 is still showing, we should probably keep L3 but maybe it's better to just hide the targeted one.
        // For simplicity, we just hide the targeted one.
    }

    async function executeExport() {
        const station = document.getElementById('exportStationSelect').value;
        const prepName = document.getElementById('exportSigPrepName').value;
        const prepPos = document.getElementById('exportSigPrepPos').value;
        const certName = document.getElementById('exportSigCertName').value;
        const certPos = document.getElementById('exportSigCertPos').value;
        const verName = document.getElementById('exportSigVerName').value;
        const verPos = document.getElementById('exportSigVerPos').value;

        const url = '{{ route("admin.export.attendance") }}'
            + '?year={{ $year }}'
            + '&month={{ $month }}'
            + '&station=' + encodeURIComponent(station)
            + '&prep_name=' + encodeURIComponent(prepName)
            + '&prep_pos=' + encodeURIComponent(prepPos)
            + '&prep_pos2=' + encodeURIComponent(document.getElementById('exportSigPrepPos2').value)
            + '&prep_pos3=' + encodeURIComponent(document.getElementById('exportSigPrepPos3').value)
            + '&cert_name=' + encodeURIComponent(certName)
            + '&cert_pos=' + encodeURIComponent(certPos)
            + '&cert_pos2=' + encodeURIComponent(document.getElementById('exportSigCertPos2').value)
            + '&cert_pos3=' + encodeURIComponent(document.getElementById('exportSigCertPos3').value)
            + '&ver_name=' + encodeURIComponent(verName)
            + '&ver_pos=' + encodeURIComponent(verPos)
            + '&ver_pos2=' + encodeURIComponent(document.getElementById('exportSigVerPos2').value)
            + '&ver_pos3=' + encodeURIComponent(document.getElementById('exportSigVerPos3').value);
            
        closeModal('exportModal');
        
        // Show progress modal
        const dlModal = document.getElementById('downloadProgressModal');
        dlModal.style.display = 'flex';
        
        document.getElementById('dlSpinner').style.display = 'block';
        document.getElementById('dlSuccessIcon').style.display = 'none';
        document.getElementById('dlProgressTitle').innerText = 'Exporting Excel...';
        document.getElementById('dlProgressText').innerText = 'Generating your report, please wait';

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Download request failed');

            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            
            // Try to extract original filename
            let filename = 'Attendance_Report_' + station + '_' + '{{ $year }}_{{ $month }}.xlsx';
            const disposition = response.headers.get('Content-Disposition');
            if (disposition && disposition.indexOf('filename=') !== -1) {
                const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = downloadUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(downloadUrl);
            document.body.removeChild(a);

            // Show success animation
            document.getElementById('dlSpinner').style.display = 'none';
            document.getElementById('dlSuccessIcon').style.display = 'block';
            document.getElementById('dlProgressTitle').innerText = 'Export Complete!';
            document.getElementById('dlProgressTitle').style.color = '#10b981';
            document.getElementById('dlProgressText').innerText = 'Your file has been downloaded successfully.';

            setTimeout(() => {
                closeModal('downloadProgressModal');
                document.getElementById('dlProgressTitle').style.color = '#1e293b';
            }, 2500);

        } catch (err) {
            console.error(err);
            alert('An error occurred during export. Please try again.');
            closeModal('downloadProgressModal');
        }
    }

    function executePrint() {
        closeModal('printModal');

        const dlModal = document.getElementById('downloadProgressModal');
        dlModal.style.display = 'flex';
        
        document.getElementById('dlSpinner').style.display = 'block';
        document.getElementById('dlSuccessIcon').style.display = 'none';
        document.getElementById('dlProgressTitle').innerText = 'Preparing Print...';
        document.getElementById('dlProgressTitle').style.color = '#1e293b';
        document.getElementById('dlProgressText').innerText = 'Formatting records for printing';

        const selectedStation = document.getElementById('printStationSelect').value;
        const calFilter = document.getElementById('calStationFilter');
        
        const stationDisplay = document.getElementById('printStationNameDisplay');
        if (stationDisplay) {
            if (selectedStation === 'all') {
                const allStationsText = Array.from(document.getElementById('printStationSelect').options)
                    .filter(opt => opt.value !== 'all')
                    .map(opt => opt.innerText)
                    .join(',');
                stationDisplay.innerText = allStationsText;
            } else {
                const selOpt = document.getElementById('printStationSelect').options[document.getElementById('printStationSelect').selectedIndex];
                stationDisplay.innerText = selOpt.innerText;
            }
        }

        let originalFilter = '';
        if (calFilter) {
            originalFilter = calFilter.value;
            calFilter.value = selectedStation === 'all' ? '' : selectedStation;
            filterCalTable();
        }

        // Update signatories on page
        const pPrepName = document.getElementById('printSigPrepName').value;
        const pPrepPos = document.getElementById('printSigPrepPos').value;
        const pPrepPos2 = document.getElementById('printSigPrepPos2').value;
        const pPrepPos3 = document.getElementById('printSigPrepPos3').value;
        const pCertName = document.getElementById('printSigCertName').value;
        const pCertPos = document.getElementById('printSigCertPos').value;
        const pCertPos2 = document.getElementById('printSigCertPos2').value;
        const pCertPos3 = document.getElementById('printSigCertPos3').value;
        const pVerName = document.getElementById('printSigVerName').value;
        const pVerPos = document.getElementById('printSigVerPos').value;
        const pVerPos2 = document.getElementById('printSigVerPos2').value;
        const pVerPos3 = document.getElementById('printSigVerPos3').value;

        if (pPrepName) document.getElementById('sig-prepared-name').innerText = pPrepName;
        if (pPrepPos) document.getElementById('sig-prepared-pos').innerText = pPrepPos;
        if (pPrepPos2) document.getElementById('sig-prepared-pos2').innerText = pPrepPos2;
        if (pPrepPos3) document.getElementById('sig-prepared-pos3').innerText = pPrepPos3;
        if (pCertName) document.getElementById('sig-certified-name').innerText = pCertName;
        if (pCertPos) document.getElementById('sig-certified-pos').innerText = pCertPos;
        if (pCertPos2) document.getElementById('sig-certified-pos2').innerText = pCertPos2;
        if (pCertPos3) document.getElementById('sig-certified-pos3').innerText = pCertPos3;
        if (pVerName) document.getElementById('sig-verified-name').innerText = pVerName;
        if (pVerPos) document.getElementById('sig-verified-pos').innerText = pVerPos;
        if (pVerPos2) document.getElementById('sig-verified-pos2').innerText = pVerPos2;
        if (pVerPos3) document.getElementById('sig-verified-pos3').innerText = pVerPos3;

        // Slight delay to ensure DOM layout adjusts and user sees loader
        setTimeout(() => {
            dlModal.style.display = 'none';
            
            window.print();
            
            // Revert filter after print completes (or user cancels)
            if (calFilter) {
                calFilter.value = originalFilter;
                filterCalTable();
            }

            // Show brief success feedback
            dlModal.style.display = 'flex';
            document.getElementById('dlSpinner').style.display = 'none';
            document.getElementById('dlSuccessIcon').style.display = 'block';
            document.getElementById('dlProgressTitle').innerText = 'Print Complete!';
            document.getElementById('dlProgressTitle').style.color = '#10b981';
            document.getElementById('dlProgressText').innerText = 'Your records have been processed.';

            setTimeout(() => {
                closeModal('downloadProgressModal');
                document.getElementById('dlProgressTitle').style.color = '#1e293b';
            }, 2000);

        }, 800);
    }

    function confirmClear() {
        document.getElementById('actionDropdown').classList.remove('show');
        document.getElementById('clearMonthModal').style.display = 'flex';
    }

    function executeClearMonth() {
        const btn = document.getElementById('confirmClearBtn');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
        btn.disabled = true;

        const fd = new FormData();
        fd.append('ym', '{{ $ym }}');
        
        fetch('{{ route("admin.calendar.clearMonth") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                window.location.reload();
            } else {
                alert('Error: ' + res.message);
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        })
        .catch(e => {
            alert('Failed: ' + e);
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        });
    }

    async function performFetch(url, fd, modalId) {
        try {
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: fd });
            const json = await res.json();
            if (json.success) { if (modalId) closeModal(modalId); window.location.reload(); }
            else alert('Error: ' + json.message);
        } catch (e) { alert('Request failed: ' + e); }
    }

    function submitForm(event, url, modalId) {
        event.preventDefault();
        const form = event.target;
        const btn  = form.querySelector('button[type="submit"]');
        const old  = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled  = true;
        performFetch(url, new FormData(form), modalId).finally(() => {
            btn.innerHTML = old; btn.disabled = false;
        });
    }

    /* ── Batch Save ────────────────────────────────────────────── */
    function submitBatch() {
        const inputs = document.querySelectorAll('input[name^="attendance["]');
        const fd = new FormData();
        fd.append('ym', '{{ $ym }}');
        inputs.forEach(inp => {
            const val = inp.value.trim().toUpperCase();
            if (val) fd.append(inp.name, val);
        });
        fetch('{{ route("admin.calendar.saveBatch") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd
        }).then(r => r.text()).then(() => window.location.reload())
          .catch(e => alert('Batch save failed: ' + e));
    }

    /* ── Cell Change → Absence Reason Modal ───────────────────── */
    function handleCellChange(input, empId, day) {
        const val = input.value.trim().toUpperCase();
        input.value = val;
        applyInputColor(input);

        // Auto-save logic
        const fd = new FormData();
        fd.append('emp_id', empId);
        fd.append('day', day);
        fd.append('ym', '{{ $ym }}');
        fd.append('val', val);

        fetch('{{ route("admin.calendar.autosave") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd
        }).catch(e => console.error('Auto-save failed', e));

        const dateStr = `{{ $year }}-${String({{ $month }}).padStart(2,'0')}-${String(day).padStart(2,'0')}`;

        if (val === 'A' || val === '1' || val === '½' || val === '1/2') {
            document.getElementById('abs_emp_id').value = empId;
            document.getElementById('abs_day').value    = day;
            document.getElementById('absenceDetails').innerText = `Day ${day} — Employee #${empId}`;
            document.getElementById('abs_custom_reason').value = '';
            document.getElementById('absenceReasonModal').style.display = 'flex';
            setTimeout(() => document.getElementById('abs_custom_reason').focus(), 50);
        } else if (val === 'L') {
            document.getElementById('late_emp_id').value = empId;
            document.getElementById('late_day').value    = day;
            document.getElementById('late_date').value   = dateStr;
            document.getElementById('lateDetails').innerText = `Day ${day} — Employee #${empId}`;
            document.getElementById('late_mins').value = '';
            document.getElementById('lateMinutesModal').style.display = 'flex';
            setTimeout(() => document.getElementById('late_mins').focus(), 50);
        } else if (val === 'U') {
            document.getElementById('ut_emp_id').value = empId;
            document.getElementById('ut_day').value    = day;
            document.getElementById('ut_date').value   = dateStr;
            document.getElementById('utDetails').innerText = `Day ${day} — Employee #${empId}`;
            document.getElementById('ut_mins').value = '';
            document.getElementById('undertimeMinutesModal').style.display = 'flex';
            setTimeout(() => document.getElementById('ut_mins').focus(), 50);
        } else if (val === '/') {
            // Store target for bulk action if user chooses "Whole Month"
            window.pendingPresentEmpId = empId;
            document.getElementById('presentDetails').innerText = `Employee #${empId} — ${dateStr}`;
            document.getElementById('presentConfirmModal').style.display = 'flex';
        }
    }

    /* ── Single Employee Bulk Actions from Grid ── */
    async function executeSingleEmpPresent() {
        const empId = window.pendingPresentEmpId;
        if(!empId) return;
        
        closeModal('presentConfirmModal');
        const fd = new FormData();
        fd.append('emp_id', empId);
        fd.append('year', '{{ $year }}');
        fd.append('month', '{{ $month }}');

        try {
            const res = await fetch('{{ route("admin.employee.presentAll") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: fd
            });
            const result = await res.json();
            if (result.success) {
                showToast('Employee marked present for the month!');
                setTimeout(() => window.location.reload(), 1000);
            } else throw new Error(result.message);
        } catch (e) { alert('Error: ' + e.message); }
    }

    /* ── AJAX Submit Helpers for Individual Cell Actions ── */
    async function submitLateMinutes(event) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('button[type="submit"]');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        try {
            const res = await fetch('{{ route("admin.employee.late") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: new FormData(form)
            });
            const result = await res.json();
            if (result.success) {
                closeModal('lateMinutesModal');
                showToast('Late minutes recorded!');
            } else throw new Error(result.message);
        } catch (e) { alert('Error: ' + e.message); btn.innerHTML = oldHtml; btn.disabled = false; }
    }

    async function submitUndertimeMinutes(event) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('button[type="submit"]');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        try {
            const res = await fetch('{{ route("admin.employee.undertime") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: new FormData(form)
            });
            const result = await res.json();
            if (result.success) {
                closeModal('undertimeMinutesModal');
                showToast('Undertime minutes recorded!');
            } else throw new Error(result.message);
        } catch (e) { alert('Error: ' + e.message); btn.innerHTML = oldHtml; btn.disabled = false; }
    }

    function showToast(msg) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,#22c55e,#16a34a);color:white;padding:12px 20px;border-radius:14px;font-weight:700;font-size:0.85rem;z-index:9999;box-shadow:0 6px 20px rgba(34,197,94,0.4);display:flex;align-items:center;gap:8px;';
        toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }

    function submitAbsenceReason(event) {
        event.preventDefault();
        const form = event.target;
        const btn = form.querySelector('button[type="submit"]');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        const fd = new FormData(form);
        fetch('{{ route("admin.calendar.absenceReason") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd
        })
        .then(res => {
            if (!res.ok) throw new Error('Server error ' + res.status);
            closeModal('absenceReasonModal');
            // Show a brief success toast
            const toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,#22c55e,#16a34a);color:white;padding:12px 20px;border-radius:14px;font-weight:700;font-size:0.85rem;z-index:9999;box-shadow:0 6px 20px rgba(34,197,94,0.4);display:flex;align-items:center;gap:8px;';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> Absence reason saved!';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2500);
        })
        .catch(e => {
            alert('Failed to save reason: ' + e);
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        });
    }

    /* ── Range Selection & Cell Merging ────────────────────────── */
    // Map from status symbol → autosave symbol expected by backend
    const STATUS_TO_AUTOSAVE = { 'A': 'A', '/': '/', 'HOL': '1', 'SL': 'A', 'VL': 'A', 'AL': 'A', 'OT': 'A' };
    // Display label for merged cell
    const STATUS_DISPLAY = { 'A': 'A', '/': '/', 'HOL': 'HOL', 'SL': 'SL', 'VL': 'VL', 'AL': 'AL', 'OT': 'OT' };
    // Background color for merged cell
    const STATUS_BG    = { '/': '#dcfce7', 'HOL': '#ffe4e6', 'A': '#fee2e2', 'SL': '#fef3c7', 'VL': '#e0f2fe', 'AL': '#f3e8ff', 'OT': '#f1f5f9' };
    const STATUS_COLOR = { '/': '#15803d', 'HOL': '#e11d48', 'A': '#dc2626', 'SL': '#b45309', 'VL': '#0369a1', 'AL': '#7c3aed', 'OT': '#475569' };

    let lastClickedInput = null;
    let selectedInputs = [];
    const mergeHistory = new Map(); // key = firstTd, value = array of { td, outerHTML, insertBefore }

    const tbody = document.getElementById('attendanceTbody');
    if (tbody) {
        tbody.addEventListener('click', function(e) {
            const inp = e.target.closest('.cell-input');
            if (!inp || inp.type === 'hidden') return;

            if (e.shiftKey && lastClickedInput && lastClickedInput.closest('tr') === inp.closest('tr')) {
                e.preventDefault();
                selectRange(lastClickedInput, inp);
                showMergeTooltip(inp);
            } else {
                clearSelection();
                lastClickedInput = inp;
            }
        });
    }

    function selectRange(startInp, endInp) {
        clearSelection();
        const row = startInp.closest('tr');
        // Only real visible text inputs — exclude hidden merge inputs
        const inputs = Array.from(row.querySelectorAll('input.cell-input[type="text"]'));
        const startIndex = inputs.indexOf(startInp);
        const endIndex   = inputs.indexOf(endInp);

        if (startIndex === -1 || endIndex === -1) return;

        const min = Math.min(startIndex, endIndex);
        const max = Math.max(startIndex, endIndex);

        selectedInputs = [];
        for (let i = min; i <= max; i++) {
            inputs[i].classList.add('cell-selected');
            selectedInputs.push(inputs[i]);
        }
    }

    function clearSelection() {
        document.querySelectorAll('.cell-selected').forEach(el => el.classList.remove('cell-selected'));
        selectedInputs = [];
        const tooltip = document.querySelector('.merge-tooltip');
        if (tooltip) tooltip.remove();
    }

    function closeMergeModal() {
        closeModal('mergeModal');
        clearSelection();
        pendingMergeInputs = [];
        lastClickedInput = null;
    }

    function showMergeTooltip(inp) {
        const existing = document.querySelector('.merge-tooltip');
        if (existing) existing.remove();

        if (selectedInputs.length < 2) return;

        const rect = inp.getBoundingClientRect();
        const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollY = window.pageYOffset || document.documentElement.scrollTop;

        const tooltip = document.createElement('div');
        tooltip.className = 'merge-tooltip';
        tooltip.innerHTML = `<i class="fas fa-link"></i> Merge ${selectedInputs.length} Cells`;
        tooltip.style.position = 'fixed';
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top  = (rect.top - 48) + 'px';
        tooltip.style.zIndex = '9999';

        tooltip.onclick = (e) => {
            e.stopPropagation();
            openMergeModal();
        };

        document.body.appendChild(tooltip);

        // Dismiss on outside click (slight delay to avoid immediate close)
        setTimeout(() => {
            const handleOutside = (e) => {
                if (!tooltip.contains(e.target) && !e.target.closest('.cell-input')) {
                    clearSelection();
                    document.removeEventListener('click', handleOutside);
                }
            };
            document.addEventListener('click', handleOutside);
        }, 120);
    }

    // ── Stable snapshot of the selection that will be merged ──────
    // We capture this when the modal opens so that clearSelection()
    // (triggered by outside clicks) doesn't lose the data before submit.
    let pendingMergeInputs = [];

    function openMergeModal() {
        if (selectedInputs.length < 2) {
            alert('Select at least 2 cells: click the first cell, then Shift+Click the last cell in the same row.');
            return;
        }
        // Take stable snapshot NOW
        pendingMergeInputs = [...selectedInputs];

        const count = pendingMergeInputs.length;
        const nameEl = pendingMergeInputs[0].closest('tr').querySelector('.td-name');
        const empName = nameEl ? nameEl.textContent.trim() : 'employee';

        document.getElementById('mergeDetails').innerHTML =
            `<i class="fas fa-info-circle" style="color:#3b82f6;"></i> Merging <strong>${count} day${count > 1 ? 's' : ''}</strong> for <strong>${empName}</strong>.`;
        document.getElementById('mergeReason').value = '';
        document.getElementById('mergeStatus').value = 'A';
        document.getElementById('mergeCustomLabel').value = '';
        document.getElementById('mergeCustomLabelGroup').style.display = 'none';
        document.getElementById('mergeModal').style.display = 'flex';
        setTimeout(() => document.getElementById('mergeReason').focus(), 50);

        // Remove tooltip and highlight (snapshot already saved above)
        const tooltip = document.querySelector('.merge-tooltip');
        if (tooltip) tooltip.remove();
        // Keep visual highlight on cells until merge is confirmed
    }

    function onMergeStatusChange(val) {
        const group = document.getElementById('mergeCustomLabelGroup');
        const input = document.getElementById('mergeCustomLabel');
        if (val === 'OT') {
            group.style.display = 'block';
            setTimeout(() => input.focus(), 50);
        } else {
            group.style.display = 'none';
            input.value = '';
        }
    }

    async function submitMerge(e) {
        e.preventDefault();

        // Use the stable snapshot — NOT selectedInputs (which may have been cleared)
        const mergeInputs = pendingMergeInputs;
        if (mergeInputs.length < 2) {
            alert('No cells selected. Please close this modal and select cells first.');
            closeModal('mergeModal');
            return;
        }

        // Resolve effective status symbol (custom label for OT)
        let statusSym = document.getElementById('mergeStatus').value;
        if (statusSym === 'OT') {
            const customLabel = document.getElementById('mergeCustomLabel').value.trim().toUpperCase();
            if (!customLabel) {
                alert('Please enter a Custom Symbol / Label for "Others".');
                document.getElementById('mergeCustomLabel').focus();
                return;
            }
            statusSym = customLabel; // use custom text as the display symbol
        }
        const autosaveSym = STATUS_TO_AUTOSAVE[statusSym] || 'A';
        const reason      = document.getElementById('mergeReason').value.trim();

        const btn = e.target.querySelector('button[type="submit"]');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
        btn.disabled = true;

        // ── 1. Save every cell to DB ───────────────────────────────
        const promises = [];
        for (const inp of mergeInputs) {
            const m = inp.name ? inp.name.match(/\[(\d+)\]\[(\d+)\]/) : null;
            if (!m) continue;
            const empId = m[1], day = m[2];

            const fd1 = new FormData();
            fd1.append('emp_id', empId); fd1.append('day', day);
            fd1.append('ym', '{{ $ym }}'); fd1.append('val', autosaveSym);
            promises.push(fetch('{{ route("admin.calendar.autosave") }}',
                { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: fd1 }));


        }
        await Promise.all(promises).catch(err => console.error('Merge save error:', err));

        // ── 2. Get ALL TDs between first & last selected (incl. SAT/SUN/HOL) ──
        const firstInputTd = mergeInputs[0].closest('td');
        const lastInputTd  = mergeInputs[mergeInputs.length - 1].closest('td');
        const rowTds       = Array.from(firstInputTd.closest('tr').querySelectorAll('td'));
        const firstIdx     = rowTds.indexOf(firstInputTd);
        const lastIdx      = rowTds.indexOf(lastInputTd);
        const tdsToMerge   = rowTds.slice(firstIdx, lastIdx + 1);
        const firstTd      = tdsToMerge[0];
        const lastTd       = tdsToMerge[tdsToMerge.length - 1];

        // ── 3. Capture original HTML BEFORE any DOM mutation ────────
        const savedOuterHTMLs = tdsToMerge.map(td => td.outerHTML);
        mergeHistory.set(firstTd, savedOuterHTMLs);

        // ── 4. Build hidden inputs for batch-save compatibility ─────
        const hiddenInputsHTML = mergeInputs.map(inp =>
            `<input type="hidden" name="${inp.name}" value="${autosaveSym}" class="cell-input">`
        ).join('');

        // ── 5. Style constants ───────────────────────────────────────
        const bg    = STATUS_BG[statusSym]    || '#fee2e2';
        const color = STATUS_COLOR[statusSym] || '#dc2626';
        const label = STATUS_DISPLAY[statusSym] || statusSym;

        // ── 6. Visually merge: set colspan, replace content, remove siblings ──
        firstTd.setAttribute('colspan', tdsToMerge.length);
        firstTd.className    = 'td-day';
        firstTd.style.padding = '0';
        firstTd.innerHTML = `
            <div class="merged-span"
                style="background:${bg};color:${color};box-shadow:inset 0 0 0 1px ${color}40;"
                title="Double-click to unmerge"
                ondblclick="unmergeCell(this)">
                <span style="font-size:1.0rem;font-weight:800;">${label}</span>
                ${reason ? `<span style="font-size:0.58rem;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;margin-top:2px;max-width:90%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${reason}</span>` : ''}
                ${hiddenInputsHTML}
            </div>`;

        for (let i = 1; i < tdsToMerge.length; i++) {
            tdsToMerge[i].remove();
        }

        // ── 7. Persist merge to DB (await so toast shows after save) ─
        const firstInp = mergeInputs[0];
        const mFirst   = firstInp.name ? firstInp.name.match(/\[(\d+)\]\[(\d+)\]/) : null;
        // Use data-day on the last TD for accurate end_day (covers SAT/SUN endings)
        const endDayNum = lastTd.dataset.day
            || lastTd.querySelector('input.cell-input')?.name?.match(/\[(\d+)\]\[(\d+)\]/)?.[2];

        if (mFirst && endDayNum) {
            const fdMerge = new FormData();
            fdMerge.append('emp_id',    mFirst[1]);
            fdMerge.append('ym',        '{{ $ym }}');
            fdMerge.append('start_day', mFirst[2]);
            fdMerge.append('end_day',   endDayNum);
            fdMerge.append('label',     statusSym);
            fdMerge.append('reason',    reason);
            await fetch('{{ route("admin.calendar.saveMerge") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: fdMerge
            }).catch(err => console.error('Save merge error:', err));

            firstTd.dataset.mergeEmp   = mFirst[1];
            firstTd.dataset.mergeStart = mFirst[2];
        }

        // ── 7. Clean up ─────────────────────────────────────────────
        btn.innerHTML = oldHtml;
        btn.disabled  = false;
        closeModal('mergeModal');
        clearSelection();
        pendingMergeInputs = [];
        lastClickedInput   = null;

        // Success toast
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;padding:12px 20px;border-radius:14px;font-weight:700;font-size:0.85rem;z-index:9999;box-shadow:0 6px 20px rgba(59,130,246,0.4);display:flex;align-items:center;gap:8px;animation:fadeIn 0.2s ease;';
        toast.innerHTML = '<i class="fas fa-link"></i> Cells merged!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }

    // Helper: delete a merge record from the DB
    async function deleteMergeFromServer(empId, startDay) {
        const fd = new FormData();
        fd.append('emp_id',    empId);
        fd.append('ym',        '{{ $ym }}');
        fd.append('start_day', startDay);
        return fetch('{{ route("admin.calendar.deleteMerge") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd
        }).catch(err => console.error('Delete merge error:', err));
    }

    // Reference for the merged div pending unmerge confirmation
    let pendingUnmergeDiv = null;

    // Called when double-clicking a server-rendered merged cell (no JS mergeHistory entry)
    function unmergeServerCell(mergedDiv, empId, startDay) {
        const firstTd = mergedDiv.closest('td');
        const labelEl = mergedDiv.querySelector('span:first-child');
        const currentLabel = labelEl ? labelEl.textContent.trim() : '?';
        const nameEl  = firstTd.closest('tr') ? firstTd.closest('tr').querySelector('.td-name') : null;
        const empName = nameEl ? nameEl.textContent.trim() : 'this employee';
        const count   = parseInt(firstTd.getAttribute('colspan') || '1', 10);
        const currentColor = mergedDiv.style.color || '#dc2626';

        document.getElementById('unmergeDetails').innerHTML =
            `This will restore <strong>${count} individual cell${count > 1 ? 's' : ''}</strong> for <strong>${empName}</strong>` +
            ` (currently merged as <span style="color:${currentColor};font-weight:800;">${currentLabel}</span>).`;

        // Use a special flow: no savedOuterHTMLs, just delete from DB and reload
        pendingUnmergeDiv = mergedDiv;
        pendingUnmergeDiv._serverEmpId    = empId;
        pendingUnmergeDiv._serverStartDay = startDay;
        pendingUnmergeDiv._isServerMerge  = true;
        document.getElementById('unmergeModal').style.display = 'flex';
    }

    function unmergeCell(mergedDiv) {
        const firstTd = mergedDiv.closest('td');
        const savedOuterHTMLs = mergeHistory.get(firstTd);
        if (!savedOuterHTMLs || !savedOuterHTMLs.length) {
            alert('Cannot unmerge: original cell data is missing.');
            return;
        }

        // Figure out how many cells & the employee name
        const nameEl = firstTd.closest('tr') ? firstTd.closest('tr').querySelector('.td-name') : null;
        const empName = nameEl ? nameEl.textContent.trim() : 'this employee';
        const count = savedOuterHTMLs.length;
        const labelEl = mergedDiv.querySelector('span:first-child');
        const currentLabel = labelEl ? labelEl.textContent.trim() : '?';
        const currentColor = mergedDiv.style.color || '#dc2626';

        document.getElementById('unmergeDetails').innerHTML =
            `This will restore <strong>${count} individual cell${count > 1 ? 's' : ''}</strong> for <strong>${empName}</strong>` +
            ` (currently merged as <span style="color:${currentColor};font-weight:800;">${currentLabel}</span>).`;

        pendingUnmergeDiv = mergedDiv;
        document.getElementById('unmergeModal').style.display = 'flex';
    }

    function executeUnmerge() {
        if (!pendingUnmergeDiv) { closeModal('unmergeModal'); return; }

        const btn = document.getElementById('confirmUnmergeBtn');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring…';
        btn.disabled = true;

        // ── Server-rendered merge: just delete from DB and reload ──
        if (pendingUnmergeDiv._isServerMerge) {
            deleteMergeFromServer(
                pendingUnmergeDiv._serverEmpId,
                pendingUnmergeDiv._serverStartDay
            ).then(() => window.location.reload());
            return;
        }

        // ── JS-created merge: restore from saved HTML ──────────────
        const firstTd = pendingUnmergeDiv.closest('td');
        const savedOuterHTMLs = mergeHistory.get(firstTd);

        // Also remove from DB so it won't reappear on reload
        const empId    = firstTd.dataset.mergeEmp;
        const startDay = firstTd.dataset.mergeStart;
        if (empId && startDay) {
            deleteMergeFromServer(empId, startDay);
        }

        // Restore firstTd using the first saved outerHTML
        const tmp0 = document.createElement('tbody');
        tmp0.innerHTML = `<tr>${savedOuterHTMLs[0]}</tr>`;
        const restoredFirst = tmp0.querySelector('td');
        firstTd.parentElement.replaceChild(restoredFirst, firstTd);

        // Re-insert subsequent TDs in order
        const allRestoredTds = [restoredFirst];
        let refNode = restoredFirst;
        for (let i = 1; i < savedOuterHTMLs.length; i++) {
            const tmpN = document.createElement('tbody');
            tmpN.innerHTML = `<tr>${savedOuterHTMLs[i]}</tr>`;
            const restoredTd = tmpN.querySelector('td');
            refNode.after(restoredTd);
            refNode = restoredTd;
            allRestoredTds.push(restoredTd);
        }

        // Clear every restored cell — set input to empty & autosave blank to DB
        for (const td of allRestoredTds) {
            const inp = td.querySelector('input.cell-input[type="text"]');
            if (!inp) continue;

            inp.value = '';
            inp.style.color = '';

            // Autosave empty value to DB
            const m = inp.name ? inp.name.match(/\[(\d+)\]\[(\d+)\]/) : null;
            if (m) {
                const fd = new FormData();
                fd.append('emp_id', m[1]);
                fd.append('day',    m[2]);
                fd.append('ym',     '{{ $ym }}');
                fd.append('val',    '');
                fetch('{{ route("admin.calendar.autosave") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: fd
                }).catch(err => console.error('Autosave clear failed:', err));
            }
        }

        mergeHistory.delete(firstTd);
        pendingUnmergeDiv = null;

        btn.innerHTML = oldHtml;
        btn.disabled = false;
        closeModal('unmergeModal');

        // Success toast
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,#64748b,#475569);color:white;padding:12px 20px;border-radius:14px;font-weight:700;font-size:0.85rem;z-index:9999;box-shadow:0 6px 20px rgba(100,116,139,0.4);display:flex;align-items:center;gap:8px;';
        toast.innerHTML = '<i class="fas fa-link-slash"></i> Cells unmerged!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }

    /* ── All Present This Month ────────────────────────────────── */
    function markAllPresentThisMonth() {
        document.getElementById('actionDropdown').classList.remove('show');
        document.getElementById('markAllPresentModal').style.display = 'flex';
    }

    async function executeMarkAllPresent() {
        // Switch modal to loading state
        document.getElementById('mapConfirmView').style.display = 'none';
        document.getElementById('mapLoadingView').style.display = 'block';
        document.getElementById('mapDoneView').style.display    = 'none';

        const year   = Number('{{ $year }}');
        const month  = Number('{{ $month }}');
        const empIds = JSON.parse(document.getElementById('empIdsData').textContent);
        const csrf   = getCsrfToken();
        const total  = empIds.length;
        let done = 0;

        document.getElementById('mapProgressCount').textContent = `0 / ${total} employees`;

        for (const id of empIds) {
            const fd = new FormData();
            fd.append('emp_id', id);
            fd.append('year',   year);
            fd.append('month',  month);
            await fetch('{{ route("admin.employee.presentAll") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                body: fd
            });
            done++;
            const pct = Math.round((done / total) * 100);
            document.getElementById('mapProgressBar').style.width   = pct + '%';
            document.getElementById('mapProgressText').textContent  = `Processing employee ${done} of ${total}…`;
            document.getElementById('mapProgressCount').textContent = `${done} / ${total} employees`;
        }

        // Switch to done state
        document.getElementById('mapLoadingView').style.display = 'none';
        document.getElementById('mapDoneView').style.display    = 'block';

        setTimeout(() => window.location.reload(), 1500);
    }

    /* ── Individual View Grid Handlers ─────────────────────────── */
    function updateCellColor(selectElement, empId, day, ym) {
        const val = selectElement.value;
        const cell = document.getElementById('cell-' + day);
        
        // Remove old status classes
        cell.classList.remove('present', 'absent', 'late', 'undertime', 'halfday');
        
        if (val) {
            cell.classList.add(val === 'halfday' ? 'absent' : val);
        }

        // Auto-save logic (similar to batch save but single cell)
        const fd = new FormData();
        fd.append('emp_id', empId);
        fd.append('day', day);
        fd.append('ym', ym);
        
        // Map select values to the single-char symbols expected by calendarAutosave
        const symMap = { 'present': '/', 'absent': '1', 'late': 'L', 'undertime': 'U', 'halfday': '½' };
        fd.append('val', symMap[val] || '');

        fetch('{{ route("admin.calendar.autosave") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd
        }).catch(e => console.error('Auto-save failed', e));

        // Prompt for absence reason if needed
        if (val === 'absent' || val === 'halfday') {
            document.getElementById('abs_emp_id').value = empId;
            document.getElementById('abs_day').value    = day;
            document.getElementById('absenceDetails').innerText = `Day ${day} — Employee #${empId}`;
            document.getElementById('abs_custom_reason').value = '';
            document.getElementById('absenceReasonModal').style.display = 'flex';
            setTimeout(() => document.getElementById('abs_custom_reason').focus(), 50);
        }
    }

    function quickAbsent(empId, dateStr) {
        // Find today's day number component
        const todayDay = parseInt(dateStr.split('-')[2], 10);
        const cellSelect = document.querySelector(`#cell-${todayDay} .cell-select`);
        
        if(cellSelect) {
            cellSelect.value = 'absent';
            cellSelect.dispatchEvent(new Event('change'));
        } else {
            alert('Cannot mark absent for today on this view (likely out of current month scope, or weekend/holiday).');
        }
    }

    /* ── Color batch inputs on load & Keyboard Navigation ─────── */
    function applyInputColor(inp) {
        const v = inp.value.trim().toUpperCase();
        if (v === '/' || v === 'P') inp.style.color = '#374151';
        else if (v === 'A' || v === '1') inp.style.color = '#dc2626';
        else if (v === 'L') inp.style.color = '#b45309';
        else if (v === 'U') inp.style.color = '#4338ca';
        else inp.style.color = '';
    }

    function quickAbsent(empId, dateStr) {
        // Find today's day number component
        const todayDay = parseInt(dateStr.split('-')[2], 10);
        const cellSelect = document.querySelector(`#cell-${todayDay} .cell-select`);
        
        if (cellSelect) {
            cellSelect.value = 'absent';
            cellSelect.dispatchEvent(new Event('change'));
        } else {
            alert('Cannot mark absent for today on this view (likely out of current month scope, or weekend/holiday).');
        }
    }

    /* ── Initializations ── */
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('input.cell-input[type="text"]');
        const searchInputEl = document.getElementById('searchInput');

        inputs.forEach((inp, index) => {
            applyInputColor(inp);
            inp.dataset.index = index;

            inp.addEventListener('keydown', function(e) {
                const allCells = Array.from(document.querySelectorAll('.cell-input'));
                const tr = this.closest('tr');
                const rowCells = tr.querySelectorAll('.cell-input');
                const daysInMonth = rowCells.length;
                const currentIndex = allCells.indexOf(this);
                let nextIndex = -1;

                switch(e.key) {
                    case 'ArrowRight': nextIndex = currentIndex + 1; if (nextIndex % daysInMonth === 0) nextIndex = -1; break;
                    case 'ArrowLeft':  nextIndex = currentIndex - 1; if (currentIndex % daysInMonth === 0) nextIndex = -1; break;
                    case 'ArrowDown':
                    case 'Enter':      e.preventDefault(); nextIndex = currentIndex + daysInMonth; break;
                    case 'ArrowUp':    e.preventDefault(); nextIndex = currentIndex - daysInMonth; break;
                }

                if (nextIndex >= 0 && nextIndex < allCells.length) {
                    allCells[nextIndex].focus();
                    allCells[nextIndex].select();
                }
            });
        });

        if (searchInputEl) {
            let searchTimeout;
            searchInputEl.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                const query = searchInputEl.value.toLowerCase().trim();
                const suggestions = document.getElementById('searchSuggestions');
                if (query.length === 0 && suggestions) suggestions.style.display = 'none';
                searchTimeout = setTimeout(() => filterCalTable(), 150);
            });
        }
    });
</script>
    {{-- Preview Modal (Synched from admin/index) --}}
    <div id="tardyPreviewModal" class="custom-overlay" style="z-index:9000; display:none;">
        <div class="custom-box" style="max-width:350px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="tardyPreviewTitle" style="margin:0; font-size:1rem; color:#64748b; font-weight:700;">Records View</h3>
                <button onclick="document.getElementById('tardyPreviewModal').style.display='none'" style="background:none; border:none; cursor:pointer; color:#94a3b8;"><i class="fas fa-times"></i></button>
            </div>
            <h4 id="tardyPreviewName" style="margin-bottom:20px; font-size:1.15rem; font-weight:800; color:#1e293b; border-left:4px solid #3b82f6; padding-left:12px; line-height:1.2;"></h4>
            <div id="tardyPreviewList" style="max-height:400px; overflow-y:auto; padding-right:5px;"></div>
        </div>
    </div>

    <script>
        function showAbsencePreview(el, title = 'Absence Records') {
            const jsonStr = el.dataset.tardy;
            if(!jsonStr || jsonStr === '[]') return;
            const empName = el.dataset.name;
            
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
                    const isWop = log.type.toLowerCase().indexOf('without') !== -1;
                    const color = isWop ? '#991b1b' : '#166534';
                    const bg = isWop ? '#fee2e2' : '#dcfce3';
                    
                    html += `
                        <div style="background:${bg}; border-radius:8px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; border: 1px solid rgba(0,0,0,0.05);">
                            <div>
                                <div style="font-weight:600; color:${color}; font-size:0.9rem;">Day ${log.day}</div>
                                <div style="font-size:0.75rem; color:${color}; opacity:0.8; font-weight:500; text-transform: uppercase; margin-top:2px;">Absence</div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-weight:700; color:${color}; font-size:1.1rem;">${log.type}</div>
                            </div>
                        </div>
                    `;
                });
                listCont.innerHTML = html + '</div>';
            } catch(e) { console.error(e); }
            
            document.getElementById('tardyPreviewModal').style.display = 'flex';
        }

        function confirmLogout(e) { e.preventDefault(); document.getElementById('logoutModal').style.display = 'flex'; }
        function closeLogout() { document.getElementById('logoutModal').style.display = 'none'; }
        /* ── Tab Pill Animation JS ── */
    document.addEventListener('DOMContentLoaded', function() {
        const pill = document.getElementById('viewTabsPill');
        const indicator = document.getElementById('tabIndicator');
        const tabs = pill.querySelectorAll('.view-tab');
        const activeTab = pill.querySelector('.view-tab.active');

        let lastX = activeTab ? activeTab.offsetLeft : 0;

        function moveIndicator(target) {
            if (!target) return;
            const newX = target.offsetLeft;
            
            // Apply snake movement direction
            if (newX > lastX) {
                pill.classList.add('moving-right');
                pill.classList.remove('moving-left');
            } else if (newX < lastX) {
                pill.classList.add('moving-left');
                pill.classList.remove('moving-right');
            }
            
            indicator.style.left = newX + 'px';
            indicator.style.width = target.offsetWidth + 'px';
            lastX = newX;
        }

        // Initialize at active tab (without snake effect initially)
        if (activeTab) {
            indicator.style.transition = 'none';
            indicator.style.left = activeTab.offsetLeft + 'px';
            indicator.style.width = activeTab.offsetWidth + 'px';
            setTimeout(() => indicator.style.transition = '', 50);
        }

        tabs.forEach(tab => {
            tab.addEventListener('mouseenter', () => moveIndicator(tab));
            tab.addEventListener('mouseleave', () => {
                moveIndicator(activeTab);
            });
        });

        // Window resize handle
        window.addEventListener('resize', () => moveIndicator(activeTab));
    });
</script>
@endsection