@extends('layouts.staff')

@section('title', 'Personnel Directory | SDO QC')

@section('styles')
    <style>
        /* COLOR PALETTE & GLOBALS */
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.05);
            --glass-bg: rgba(255, 255, 255, 0.6);
            --glass-border: rgba(255, 255, 255, 0.8);
            --sky-top: #bae6fd;
            --sky-bottom: #f8fafc;
        }

        body.dark-mode {
            --primary: #60a5fa;
            --primary-hover: #3b82f6;
            --surface: #1e293b;
            --background: #0f172a;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #334155;
            --glass-bg: rgba(15, 23, 42, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --sky-top: #020617;
            --sky-bottom: #0f172a;
        }

        html, body { 
            margin: 0 !important; 
            padding: 0 !important; 
            height: 100% !important;
            background-color: var(--background); 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Attendance Badges */
        .att-badge {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 4px 10px; border-radius: 20px; font-weight: 800; font-size: 0.9rem;
            min-width: 40px; border: 1px solid transparent; transition: all 0.2s;
        }
        .att-badge-pres  { background: #dcfce3; color: #16a34a; border-color: #bbf7d0; }
        .att-badge-abs   { background: #fee2e2; color: #dc2626; border-color: #fecaca; }
        .att-badge-late  { background: #fef3c7; color: #d97706; border-color: #fde68a; }
        .att-badge-tardy { background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff; }
        .att-badge-pay   { background: #dcfce3; color: #15803d; border-color: #bbf7d0; }
        .att-badge-wop   { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
        .att-badge-zero  { background: #f8fafc; color: #cbd5e1; border-color: #e2e8f0; }

        /* Sky layer */
        .sky-layer {
            position: fixed; top: 0; left: 0; width: 100%; height: 55%;
            z-index: 0; overflow: hidden; pointer-events: none;
            background: linear-gradient(to bottom, var(--sky-top), var(--sky-bottom));
            transition: background 0.5s ease;
        }
        .sun {
            position: absolute; top: -40px; right: -40px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, #fef08a, #eab308);
            border-radius: 50%; opacity: 0.9; filter: blur(20px);
            animation: pulseSun 6s infinite alternate;
            box-shadow: 0 0 60px rgba(234, 179, 8, 0.4);
            transition: transform 1s ease, background 1s ease, box-shadow 1s ease;
        }
        .dark-mode .sun {
            background: radial-gradient(circle, #f1f5f9, #cbd5e1);
            box-shadow: 0 0 40px rgba(255, 255, 255, 0.3);
            transform: translate(-40px, 40px) scale(0.8);
            filter: blur(5px);
        }
        /* Stars for dark mode */
        .stars-overlay {
            position: absolute; inset: 0;
            background-image: radial-gradient(white 1px, transparent 0);
            background-size: 50px 50px;
            opacity: 0; transition: opacity 1s ease;
        }
        .dark-mode .stars-overlay { opacity: 0.2; }
        .cloud {
            position: absolute; color: #fff; opacity: 0.9;
            animation: floatCloud linear infinite;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
        }
        .c1 { font-size: 6rem; top: 10%; left: -15%; animation-duration: 55s; }
        .c2 { font-size: 9rem; top: 25%; left: -25%; animation-duration: 70s; animation-delay: -10s; }
        .c3 { font-size: 4rem; top: 5%;  left: -10%; animation-duration: 40s; animation-delay: -25s; }
        .plane  { position: absolute; font-size: 2.5rem; color: #fff; top: 15%; left: -10%; animation: flyPlane 25s linear infinite; }
        .balloon{ position: absolute; font-size: 3.5rem; color: #ff7e7e; top: 30%; right: -10%; animation: flyBalloon 40s linear infinite; opacity: 0.9; }
        .bird   { position: absolute; font-size: 1rem; color: #334155; animation: flyBird 10s linear infinite; }
        .bird-1 { top: 20%; left: -5%; animation-delay: 0s; }
        .bird-2 { top: 22%; left: -8%; animation-delay: 0.5s; }
        .floating-icons { position: absolute; width: 100%; height: 100%; pointer-events: none; }
        .float-icon { position: absolute; color: rgba(59,130,246,0.08); font-size: 2rem; animation: floatUp linear infinite; }
        .fi-1 { left: 10%; bottom: -50px; animation-duration: 15s; font-size: 3rem; }
        .fi-2 { left: 80%; bottom: -50px; animation-duration: 20s; animation-delay: 2s; font-size: 2.5rem; }
        .fi-3 { left: 30%; bottom: -50px; animation-duration: 18s; animation-delay: 5s; font-size: 1.5rem; }
        .fi-4 { left: 60%; bottom: -50px; animation-duration: 25s; animation-delay: 1s; font-size: 4rem; }

        /* City skyline */
        .city-skyline {
            position: fixed; bottom: 45%; width: 100%; text-align: center;
            color: #94a3b8; font-size: 7rem; white-space: nowrap;
            opacity: 0.35; z-index: 0; transform: scaleY(1.5);
            pointer-events: none;
        }

        /* Road */
        .road-container {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 45%;
            z-index: 0; overflow: hidden; pointer-events: none;
        }
        .runner  { position: absolute; bottom: 40px;  left: -10%;  font-size: 4rem; color: #475569; animation: runAcross 10s linear infinite; }
        .scooter { position: absolute; bottom: 90px;  left: -20%;  font-size: 5rem; color: #3b82f6; animation: driveAcross 7s linear infinite; z-index: 1; }
        .bus     { position: absolute; bottom: 150px; left: -30%;  font-size: 8rem; color: #fbbf24; animation: driveAcross 18s linear infinite; animation-delay: 5s; opacity: 0.8; }
        .worker  { position: absolute; bottom: 30px;  left: -15%;  font-size: 3rem; color: #334155; animation: walkAcross 25s linear infinite; animation-delay: 3s; }

        /* Keyframes */
        @keyframes floatCloud  { 0%{ transform: translateX(0); } 100%{ transform: translateX(120vw); } }
        @keyframes flyPlane    { 0%{ transform: translate(0,0) rotate(10deg); } 100%{ transform: translate(120vw,-10vh) rotate(10deg); } }
        @keyframes flyBalloon  { 0%{ transform: translateX(0); } 50%{ transform: translateX(-60vw) translateY(-50px); } 100%{ transform: translateX(-120vw); } }
        @keyframes flyBird     { 0%{ left:-5%; transform:translateY(0); } 25%{ transform:translateY(10px); } 50%{ transform:translateY(-10px); } 100%{ left:110%; } }
        @keyframes floatUp     { 0%{ transform:translateY(0) rotate(0deg); opacity:0; } 20%{ opacity:.4; } 80%{ opacity:.4; } 100%{ transform:translateY(-120vh) rotate(360deg); opacity:0; } }
        @keyframes runAcross   { 0%{ left:-10%; } 100%{ left:110%; } }
        @keyframes driveAcross { 0%{ left:-20%; } 100%{ left:120%; } }
        @keyframes walkAcross  { 0%{ left:-10%; } 100%{ left:110%; } }
        @keyframes pulseSun    { 0%{ transform:scale(1); } 100%{ transform:scale(1.15); } }


        /* PAGE WRAPPER */
        .em-page {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 24px 24px 0 24px;
            height: 100%;
            width: 100%;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* HERO BRAND CARD - GLASS EFFECT */
        .hero-brand-card {
            position: relative;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-lg);
            padding: 24px 32px;
            display: flex;
            align-items: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--glass-border);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* STAT CARDS */
        .stat-card-new {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card-new:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .stat-icon-wrap {
            width: 40px; height: 40px;
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 12px;
        }

        /* CONTROL BAR */
        .bottom-control-bar {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: 12px 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 24px;
        }
        .month-nav-btn {
            width: 36px; height: 36px;
            border-radius: var(--radius-sm);
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .month-nav-btn:hover {
            background: var(--background);
            color: var(--primary);
        }
        .search-wrap-new {
            display: flex; align-items: center;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--surface);
            height: 42px; width: 100%; max-width: 500px;
            overflow: hidden;
            transition: all 0.2s;
        }
        .search-wrap-new:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* ACTION BUTTONS */
        .btn-action {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .btn-action:hover {
            background: var(--background);
            color: var(--text-main);
        }

        /* TABLE CARD */
        .em-card {
            background: var(--surface);
            border-radius: var(--radius-md) var(--radius-md) 0 0;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            border-bottom: none;
            flex: 1 1 0; min-height: 0;
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        .em-table-wrap {
            overflow: auto; flex: 1 1 0;
        }
        table.em-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }
        table.em-table thead { position: sticky; top: 0; z-index: 10; }
        
        table.em-table thead tr.head-main th {
            padding: 12px 16px;
            font-size: 0.75rem; font-weight: 600;
            text-transform: uppercase; color: var(--text-muted);
            background: var(--background);
            border-bottom: 2px solid var(--border);
            white-space: nowrap; text-align: left;
        }
        table.em-table thead tr.head-main th.att-group-header {
            text-align: center; background: #f1f5f9; color: var(--text-main);
        }
        table.em-table thead tr.head-att th {
            padding: 8px 12px;
            font-size: 0.7rem; font-weight: 600;
            color: var(--text-muted);
            background: var(--surface); 
            border-bottom: 1px solid var(--border);
            text-align: center;
        }

        /* Tooltips */
        [data-tooltip] { position: relative; cursor: help; text-decoration: underline dotted var(--text-muted); }
        [data-tooltip]:hover::before {
            content: attr(data-tooltip);
            position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%) translateY(-8px);
            background: var(--text-main); color: white; padding: 6px 12px; border-radius: 4px;
            font-size: 0.75rem; font-weight: 500; white-space: nowrap;
            z-index: 1000; text-transform: none; letter-spacing: normal;
        }

        /* Rows */
        table.em-table tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
        table.em-table tbody tr:hover { background: rgba(59, 130, 246, 0.05); }
        .dark-mode table.em-table tbody tr:hover { background: rgba(255, 255, 255, 0.02); }
        table.em-table td {
            padding: 12px 16px; font-size: 0.875rem; color: var(--text-main);
            vertical-align: middle;
        }

        /* Profile cell */
        .emp-profile-name { font-weight: 600; color: var(--text-main); white-space: nowrap; }
        .emp-profile-id   { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }
        .emp-station-badge {
            display: inline-block; padding: 2px 8px; margin-top: 4px;
            background: #eff6ff; color: var(--primary); border-radius: 4px;
            font-size: 0.7rem; font-weight: 500; border: 1px solid #bfdbfe;
        }

        /* Status */
        .status-indicator { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 500; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .dot-active   { background: #22c55e; }
        .dot-inactive { background: #ef4444; }
        .dot-other    { background: #f59e0b; }
        .status-text-active   { color: #166534; }
        .status-text-inactive { color: #991b1b; }
        .official-time { font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; }

        /* Attendance stat values — matches admin view */
        .att-stat-val { font-size: 1.15rem; font-weight: 700; color: #374151; line-height: 1; }
        .att-stat-val.has-data { color: #2563eb; }
        .stat-yellow { color: #d97706 !important; }
        .stat-red    { color: #dc2626 !important; }
        .days-circle { font-weight: 700; font-size: 1.15rem; color: #15803d; }
        .wop-circle  { font-weight: 700; font-size: 1.15rem; color: #dc2626; }

        /* Attendance badge pills */
        .att-badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 28px; height: 28px; padding: 0 8px; border-radius: 6px;
            font-size: 0.85rem; font-weight: 600;
        }
        .att-badge-zero   { background: transparent; color: var(--text-muted); }
        .att-badge-pres   { background: #dcfce3; color: #166534; }
        .att-badge-abs    { background: #fee2e2; color: #991b1b; }
        .att-badge-late   { background: #fef3c7; color: #92400e; }
        .att-badge-tardy  { background: #f3e8ff; color: #6b21a8; }
        .att-badge-pay    { background: #dcfce3; color: #166534; font-weight: 700; }
        .att-badge-wop    { background: #fee2e2; color: #991b1b; font-weight: 700; }

        /* Search suggestions */
        .em-suggestions {
            position: absolute; top: calc(100% + 4px); left: 0; width: 100%;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            z-index: 1001; display: none; max-height: 250px; overflow-y: auto;
        }
        .suggestion-item { padding: 10px 16px; cursor: pointer; font-size: 0.85rem; transition: background .15s; }
        .suggestion-item:hover { background: var(--background); }

        .clear-search-btn {
            background: transparent; border: none; color: var(--text-muted); cursor: pointer;
            padding: 8px; display: none; transition: all 0.2s;
        }
        .clear-search-btn:hover { color: #ef4444; }

        /* Back to top */
        .back-to-top {
            position: fixed; bottom: 30px; right: 30px;
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--primary); color: white; border: none;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; cursor: pointer;
            box-shadow: var(--shadow-md);
            opacity: 0; visibility: hidden; transform: translateY(20px);
            transition: all 0.3s ease; z-index: 999;
        }
        .back-to-top.show  { opacity: 1; visibility: visible; transform: translateY(0); }
        .back-to-top:hover { background: var(--primary-hover); transform: translateY(-3px); }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim-fade { animation: fadeIn 0.4s ease forwards; }

        @media print {
            body * { visibility: hidden; }
            #empTable, #empTable * { visibility: visible; }
            #empTable { position: absolute; left: 0; top: 0; width: 100%; }
            .em-page { display: block; padding: 0; height: auto; }
            .bottom-control-bar, .hero-brand-card, .stat-card-new, .page-bg-blobs { display: none !important; }
        }
        /* PRINT STYLES */
        @media print {
            body { background: white !important; }
            .hero-brand-card, .bottom-control-bar, .em-suggestions, .stat-card-new, .back-to-top, .blob-1, .blob-2, .float-items, .city-skyline, .road-container { display: none !important; }
            .em-page { padding: 0 !important; overflow: visible !important; height: auto !important; }
            .em-table-wrap { overflow: visible !important; box-shadow: none !important; margin: 0 !important; padding: 0 !important; }
            .em-card { box-shadow: none !important; margin: 0 !important; padding: 0 !important; border: none !important; }
            .em-table { width: 100% !important; border-collapse: collapse !important; border: 1px solid #000; }
            th, td { border: 1px solid #000 !important; font-size: 10pt !important; padding: 4px !important; color: black !important; }

            /* Signatories */
            .print-signatories { 
                display: flex !important; 
                margin-top: 40px; 
                justify-content: space-between; 
                width: 100%; 
                page-break-inside: avoid; 
                color: black !important; 
                font-family: "Inter", Arial, sans-serif; 
                font-size: 11pt; 
                padding: 0 50px;
            }
            .sig-block { 
                text-align: center; 
                flex: 1;
            }
            .sig-title { 
                font-weight: bold; 
                margin-bottom: 40px; 
                text-align: left; 
            }
            .sig-line { 
                border-bottom: 1px solid black; 
                width: 90%; 
                margin: 0 auto 5px auto; 
                min-height: 22px;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
@endsection

@section('content')
    {{-- Animated Login-style Background --}}
    <div class="sky-layer">
        <div class="stars-overlay"></div>
        <div class="sun"></div>
        <i class="fas fa-cloud c1 cloud"></i>
        <i class="fas fa-cloud c2 cloud"></i>
        <i class="fas fa-cloud c3 cloud"></i>
        <i class="fas fa-plane plane"></i>
        <i class="fas fa-parachute-box balloon"></i>
        <i class="fas fa-dove bird bird-1"></i>
        <i class="fas fa-dove bird bird-2"></i>
        <div class="floating-icons">
            <i class="fas fa-book float-icon fi-1"></i>
            <i class="fas fa-cog float-icon fi-2"></i>
            <i class="fas fa-user-graduate float-icon fi-3"></i>
            <i class="fas fa-folder-open float-icon fi-4"></i>
        </div>
    </div>
    <div class="city-skyline">
        <i class="fas fa-school"></i> <i class="fas fa-building"></i> <i class="fas fa-city"></i>
        <i class="fas fa-hospital"></i> <i class="fas fa-landmark"></i>
    </div>
    <div class="road-container">
        <i class="fas fa-bus-alt bus"></i>
        <i class="fas fa-person-running runner"></i>
        <i class="fas fa-motorcycle scooter"></i>
        <i class="fas fa-user-tie worker"></i>
    </div>

    @php
        [$prevYear, $prevMonth] = explode('-', $prevDate);
        [$nextYear, $nextMonth] = explode('-', $nextDate);

        function sfmtMins(int $mins): string
        {
            if ($mins <= 0) return '—';
            $h = intdiv($mins, 60);
            $m = $mins % 60;
            return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
        }
    @endphp

    <div class="em-page">

        {{-- HEADER --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Hero Brand Card (Glassmorphism) --}}
            <div class="hero-brand-card anim-fade">
                {{-- Left: Logo + Title --}}
                <div style="display:flex;align-items:center;gap:20px;position:relative;z-index:2;flex:1;">
                    <div style="width:64px;height:64px;background:rgba(255,255,255,0.7);border-radius:12px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.9);flex-shrink:0;">
                        <img src="{{ asset('logo.png') }}"
                            onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/1/1e/Department_of_Education_%28DepEd%29_Seal.svg/1200px-Department_of_Education_%28DepEd%29_Seal.svg.png'"
                            alt="DepEd Logo"
                            style="height:48px;">
                    </div>
                    <div>
                        <p style="font-size:0.75rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:1px;margin:0 0 4px;">Department of Education</p>
                        <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-main);margin:0 0 6px;line-height:1.2;">SDO Quezon City Directory</h1>
                        <p style="font-size:0.85rem;color:var(--text-muted);font-weight:600;margin:0;">
                            <i class="fas fa-calendar-alt" style="margin-right:6px;color:var(--primary);"></i>
                            Personnel Attendance Overview &bull; 
                            <span id="monthLabel" style="color:var(--primary);font-weight:700;">{{ $monthLabel }}</span>
                        </p>
                    </div>
                </div>

                {{-- Right: Profile + Clock + Weather --}}
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:12px;position:relative;z-index:10;">
                    {{-- Profile pill --}}
                    <div onclick="openProfileModal()" style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.7);border:1px solid rgba(255,255,255,0.9);border-radius:30px;padding:6px 16px 6px 6px;cursor:pointer;transition:all 0.2s;box-shadow:var(--shadow-sm);"
                        onmouseover="this.style.background='rgba(255,255,255,0.95)'" onmouseout="this.style.background='rgba(255,255,255,0.7)'">
                        @if(session('user_photo'))
                            <img src="{{ asset(session('user_photo')) }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                        @else
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-size:0.9rem;font-weight:700;">
                                {{ substr(Auth::user()->name ?? 'S', 0, 1) }}
                            </div>
                        @endif
                        <span style="font-size:0.85rem;font-weight:700;color:var(--text-main);">{{ explode(' ', Auth::user()->name ?? 'Staff')[0] }}</span>
                        <i class="fas fa-chevron-down" style="font-size:0.7rem;color:var(--text-muted);"></i>
                    </div>
                    
                    {{-- Clock + Weather --}}
                    <div style="display:flex;gap:10px;align-items:center;">
                        <button onclick="toggleDarkMode()" class="btn-action" id="themeToggle" title="Toggle Theme" style="background:var(--glass-bg); border-radius:50%; border:1px solid var(--glass-border); width:44px; height:44px;">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </button>
                        <div style="background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:8px;padding:8px 12px;text-align:right;box-shadow:var(--shadow-sm);backdrop-filter:blur(10px);">
                            <span id="liveTime" style="font-size:1.1rem;font-weight:700;color:var(--text-main);line-height:1;display:block;">--:-- <span style="font-size:0.75rem;color:var(--text-muted);">--</span></span>
                            <span id="liveDate" style="font-size:0.7rem;font-weight:600;color:var(--text-muted);display:block;margin-top:4px;">--- --, ----</span>
                        </div>
                        <div id="weatherWidget" style="display:flex;align-items:center;gap:10px;background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:8px;padding:8px 12px;cursor:pointer;box-shadow:var(--shadow-sm);backdrop-filter:blur(10px);" title="Live Weather">
                            <i class="fas fa-sun" id="weatherIcon" style="font-size:1.4rem;color:#fbbf24;"></i>
                            <div style="text-align:left;">
                                <span id="weatherTemp" style="font-size:1rem;font-weight:700;color:var(--text-main);line-height:1;display:block;">--°C</span>
                                <span style="font-size:0.65rem;font-weight:600;color:var(--text-muted);display:block;margin-top:2px;">Quezon City</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $activeCount   = 0;
                $leaveCount    = 0;
                $inactiveCount = 0;
                foreach ($employees as $emp) {
                    if ($emp->status === 'ACTIVE') $activeCount++;
                    elseif ($emp->status === 'ON LEAVE' || $emp->status === 'OTHERS') $leaveCount++;
                    elseif ($emp->status === 'INACTIVE') $inactiveCount++;
                }
            @endphp

            {{-- Stats Row --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
                <div class="stat-card-new anim-fade" style="animation-delay: 0.1s;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div class="stat-icon-wrap" style="background:#eff6ff;color:#2563eb;"><i class="fas fa-users"></i></div>
                    </div>
                    <span style="font-size:0.8rem;color:var(--text-muted);font-weight:500;margin-bottom:4px;">Total Personnel</span>
                    <span style="font-size:1.75rem;font-weight:700;color:var(--text-main);line-height:1;">{{ count($employees) }}</span>
                </div>
                
                <div class="stat-card-new anim-fade" style="animation-delay: 0.2s;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div class="stat-icon-wrap" style="background:#dcfce3;color:#16a34a;"><i class="fas fa-user-check"></i></div>
                    </div>
                    <span style="font-size:0.8rem;color:var(--text-muted);font-weight:500;margin-bottom:4px;">Active Service</span>
                    <span style="font-size:1.75rem;font-weight:700;color:var(--text-main);line-height:1;">{{ $activeCount }}</span>
                </div>

                <div class="stat-card-new anim-fade" style="animation-delay: 0.3s;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div class="stat-icon-wrap" style="background:#fef3c7;color:#d97706;"><i class="fas fa-user-clock"></i></div>
                    </div>
                    <span style="font-size:0.8rem;color:var(--text-muted);font-weight:500;margin-bottom:4px;">On Leave / Other</span>
                    <span style="font-size:1.75rem;font-weight:700;color:var(--text-main);line-height:1;">{{ $leaveCount }}</span>
                </div>

                <div class="stat-card-new anim-fade" style="animation-delay: 0.4s;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div class="stat-icon-wrap" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-user-times"></i></div>
                    </div>
                    <span style="font-size:0.8rem;color:var(--text-muted);font-weight:500;margin-bottom:4px;">Inactive</span>
                    <span style="font-size:1.75rem;font-weight:700;color:var(--text-main);line-height:1;">{{ $inactiveCount }}</span>
                </div>
            </div>

            {{-- Control Bar --}}
            <div class="bottom-control-bar anim-fade" style="animation-delay: 0.5s;">
                {{-- Month Nav --}}
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="month-nav-btn" data-month="{{ $prevYear }}-{{ sprintf('%02d', $prevMonth) }}" onclick="goToMonth(this.getAttribute('data-month'))" title="Previous Month">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div style="position:relative;display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--background);border-radius:6px;border:1px solid var(--border);">
                        <i class="fas fa-calendar" style="color:var(--primary);font-size:0.9rem;"></i>
                        <span style="font-size:0.875rem;font-weight:600;color:var(--text-main);">{{ $monthLabel }}</span>
                        <input type="month" id="monthPicker" value="{{ $monthVal }}" onchange="goToMonth(this.value)"
                            style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;">
                    </div>
                    <button class="month-nav-btn" data-month="{{ $nextYear }}-{{ sprintf('%02d', $nextMonth) }}" onclick="goToMonth(this.getAttribute('data-month'))" title="Next Month">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                {{-- Search --}}
                <div style="display:flex;justify-content:center;">
                    <div class="search-wrap-new">
                        <select id="stationFilter" style="border:none;background:var(--background);padding:0 16px;font-size:0.85rem;font-weight:500;color:var(--text-main);outline:none;cursor:pointer;border-right:1px solid var(--border);height:100%;flex-shrink:0;">
                            <option value="">All Stations</option>
                            @foreach($stations as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                        <div style="position:relative;display:flex;align-items:center;flex:1;min-width:0;">
                            <i class="fas fa-search" style="color:var(--text-muted);font-size:0.9rem;margin:0 12px;"></i>
                            <input type="text" id="searchInput" placeholder="Search personnel..."
                                style="border:none;outline:none;background:transparent;font-size:0.875rem;color:var(--text-main);width:100%;" autocomplete="off">
                            <button id="clearSearch" class="clear-search-btn" title="Clear"><i class="fas fa-times"></i></button>
                            <div id="searchSuggestions" class="em-suggestions"></div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="btn-action" onclick="openPrintModal()" title="Print">
                        <i class="fas fa-print"></i>
                    </button>
                    <button class="btn-action" onclick="openExportModal()" title="Export Excel" style="color:#16a34a; border-color:#bbf7d0; background:#f0fdf4;">
                        <i class="fas fa-file-excel"></i>
                    </button>
                    <a href="{{ route('logout') }}" onclick="confirmLogout(event)"
                        style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;text-decoration:none;transition:all .2s;"
                        title="Logout"
                        onmouseover="this.style.background='#fee2e2'"
                        onmouseout="this.style.background='#fef2f2'">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="em-card anim-fade" style="animation-delay: 0.6s;">
            <div class="em-table-wrap">
                <table class="em-table" id="empTable">
                    <thead>
                        <tr class="head-main">
                            <th rowspan="2" style="cursor:pointer;" onclick="sortTable(0,'string')">Employee Profile <i class="fas fa-sort" style="margin-left:4px;color:var(--border);"></i></th>
                            <th rowspan="2" style="cursor:pointer;" onclick="sortTable(1,'string')">Status &amp; Time <i class="fas fa-sort" style="margin-left:4px;color:var(--border);"></i></th>
                            <th colspan="5" class="att-group-header">Attendance ({{ strtoupper($monthLabel) }})</th>
                            <th rowspan="2" style="text-align:center;" data-tooltip="Total days marked present with pay">Days W/ Pay</th>
                            <th rowspan="2" style="text-align:center;" data-tooltip="Total absent days without pay">W.O.P.</th>
                        </tr>
                        <tr class="head-att">
                            <th data-tooltip="Total Present Days">PRES</th>
                            <th data-tooltip="Total Absent Days">ABS</th>
                            <th data-tooltip="Total Undertime Instances">UNDRTM</th>
                            <th data-tooltip="Total Late Instances">LATE</th>
                            <th data-tooltip="Total Tardy (Late + Undertime)">TARDY</th>
                        </tr>
                    </thead>
                    <tbody id="empTableBody">
                        @forelse($employees as $emp)
                            @php
                                $empId  = $emp->id;
                                $status = $emp->status ?? 'ACTIVE';
                                $att    = $attendanceSummary[$empId] ?? ['present'=>0,'absent'=>0,'late'=>0,'undertime'=>0,'halfday'=>0];
                                $lMin   = $lateMins[$empId] ?? 0;
                                $utMin  = $undertimeMins[$empId] ?? 0;
                                $reasons = $absReasons[$empId] ?? ['with_pay' => 0, 'without_pay' => 0];

                                $empLateLogs = $lateDetails[$empId] ?? [];
                                $empUtLogs   = $utDetails[$empId] ?? [];
                                $lateCount   = count($empLateLogs);
                                $utCount     = count($empUtLogs);
                                $tardyCount  = $lateCount + $utCount;

                                $daysWithPay = $reasons['with_pay'];
                                $wop         = $reasons['without_pay'];
                                $absCount    = $daysWithPay + $wop;
                                $presCount   = ($att['present'] ?? 0) + $lateCount;

                                $tardyLog = array_merge($empLateLogs, $empUtLogs);
                                usort($tardyLog, function($a, $b) { return $a['day'] <=> $b['day']; });

                                $empAbsDetails   = $absDetails[$empId] ?? ['with_pay_days' => [], 'without_pay_days' => []];
                                $empWithPayLogs  = array_map(function($d) { return ['day' => $d, 'type' => 'With Pay']; }, $empAbsDetails['with_pay_days']);
                                $empWopLogs      = array_map(function($d) { return ['day' => $d, 'type' => 'Without Pay']; }, $empAbsDetails['without_pay_days']);

                                $statusClass = match(true) {
                                    $status === 'ACTIVE'   => 'dot-active',
                                    $status === 'INACTIVE' => 'dot-inactive',
                                    default                => 'dot-other',
                                };
                                $statusTextClass = match(true) {
                                    $status === 'ACTIVE' => 'status-text-active',
                                    default              => 'status-text-inactive',
                                };
                            @endphp
                            <tr class="emp-row"
                                data-search="{{ strtolower(($emp->last_name??'').' '.($emp->first_name??'').' '.($emp->middle_name??'').' '.($emp->emp_number??'').' '.($emp->station??'')) }}">

                                {{-- Profile --}}
                                <td>
                                    <div class="emp-profile-name">{{ $emp->last_name ?? '' }}, {{ $emp->first_name ?? '' }} {{ $emp->middle_name ?? '' }}</div>
                                    <div class="emp-profile-id">ID: {{ $emp->emp_number ?? 'N/A' }}</div>
                                    @if($emp->station)
                                        <div class="emp-station-badge">{{ $emp->station }}</div>
                                    @endif
                                </td>

                                {{-- Status & Time (read-only) --}}
                                <td>
                                    <div class="status-indicator" style="margin-bottom: 8px;">
                                        <span class="status-dot {{ $statusClass }}"></span>
                                        @php $statusColor = $status === 'ACTIVE' ? '#15803d' : '#ef4444'; @endphp
                                        <span style="color:{{ $statusColor }}; font-weight:600; font-size:0.8rem;">{{ $status }}</span>
                                    </div>
                                    <div class="official-time">
                                        <i class="far fa-clock" style="margin-right:4px;"></i>{{ $emp->official_time ?? '—' }}
                                    </div>
                                </td>

                                {{-- PRES --}}
                                <td style="text-align:center;">
                                    <span class="att-stat-val {{ $presCount > 0 ? 'has-data' : '' }}">{{ $presCount }}</span>
                                </td>

                                {{-- ABS --}}
                                <td style="text-align:center;">
                                    @php
                                        $allAbsLogs = array_merge($empWithPayLogs, $empWopLogs);
                                        usort($allAbsLogs, function($a, $b) { return $a['day'] <=> $b['day']; });
                                    @endphp
                                    <div class="tardy-cell-wrap"
                                         style="cursor: {{ $absCount > 0 ? 'pointer' : 'default' }}; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-tardy="{{ json_encode($allAbsLogs) }}"
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showAbsencePreview(this, 'Absent Records')"
                                         title="{{ $absCount > 0 ? 'Click to view absence dates' : '' }}"
                                         onmouseover="if(this.dataset.tardy !== '[]') this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="att-stat-val {{ $absCount > 0 ? 'stat-red' : '' }}">{{ $absCount }}</span>
                                    </div>
                                </td>

                                {{-- UNDRTM --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: {{ $utCount > 0 ? 'pointer' : 'default' }}; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-tardy="{{ json_encode($empUtLogs) }}"
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showTardyPreview(this, 'Undertime Records')"
                                         title="{{ $utCount > 0 ? 'Click to view undertime details' : '' }}"
                                         onmouseover="if(this.dataset.tardy !== '[]') this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="att-stat-val {{ $utCount > 0 ? 'stat-yellow' : '' }}">{{ $utCount }}</span>
                                        @if($utMin > 0)
                                            <span style="font-size: 0.62rem; color: #d97706; font-weight: 700; margin-top: 1px; letter-spacing: -0.2px;">{{ $utMin }}m early</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- LATE --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-tardy="{{ json_encode($empLateLogs) }}"
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showTardyPreview(this, 'Late Records')"
                                         title="Click to view late details"
                                         onmouseover="this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="att-stat-val {{ $lMin > 0 ? 'stat-yellow' : '' }}">{{ $lMin }}</span>
                                    </div>
                                </td>

                                {{-- TARDY --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-tardy="{{ json_encode($tardyLog) }}"
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showTardyPreview(this, 'Tardy Records')"
                                         title="Click to view tardy details"
                                         onmouseover="this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="att-stat-val {{ $tardyCount > 0 ? 'stat-yellow' : '' }}">{{ $tardyCount }}</span>
                                    </div>
                                </td>

                                {{-- Days W/ Pay --}}
                                <td style="text-align:center;">
                                    <div class="tardy-cell-wrap"
                                         style="cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%; transition: transform 0.1s;"
                                         data-tardy="{{ json_encode($empWithPayLogs) }}"
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
                                         data-tardy="{{ json_encode($empWopLogs) }}"
                                         data-name="{{ ($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '') }}"
                                         onclick="showAbsencePreview(this, 'Without Pay Absences')"
                                         title="Click to view dates"
                                         onmouseover="if(this.dataset.tardy !== '[]') this.style.transform='scale(1.1)';"
                                         onmouseout="this.style.transform='scale(1)';">
                                        <span class="wop-circle">{{ $wop > 0 ? $wop : '—' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center; padding: 60px 20px; color: var(--text-muted);">
                                    <i class="fas fa-users-slash" style="font-size:2.5rem; margin-bottom:12px; display:block; color:#cbd5e1;"></i>
                                    No employees found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Signatories (Print Only) --}}
            <div class="print-signatories" style="display: none;">
                <div class="sig-block">
                    <div class="sig-title">Prepared by:</div>
                    <div id="sig-prepared-name" class="sig-line" style="font-weight: 800; text-transform: uppercase;"></div>
                    <div id="sig-prepared-pos" style="font-size: 0.72rem; font-weight: 600; color: #4b5563;">Signature over Printed Name</div>
                    <div id="sig-prepared-pos2" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                    <div id="sig-prepared-pos3" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                </div>
                <div class="sig-block">
                    <div class="sig-title">Certified Correct by:</div>
                    <div id="sig-certified-name" class="sig-line" style="font-weight: 800; text-transform: uppercase;"></div>
                    <div id="sig-certified-pos" style="font-size: 0.72rem; font-weight: 600; color: #4b5563;">Human Resource Management Officer II</div>
                    <div id="sig-certified-pos2" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                    <div id="sig-certified-pos3" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                </div>
                <div class="sig-block">
                    <div class="sig-title">Verified Correct by:</div>
                    <div id="sig-verified-name" class="sig-line" style="font-weight: 800; text-transform: uppercase;"></div>
                    <div id="sig-verified-pos" style="font-size: 0.72rem; font-weight: 600; color: #4b5563;">Human Resource Management Officer V</div>
                    <div id="sig-verified-pos2" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                    <div id="sig-verified-pos3" style="font-size: 0.72rem; font-weight: 500; color: #6b7280; margin-top: 1px;"></div>
                </div>
            </div>
        </div>

    </div>{{-- em-page --}}

    <div id="profileModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:1050; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div style="background:var(--surface); border-radius:var(--radius-lg); width:100%; max-width:400px; padding:32px; box-shadow:var(--shadow-md);">
            <div style="width:80px;height:80px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px auto;border:4px solid white;box-shadow:var(--shadow-sm);">
                @if(session('user_photo'))
                    <img id="modalAvatarPreview" src="{{ asset(session('user_photo')) }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                @else
                    <div id="modalAvatarInitial" style="width:100%;height:100%;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;">
                        {{ substr(Auth::user()->name ?? 'S', 0, 1) }}
                    </div>
                    <img id="modalAvatarPreview" src="#" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:none;">
                @endif
            </div>
            <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-main);text-align:center;margin-bottom:8px;">Edit Profile Photo</h2>
            <p style="font-size:0.875rem;color:var(--text-muted);text-align:center;margin-bottom:24px;">Update your profile picture for the directory.</p>
            <form action="{{ route('staff.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                @csrf
                <div style="margin-bottom:24px;">
                    <label for="profile_photo" style="display:block;padding:12px;background:var(--background);color:var(--text-main);border-radius:var(--radius-md);font-weight:600;cursor:pointer;transition:0.2s;border:2px dashed var(--border);text-align:center;">
                        <i class="fas fa-camera" style="margin-right:8px;color:var(--text-muted);"></i> Choose New Image
                        <input type="file" id="profile_photo" name="self_photo" accept="image/*" style="display:none;" onchange="previewProfileImage(this)">
                    </label>
                </div>
                <div style="display:flex;gap:12px;">
                    <button type="button" onclick="closeProfileModal()" style="flex:1;padding:10px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-main);font-weight:600;cursor:pointer;">Cancel</button>
                    <button type="submit" style="flex:1;padding:10px;border-radius:var(--radius-sm);border:none;background:var(--primary);color:white;font-weight:600;cursor:pointer;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="tardyPreviewModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9000; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div class="custom-box" style="width: 90%; max-width:400px; text-align:left; background:#fff; border-radius:12px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.1); position:relative;">
            <h3 style="margin-bottom:5px; color:#1e293b; display:flex; align-items:center; gap:8px; font-size:1.15rem; font-weight:700;">
                <i class="fas fa-clock" style="color:#ef4444;"></i> <span id="tardyPreviewTitle">Tardy Records</span>
            </h3>
            <p id="tardyPreviewName" style="color:#64748b; font-size:0.85rem; margin-bottom:15px; font-weight:600;"></p>
            
            <div id="tardyPreviewList" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                <!-- Rendered by JS -->
            </div>
            
            <div style="text-align:right; margin-top:20px;">
                <button onclick="document.getElementById('tardyPreviewModal').style.display='none'" style="padding:8px 16px; background:#f1f5f9; color:#475569; border:none; border-radius:6px; font-weight:600; cursor:pointer;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Close</button>
            </div>
        </div>
    </div>

    {{-- Print Selection Modal --}}
    <div id="printModal" class="custom-overlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9000; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div class="custom-box" style="width: 95%; max-width:900px; max-height: 90vh; overflow-y: auto; text-align:left; background:#fff; border-radius:12px; padding:30px; box-shadow:0 10px 25px rgba(0,0,0,0.1); position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h2 style="margin:0;"><i class="fas fa-print" style="color:#0f766e;margin-right:8px;"></i>Print Options</h2>
                <button onclick="document.getElementById('printModal').style.display='none'" style="border:none; background:#f1f5f9; width:34px; height:34px; border-radius:10px; cursor:pointer; color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <form id="printForm">
                <p style="color:#64748b;font-size:0.85rem;margin-bottom:12px;">Select which station's records you want to print.</p>
                <div class="form-group" style="margin-bottom:20px;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">Select Station</label>
                    <select id="printStationSelect" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; color:#1e293b; max-height:200px; overflow-y:auto; outline:none; cursor:pointer; background:#f8fafc; transition:all 0.2s ease;">
                        <option value="all">All Stations</option>
                        @foreach($stations as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; border-top: 2px solid #f1f5f9; padding-top: 20px;">
                    {{-- Prepared By --}}
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Prepared By</div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">Signatory Name</label>
                            <input type="text" id="printSigPrepName" placeholder="CHRISTINE JOY C. MAAPOY" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; font-weight:700; text-transform:uppercase; box-sizing:border-box;">
                        </div>
                        <div id="print-prep-pos1" style="margin-bottom: 10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 1
                                <button type="button" onclick="addPosField('print', 'prep')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                            </label>
                            <input type="text" id="printSigPrepPos" placeholder="Administrative Assistant III" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                        <div id="print-prep-pos2" style="display:none; margin-top:10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 2
                                <button type="button" onclick="removePosField('print', 'prep', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="printSigPrepPos2" placeholder="E-Form7 In-Charge" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                        <div id="print-prep-pos3" style="display:none; margin-top:10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 3
                                <button type="button" onclick="removePosField('print', 'prep', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="printSigPrepPos3" placeholder="..." style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                    </div>

                    {{-- Certified By --}}
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Certified Correct By</div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">Signatory Name</label>
                            <input type="text" id="printSigCertName" placeholder="MICHELLE A. MAL-IN" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; font-weight:700; text-transform:uppercase; box-sizing:border-box;">
                        </div>
                        <div id="print-cert-pos1" style="margin-bottom: 10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 1
                                <button type="button" onclick="addPosField('print', 'cert')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                            </label>
                            <input type="text" id="printSigCertPos" placeholder="HRMO II" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                        <div id="print-cert-pos2" style="display:none; margin-top:10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 2
                                <button type="button" onclick="removePosField('print', 'cert', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="printSigCertPos2" placeholder="Administrative Officer IV" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                        <div id="print-cert-pos3" style="display:none; margin-top:10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 3
                                <button type="button" onclick="removePosField('print', 'cert', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="printSigCertPos3" placeholder="..." style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                    </div>

                    {{-- Verified By --}}
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="font-size:0.75rem; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:15px; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Verified Correct By</div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">Signatory Name</label>
                            <input type="text" id="printSigVerName" placeholder="ROSELYN B. SENCIL" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; font-weight:700; text-transform:uppercase; box-sizing:border-box;">
                        </div>
                        <div id="print-ver-pos1" style="margin-bottom: 10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 1
                                <button type="button" onclick="addPosField('print', 'ver')" style="background:#3b82f6; color:white; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-plus"></i></button>
                            </label>
                            <input type="text" id="printSigVerPos" placeholder="HRMO V" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                        <div id="print-ver-pos2" style="display:none; margin-top:10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 2
                                <button type="button" onclick="removePosField('print', 'ver', 2)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="printSigVerPos2" placeholder="Administrative Officer V" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                        <div id="print-ver-pos3" style="display:none; margin-top:10px;">
                            <label style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">
                                Position Line 3
                                <button type="button" onclick="removePosField('print', 'ver', 3)" style="background:#fecaca; color:#ef4444; border:none; border-radius:4px; padding:2px 6px; font-size:0.6rem; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </label>
                            <input type="text" id="printSigVerPos3" placeholder="..." style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; box-sizing:border-box;">
                        </div>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px;">
                    <button type="button" onclick="document.getElementById('printModal').style.display='none'" style="padding:10px 18px; background:#f1f5f9; color:#475569; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:0.9rem;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Cancel</button>
                    <button type="button" onclick="executePrint()" style="padding:10px 18px; border-radius:8px; font-weight:600; cursor:pointer; background: linear-gradient(135deg, #0f766e, #115e59); color:white; border:none; font-size:0.9rem; box-shadow: 0 4px 12px rgba(15,118,110,0.3);"><i class="fas fa-print"></i> Print Now</button>
                </div>
            </form>
        </div>
    </div>

    <div id="exportModal" class="custom-overlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9000; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div class="custom-box" style="width: 90%; max-width:400px; text-align:left; background:#fff; border-radius:12px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.1); position:relative;">
            <h2 style="margin-bottom:18px; font-size:1.25rem;"><i class="fas fa-file-excel" style="color:#10b981;margin-right:8px;"></i>Export to Excel</h2>
            <form id="exportForm">
                <p style="color:#64748b;font-size:0.85rem;margin-bottom:12px;">Select which station's records you want to export.</p>
                <div class="form-group" style="margin-bottom:20px;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:5px;">Station</label>
                    <select id="exportStationSelect" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0; font-size:0.9rem; color:#1e293b; max-height:200px; overflow-y:auto; outline:none; cursor:pointer; background:#f8fafc; transition:all 0.2s ease;">
                        <option value="All">All Stations</option>
                        @foreach($stations as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                    <button type="button" class="modal-btn modal-btn-cancel" onclick="document.getElementById('exportModal').style.display='none'" style="padding:8px 16px; background:#f1f5f9; color:#475569; border:none; border-radius:6px; font-weight:600; cursor:pointer;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Cancel</button>
                    <button type="button" onclick="executeExport()" class="modal-btn" style="padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer; background: linear-gradient(135deg, #10b981, #059669); color:white; border:none; box-shadow: 0 4px 12px rgba(16,185,129,0.3);"><i class="fas fa-file-excel"></i> Download Excel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="downloadProgressModal" class="custom-overlay" style="display:none; z-index:9999; justify-content:center; align-items:center;">
        <div class="custom-box" style="text-align:center; max-width:320px; padding:30px;">
            <div id="dlSpinner" style="margin-bottom:20px;">
                <i class="fas fa-spinner fa-spin" style="font-size:3rem; color:#10b981;"></i>
            </div>
            <div id="dlSuccessIcon" style="display:none; margin-bottom:20px;">
                <i class="fas fa-check-circle" style="font-size:3.5rem; color:#10b981;"></i>
            </div>
            <h3 id="dlProgressTitle" style="color:#1e293b;margin-bottom:8px;">Exporting Excel...</h3>
            <p id="dlProgressText" style="color:#64748b;font-size:0.85rem;margin:0;">Generating your report, please wait</p>
        </div>
    </div>

    <button id="backToTop" class="back-to-top" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>
@endsection

@section('scripts')
    <script>
        /* Search + Filter */
        const searchInput    = document.getElementById('searchInput');
        const clearSearchBtn = document.getElementById('clearSearch');
        const stationFilter  = document.getElementById('stationFilter');
        const suggestionsBox = document.getElementById('searchSuggestions');
        const rows           = document.querySelectorAll('#empTableBody .emp-row');

        function applyFilters() {
            const q       = searchInput.value.toLowerCase().trim();
            const station = stationFilter.value;
            clearSearchBtn.style.display = q.length > 0 ? 'inline-flex' : 'none';
            rows.forEach(r => {
                const searchMatch  = r.dataset.search.includes(q);
                const rStation     = r.querySelector('.emp-station-badge')?.textContent || '';
                const stationMatch = (station === '' || rStation === station);
                r.style.display    = (searchMatch && stationMatch) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', function () {
            applyFilters();
            const q = this.value.toLowerCase().trim();
            if (q.length >= 2) {
                const matches = [];
                rows.forEach(r => {
                    const cell = r.querySelector('td .emp-profile-name');
                    if (cell && cell.textContent.toLowerCase().includes(q))
                        matches.push(cell.textContent.trim());
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
        });

        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            suggestionsBox.style.display = 'none';
            applyFilters();
            searchInput.focus();
        });

        stationFilter.addEventListener('change', applyFilters);

        function selectSuggestion(name) {
            searchInput.value = name;
            suggestionsBox.style.display = 'none';
            applyFilters();
        }

        window.addEventListener('click', e => {
            if (!e.target.closest('.search-wrap-new')) suggestionsBox.style.display = 'none';
        });

        /* Table Sorting */
        let sortDirections = [true, true, true];
        function sortTable(colIndex, type) {
            const tbody = document.getElementById('empTableBody');
            const trs   = Array.from(tbody.querySelectorAll('tr.emp-row'));
            const isAsc = sortDirections[colIndex];
            sortDirections[colIndex] = !sortDirections[colIndex];
            
            trs.sort((a, b) => {
                const tdA = a.children[colIndex].textContent.trim().toLowerCase();
                const tdB = b.children[colIndex].textContent.trim().toLowerCase();
                if (type === 'number') {
                    const numA = parseFloat(tdA.replace(/[^0-9.-]+/g,'')) || 0;
                    const numB = parseFloat(tdB.replace(/[^0-9.-]+/g,'')) || 0;
                    return isAsc ? numA - numB : numB - numA;
                }
                return isAsc ? tdA.localeCompare(tdB) : tdB.localeCompare(tdA);
            });
            trs.forEach(tr => tbody.appendChild(tr));
        }

        /* Back to Top */
        const bttButton = document.getElementById('backToTop');
        const tableWrap = document.querySelector('.em-table-wrap');
        tableWrap.addEventListener('scroll', () => {
            bttButton.classList.toggle('show', tableWrap.scrollTop > 150);
        });
        bttButton.addEventListener('click', () => tableWrap.scrollTo({ top: 0, behavior: 'smooth' }));

        /* Month Picker */
        function goToMonth(val) {
            const [y, m] = val.split('-');
            window.location.href = `{{ url('staff/employeeview') }}?year=${y}&month=${m}`;
        }

        function addPosField(modalType, sigType) {
            const p2 = document.getElementById(`${modalType}-${sigType}-pos2`);
            const p3 = document.getElementById(`${modalType}-${sigType}-pos3`);
            if (p2 && p2.style.display === 'none') {
                p2.style.display = 'block';
            } else if (p3 && p3.style.display === 'none') {
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
            if (field) field.style.display = 'none';
        }

        function openPrintModal() {
            document.getElementById('printStationSelect').value = 'all';
            document.getElementById('printModal').style.display = 'flex';
            
            // Reset position 2 and 3 visibility
            ['prep', 'cert', 'ver'].forEach(type => {
                const el2 = document.getElementById(`print-${type}-pos2`);
                if(el2) el2.style.display = 'none';
                const el3 = document.getElementById(`print-${type}-pos3`);
                if(el3) el3.style.display = 'none';
            });
        }

        function executePrint() {
            document.getElementById('printModal').style.display = 'none';

            const dlModal = document.getElementById('downloadProgressModal');
            dlModal.style.display = 'flex';
            
            document.getElementById('dlSpinner').style.display = 'block';
            document.getElementById('dlSuccessIcon').style.display = 'none';
            document.getElementById('dlProgressTitle').innerText = 'Preparing Print...';
            document.getElementById('dlProgressTitle').style.color = '#1e293b';
            document.getElementById('dlProgressText').innerText = 'Formatting records for printing';

            const selectedStation = document.getElementById('printStationSelect').value;
            const sfFilter = document.getElementById('stationFilter');
            
            let originalFilter = '';
            if (sfFilter) {
                originalFilter = sfFilter.value;
                sfFilter.value = selectedStation === 'all' ? '' : selectedStation;
                applyFilters();
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
                if (sfFilter) {
                    sfFilter.value = originalFilter;
                    applyFilters();
                }

                // Show brief success feedback
                dlModal.style.display = 'flex';
                document.getElementById('dlSpinner').style.display = 'none';
                document.getElementById('dlSuccessIcon').style.display = 'block';
                document.getElementById('dlProgressTitle').innerText = 'Print Complete!';
                document.getElementById('dlProgressTitle').style.color = '#10b981';
                document.getElementById('dlProgressText').innerText = 'Your records have been processed.';

                setTimeout(() => {
                    dlModal.style.display = 'none';
                    document.getElementById('dlProgressTitle').style.color = '#1e293b';
                }, 2000);

            }, 800);
        }

        function openExportModal() {
            document.getElementById('exportStationSelect').value = 'All';
            document.getElementById('exportModal').style.display = 'flex';
        }

        async function executeExport() {
            const station = document.getElementById('exportStationSelect').value;
            // The value of $monthVal is '2026-03' which split gives [y, m]
            const [y, m] = document.getElementById('monthPicker').value.split('-');
            
            const url = '{{ route("admin.export.attendance") }}'
                + '?year=' + y
                + '&month=' + parseInt(m)
                + '&station=' + encodeURIComponent(station);
                
            document.getElementById('exportModal').style.display = 'none';
            
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
                let filename = 'Attendance_Report_' + station + '_' + y + '_' + parseInt(m) + '.xlsx';
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
                    dlModal.style.display = 'none';
                    document.getElementById('dlProgressTitle').style.color = '#1e293b';
                }, 2500);

            } catch (err) {
                console.error(err);
                alert('An error occurred during export. Please try again.');
                dlModal.style.display = 'none';
            }
        }

        function toggleDarkMode() {
            const body = document.body;
            const icon = document.getElementById('themeIcon');
            const isDark = body.classList.toggle('dark-mode');
            
            if (isDark) {
                icon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            }
        }

        // Initialize Theme
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
        }

        /* Live Clock */
        function updateClock() {
            const now  = new Date();
            let h      = now.getHours();
            let m      = now.getMinutes();
            let s      = now.getSeconds();
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            m = m < 10 ? '0'+m : m;
            s = s < 10 ? '0'+s : s;
            document.getElementById('liveTime').innerHTML = `${h}:${m} <span style="font-size:0.75rem;color:var(--text-muted);">${s} ${ampm}</span>`;
            const opts = { weekday:'short', year:'numeric', month:'short', day:'numeric' };
            document.getElementById('liveDate').innerText = now.toLocaleDateString('en-US', opts);
        }
        setInterval(updateClock, 1000);
        updateClock();

        /* Live Weather */
        async function fetchWeather() {
            try {
                const res  = await fetch('https://api.open-meteo.com/v1/forecast?latitude=14.676&longitude=121.0437&current_weather=true');
                const data = await res.json();
                if (data && data.current_weather) {
                    const temp = Math.round(data.current_weather.temperature);
                    const code = data.current_weather.weathercode;
                    document.getElementById('weatherTemp').innerText = `${temp}°C`;
                    let iconClass = 'fas fa-sun', color = '#fbbf24';
                    if (code === 0)                   { iconClass = 'fas fa-sun';        color = '#fbbf24'; }
                    else if (code >= 1 && code <= 3)  { iconClass = 'fas fa-cloud-sun';  color = '#cbd5e1'; }
                    else if (code >= 45 && code <= 48){ iconClass = 'fas fa-smog';       color = '#cbd5e1'; }
                    else if (code >= 51 && code <= 67){ iconClass = 'fas fa-cloud-rain'; color = '#60a5fa'; }
                    else if (code >= 71 && code <= 82){ iconClass = 'fas fa-snowflake';  color = '#7dd3fc'; }
                    else if (code >= 95)              { iconClass = 'fas fa-bolt';       color = '#eab308'; }
                    else                              { iconClass = 'fas fa-cloud';      color = '#94a3b8'; }
                    const iconEl     = document.getElementById('weatherIcon');
                    iconEl.className = iconClass;
                    iconEl.style.color = color;
                }
            } catch (err) {
                document.getElementById('weatherTemp').innerText = '--°C';
            }
        }
        fetchWeather();
        setInterval(fetchWeather, 30 * 60 * 1000);

        /* Profile Modal */
        function openProfileModal()  { document.getElementById('profileModal').style.display = 'flex'; }
        function closeProfileModal() { document.getElementById('profileModal').style.display = 'none'; }
        function previewProfileImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('modalAvatarPreview');
                    const initial = document.getElementById('modalAvatarInitial');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (initial) initial.style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        /* Tardy/Absence Modals (Matching admin/index.blade.php) */
        function showTardyPreview(el, title = 'Tardy Records') {
            const jsonStr = el.dataset.tardy;
            const empName = el.dataset.name;
            
            document.getElementById('tardyPreviewName').innerText = 'Employee: ' + empName;
            const titleEl = document.getElementById('tardyPreviewTitle');
            if(titleEl) titleEl.innerText = title;
            
            const listCont = document.getElementById('tardyPreviewList');
            listCont.innerHTML = '';
            
            try {
                const logs = JSON.parse(jsonStr);
                if(!logs || logs.length === 0) {
                    listCont.innerHTML = '<div style="background:#f8fafc; padding:15px; border-radius:8px; text-align:center; color:#94a3b8; font-size:0.85rem;"><i class="fas fa-info-circle"></i> No tardy records found.</div>';
                } else {
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
                                <div style="font-weight:900; color:${color}; font-size:0.95rem; font-family: monospace;">
                                    ${log.mins} <span style="font-size: 0.65rem; opacity: 0.8;">MINS</span>
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
    </script>
@endsection