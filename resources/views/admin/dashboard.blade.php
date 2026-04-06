@extends('layouts.app')

@section('title', 'Dashboard | SDO QC')

@section('styles')
    <style>
        .dash-wrap {
            display: flex;
            flex-direction: column;
            gap: 16px;
            height: calc(100vh - 80px); /* Fit to screen */
            overflow: hidden;
            padding: 2px 2px 10px;
        }

        .dash-banner {
            background: var(--glass);
            backdrop-filter: blur(25px);
            border-radius: 20px;
            padding: 16px 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: fadeInUp 0.5s ease forwards;
            flex-shrink: 0;
        }

        .dash-banner h1 {
            font-size: 1.9rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .dash-banner p {
            color: #64748b;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .banner-right {
            display: flex;
            align-items: stretch;
            gap: 14px;
        }

        /* Tear-off Calendar Widget */
        .cal-widget {
            background: white;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            overflow: hidden;
            text-align: center;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            min-width: 75px;
            transform: perspective(400px) rotateX(2deg);
            transform-origin: top;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .cal-widget:hover {
            transform: perspective(400px) rotateX(0deg) translateY(-2px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.1);
        }
        .cal-widget-header {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            padding: 5px 0;
            letter-spacing: 1px;
            border-bottom: 2px dashed rgba(255,255,255,0.4);
        }
        .cal-widget-body {
            padding: 8px 12px 6px;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
        }
        .cal-widget-day {
            font-size: 1.6rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1;
            text-shadow: 1px 1px 0px rgba(0,0,0,0.05);
        }
        .cal-widget-year {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            margin-top: 2px;
        }

        /* Digital Clock Widget */
        .time-widget {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border-radius: 14px;
            padding: 0 16px;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 20px rgba(15,23,42,0.15);
            border: 1px solid #334155;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s;
        }
        .time-widget:hover {
            transform: translateY(-2px);
        }
        .time-widget::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 45%;
            background: linear-gradient(180deg, rgba(255,255,255,0.1), transparent);
            pointer-events: none;
        }
        .time-icon {
            font-size: 1.2rem;
            color: #38bdf8;
            filter: drop-shadow(0 0 6px rgba(56,189,248,0.6));
            animation: pulseIcon 2s infinite ease-in-out;
        }
        .time-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: #f1f5f9;
            text-shadow: 0 0 10px rgba(255,255,255,0.2);
            font-variant-numeric: tabular-nums;
        }
        @keyframes pulseIcon {
            0%, 100% { opacity: 0.6; transform: scale(0.95); }
            50% { opacity: 1; transform: scale(1.05); }
        }

        /* Weather Widget */
        .weather-widget {
            background: linear-gradient(135deg, #0ea5e9, #0369a1);
            border-radius: 14px;
            padding: 0 16px;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 20px rgba(14,165,233,0.25);
            border: 1px solid #bae6fd;
            transition: transform 0.3s;
        }
        .weather-widget:hover {
            transform: translateY(-2px);
        }
        .weather-icon {
            font-size: 1.6rem;
            color: #fef08a;
            filter: drop-shadow(0 0 8px rgba(254,240,138,0.5));
        }
        .weather-text {
            font-size: 1.1rem;
            font-weight: 800;
            line-height: 1.1;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        .weather-sub {
            font-size: 0.65rem;
            font-weight: 700;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .stat-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            flex-shrink: 0;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 14px 20px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            animation: fadeInUp 0.6s ease forwards;
            display: flex;
            align-items: center;
            gap: 18px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .stat-card-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .stat-card-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card-num {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1.1;
            color: var(--dark);
            letter-spacing: -0.5px;
            margin: 2px 0;
        }

        .stat-card-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card-trend {
            font-size: 0.72rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .qa-panel {
            background: var(--glass);
            backdrop-filter: blur(25px);
            border-radius: 24px;
            padding: 28px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: var(--shadow-sm);
            animation: fadeInUp 0.7s ease forwards;
        }

        .qa-panel h3 {
            font-size: 0.85rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qa-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
        }

        .qa-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px 10px;
            border-radius: 18px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.78rem;
            color: var(--dark);
            background: rgba(255, 255, 255, 0.7);
            border: 1.5px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .qa-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0;
            border-radius: 18px;
            transition: opacity 0.3s ease;
        }

        .qa-btn:hover {
            transform: translateY(-6px) scale(1.04);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.12);
            color: white;
        }

        .qa-btn:hover::before {
            opacity: 1;
        }

        .qa-btn:hover .qa-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .qa-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .qa-btn span {
            position: relative;
            z-index: 1;
        }

        .qa-employees::before {
            background: linear-gradient(135deg, #1e293b, #0f172a);
        }

        .qa-absences::before {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
        }

        .qa-attendance::before {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .qa-forms::before {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .qa-audit::before {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        }

        .qa-employees .qa-icon {
            background: #f1f5f9;
            color: #1e293b;
        }

        .qa-absences .qa-icon {
            background: #fee2e2;
            color: #ef4444;
        }

        .qa-attendance .qa-icon {
            background: #dbeafe;
            color: #2563eb;
        }

        .qa-forms .qa-icon {
            background: #fef3c7;
            color: #d97706;
        }

        .qa-audit .qa-icon {
            background: #ede9fe;
            color: #7c3aed;
        }

        .bot-row {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 16px;
            flex: 1;
            min-height: 0; /* Allow row to shrink and internal content to scroll */
        }

        .panel {
            background: var(--glass);
            backdrop-filter: blur(25px);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            animation: fadeInUp 0.8s ease forwards;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .panel-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .table-scroll-wrap {
            flex: 1;
            overflow-y: auto;
            padding-right: 4px;
        }

        .table-scroll-wrap::-webkit-scrollbar { width: 4px; }
        .table-scroll-wrap::-webkit-scrollbar-track { background: transparent; }
        .table-scroll-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        .panel-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 6px;
        }

        th {
            text-align: left;
            color: #94a3b8;
            font-size: 0.72rem;
            text-transform: uppercase;
            padding: 0 12px 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        td {
            padding: 8px 12px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--dark);
            background: rgba(255, 255, 255, 0.5);
            border-top: 1px solid rgba(255, 255, 255, 0.6);
            border-bottom: 1px solid rgba(255, 255, 255, 0.6);
        }

        td:first-child {
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
            border-left: 1px solid rgba(255, 255, 255, 0.6);
        }

        td:last-child {
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
            border-right: 1px solid rgba(255, 255, 255, 0.6);
        }

        tr:hover td {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        textarea {
            width: 100%;
            flex: 1;
            border: 2px solid transparent;
            resize: none;
            outline: none;
            font-family: inherit;
            font-size: 0.88rem;
            color: var(--dark);
            background: rgba(255, 255, 255, 0.5);
            line-height: 1.6;
            border-radius: 12px;
            padding: 12px;
            transition: 0.3s;
        }

        textarea:focus {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        @media (max-width: 1200px) {
            .stat-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .bot-row {
                grid-template-columns: 1fr;
            }

            .qa-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1200px) {
            .stat-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .stat-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stat-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .dash-banner {
                flex-direction: column;
                align-items: flex-start;
                padding: 20px;
                gap: 16px;
            }

            .banner-right {
                flex-wrap: wrap;
                gap: 10px;
            }

            .qa-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }

            .qa-btn {
                padding: 14px 8px;
                font-size: 0.72rem;
            }

            .qa-icon {
                width: 42px;
                height: 42px;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .stat-row {
                grid-template-columns: 1fr;
            }

            .qa-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endsection

@section('content')
    <div class="dash-wrap">

        <!-- HEADER BANNER -->
        <div class="dash-banner">
            <div>
                <h1>Good Day, Admin! 👋</h1>
                <p>Here's what's happening at SDO Quezon City today.</p>
            </div>
            <div class="banner-right">
                <div class="cal-widget">
                    <div class="cal-widget-header">{{ date('F') }}</div>
                    <div class="cal-widget-body">
                        <div class="cal-widget-day">{{ date('d') }}</div>
                        <div class="cal-widget-year">{{ date('Y') }}</div>
                    </div>
                </div>
                
                <div class="time-widget">
                    <div class="time-icon"><i class="fas fa-clock"></i></div>
                    <div class="time-text" id="clock">00:00:00</div>
                    <div style="display:flex;flex-direction:column;justify-content:center;margin-left:4px;">
                        <span id="ampm" style="font-size:0.6rem;font-weight:800;color:#94a3b8;line-height:1;">AM</span>
                        <span style="font-size:0.5rem;font-weight:700;color:#38bdf8;line-height:1;margin-top:2px;">PHT</span>
                    </div>
                </div>

                <div class="weather-widget">
                    <div class="weather-icon"><i class="fas fa-cloud-sun"></i></div>
                    <div style="display:flex;flex-direction:column;justify-content:center;">
                        <div class="weather-text" id="temp">--°C</div>
                        <div class="weather-sub">QUEZON CITY</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- STAT CARDS -->
        <div class="stat-row">
            <div class="stat-card">
                <div class="stat-card-icon" style="background:linear-gradient(135deg,#1e293b,#0f172a)"><i class="fas fa-users"></i></div>
                <div class="stat-card-content">
                    <div class="stat-card-label">Total Employees</div>
                    <div class="stat-card-num" data-target="{{ $totalEmp }}">0</div>
                    <div class="stat-card-trend" style="color:#64748b;"><i class="fas fa-building" style="font-size:0.7rem;"></i> All Personnel</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-id-badge"></i></div>
                <div class="stat-card-content">
                    <div class="stat-card-label">Active Employees</div>
                    <div class="stat-card-num" data-target="{{ $cntActive }}">0</div>
                    <div class="stat-card-trend" style="color:#10b981;"><i class="fas fa-arrow-up"></i> Currently serving</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)"><i class="fas fa-user-check"></i></div>
                <div class="stat-card-content">
                    <div class="stat-card-label">Present Today</div>
                    <div class="stat-card-num" data-target="{{ $stats['present'] ?? 0 }}">0</div>
                    <div class="stat-card-trend" style="color:#3b82f6;"><i class="fas fa-calendar-day"></i> {{ date('l') }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:linear-gradient(135deg,#64748b,#475569)"><i class="fas fa-user-slash"></i></div>
                <div class="stat-card-content">
                    <div class="stat-card-label">Inactive Employees</div>
                    <div class="stat-card-num" data-target="{{ $cntInactive }}">0</div>
                    <div class="stat-card-trend" style="color:#94a3b8;"><i class="fas fa-pause-circle"></i> Resigned / Terminated</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-user-clock"></i></div>
                <div class="stat-card-content">
                    <div class="stat-card-label">Other Status</div>
                    <div class="stat-card-num" data-target="{{ $cntOthers ?? 0 }}">0</div>
                    <div class="stat-card-trend" style="color:#d97706;"><i class="fas fa-plane-departure"></i> On leave / others</div>
                </div>
            </div>
        </div>



        <!-- BOTTOM ROW: Access Logs + Quick Notes -->
        <div class="bot-row">
            <div class="panel">
                <div class="panel-title">
                    <div class="panel-icon" style="background:#e0f2fe;color:var(--primary)"><i class="fas fa-history"></i>
                    </div>
                    System Access Logs
                </div>
                @if(count($logs) > 0)
                    <div class="table-scroll-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Login</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <div
                                                    style="width:28px;height:28px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:0.7rem;flex-shrink:0;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <span style="font-weight:700;">{{ $log->username }}</span>
                                            </div>
                                        </td>
                                        <td><span
                                                style="background:#e0f2fe;color:#0369a1;padding:3px 10px;border-radius:8px;font-size:0.7rem;font-weight:700;">{{ $log->role }}</span>
                                        </td>
                                        <td style="color:#64748b;font-size:0.82rem;">
                                            {{ date('h:i A', strtotime($log->login_time)) }}
                                        </td>
                                        <td>
                                            @if($log->logout_time)
                                                <span
                                                    style="color:#94a3b8;font-size:0.78rem;font-weight:700;">{{ date('h:i A', strtotime($log->logout_time)) }}</span>
                                            @else
                                                <span
                                                    style="display:inline-flex;align-items:center;gap:5px;color:#10b981;font-size:0.75rem;font-weight:800;">
                                                    <span
                                                        style="width:7px;height:7px;background:#10b981;border-radius:50%;animation:pulse 1.5s infinite;"></span>
                                                    ACTIVE
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align:center;padding:40px;color:#94a3b8;">
                        <i class="fas fa-ghost" style="font-size:2.5rem;margin-bottom:15px;opacity:0.3;"></i>
                        <p style="font-weight:600;">No recent activity found.</p>
                    </div>
                @endif
            </div>

            <div class="panel" style="display:flex;flex-direction:column;">
                <div class="panel-title">
                    <div class="panel-icon" style="background:#ffedd5;color:#ea580c"><i class="fas fa-sticky-note"></i>
                    </div>
                    Quick Notes
                    <span style="margin-left:auto;font-size:0.72rem;font-weight:600;color:#94a3b8;">Auto-saved</span>
                </div>
                <textarea id="quickNotes" placeholder="Jot down reminders, tasks, or notes here..."></textarea>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Live Clock
        function updateClock() {
            const now = new Date();
            let h = now.getHours();
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12; // the hour '0' should be '12'
            const hStr = String(h).padStart(2, '0');
            
            const clk = document.getElementById('clock');
            if (clk) clk.innerText = hStr + ':' + m + ':' + s;
            const amEl = document.getElementById('ampm');
            if (amEl) amEl.innerText = ampm;
        }
        updateClock(); setInterval(updateClock, 1000);

        // Weather
        fetch('https://api.open-meteo.com/v1/forecast?latitude=14.676&longitude=121.0437&current_weather=true')
            .then(res => res.json())
            .then(data => { document.getElementById('temp').innerText = Math.round(data.current_weather.temperature) + '°C'; })
            .catch(() => document.getElementById('temp').innerText = '--°C');

        // Notes Auto-save
        document.getElementById('quickNotes').value = localStorage.getItem('sdo_notes') || '';
        document.getElementById('quickNotes').addEventListener('input', e => localStorage.setItem('sdo_notes', e.target.value));

        // Counter Animation
        document.querySelectorAll('.stat-card-num[data-target]').forEach(counter => {
            const target = +counter.getAttribute('data-target');
            let count = 0;
            const step = Math.max(1, Math.ceil(target / 50));
            const tick = () => { count = Math.min(count + step, target); counter.innerText = count; if (count < target) setTimeout(tick, 20); };
            tick();
        });
    </script>
@endsection