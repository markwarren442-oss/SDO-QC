@extends('layouts.app')

@section('title', 'Audit Logs | SDO QC')

@section('styles')
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --bg-body: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #f1f5f9;
            --shadow-sm: 0 4px 15px rgba(0, 0, 0, 0.03);
            --shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .audit-page {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 0 10px;
            width: 100%;
            height: calc(130vh - 40px);
            overflow: hidden;
        }

        .sticky-top-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex-shrink: 0;
            background: rgba(224, 242, 254, 0.5); /* Matching some of the layout.app background for seamless stickiness */
            backdrop-filter: blur(5px);
            padding-bottom: 10px;
            z-index: 10;
        }

        /* ── Header ── */
        .audit-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 10px;
            margin-bottom: 5px;
        }

        .audit-title h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .audit-title h1 i {
            color: var(--primary);
        }

        .audit-title p {
            color: #94a3b8;
            font-size: 0.85rem;
            margin: 4px 0 0;
            font-weight: 500;
        }

        .total-records {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #94a3b8;
        }

        .total-records i {
            color: #cbd5e1;
        }

        /* ── Stats Grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .stat-sub {
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 150px;
            margin-top: 2px;
        }

        /* Varient Colors */
        .ic-blue {
            background: #eff6ff;
            color: #3b82f6;
        }

        .ic-green {
            background: #f0fdf4;
            color: #22c55e;
        }

        .ic-purple {
            background: #f5f3ff;
            color: #a855f7;
        }

        .ic-amber {
            background: #fffbeb;
            color: #f59e0b;
        }

        /* ── Filter Bar ── */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 12px 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            margin-right: 4px;
        }

        .filter-input {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s;
            min-width: 140px;
        }

        .filter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-apply {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-apply:hover {
            background: var(--primary-dark);
        }

        .btn-clear {
            background: #64748b;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-clear:hover {
            background: #475569;
        }

        /* ── Table ── */
        .logs-card {
            background: white;
            border-radius: 16px;
            padding: 0;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0; /* Important for flex child to be scrollable */
        }

        .scrollable-table-container {
            flex: 1;
            overflow-y: auto;
            position: relative;
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

        .logs-table thead {
            position: sticky;
            top: 0;
            z-index: 5;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logs-table th {
            text-align: left;
            padding: 16px 24px;
            font-size: 0.65rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            letter-spacing: 0.5px;
        }

        .logs-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .logs-table tr:last-child td {
            border-bottom: none;
        }

        .logs-table tr:hover td {
            background: #f8fafc;
        }

        /* User Avatar */
        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #1e293b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .user-name {
            font-weight: 800;
            color: #0f172a;
            font-size: 0.85rem;
        }

        /* Badges */
        .badge-base {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        .badge-action {
            background: #f5f3ff;
            color: #a855f7;
        }

        .badge-action.update {
            background: #eff6ff;
            color: #3b82f6;
        }

        .badge-module {
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.65rem;
            border-radius: 12px;
            padding: 4px 12px;
        }

        .mod-attendance {
            background: #eff6ff;
            color: #3b82f6;
        }

        .mod-employee {
            background: #ecfdf5;
            color: #10b981;
        }

        .mod-system {
            background: #f1f5f9;
            color: #64748b;
        }

        .time-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .time-main {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
        }

        .time-rel {
            font-size: 0.7rem;
            font-weight: 600;
            color: #94a3b8;
        }

        .details-cell {
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
            line-height: 1.4;
        }

        .ip-cell {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            font-family: monospace;
        }

        /* Pagination */
        .pagination-bar {
            padding: 16px 20px;
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .pag-link {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: white;
            border: 1px solid #e2e8f0;
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s;
        }

        .pag-link:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .pag-link.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade {
            animation: fadeInUp 0.4s ease forwards;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .audit-page {
                padding: 0 4px;
            }

            .filter-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-input {
                width: 100%;
                min-width: 0;
            }

            .logs-card>div {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .logs-table {
                min-width: 700px;
            }

            .logs-table td,
            .logs-table th {
                padding: 12px 14px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="audit-page">

        <div class="sticky-top-section">
            {{-- Header Area --}}
            <div class="audit-header animate-fade">
                <div class="audit-title">
                    <h1><i class="fas fa-clock-rotate-left"></i> Audit Logs</h1>
                    <p>Track all changes and activities across the system</p>
                </div>
                <div class="total-records">
                    <i class="fas fa-database"></i>
                    {{ number_format($totalCount) }} total records
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="stats-grid animate-fade" style="animation-delay: 0.05s;">
                {{-- Today --}}
                <div class="stat-card">
                    <div class="stat-icon ic-blue">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Today</span>
                        <span class="stat-value">{{ $todayCount }}</span>
                    </div>
                </div>

                {{-- This Week --}}
                <div class="stat-card">
                    <div class="stat-icon ic-green">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">This Week</span>
                        <span class="stat-value">{{ $weekCount }}</span>
                    </div>
                </div>

                {{-- Active Users --}}
                <div class="stat-card">
                    <div class="stat-icon ic-purple">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Active Users</span>
                        <span class="stat-value">{{ $activeUsersCount }}</span>
                    </div>
                </div>

                {{-- Last Activity --}}
                <div class="stat-card">
                    <div class="stat-icon ic-amber">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Last Activity</span>
                        <span class="stat-sub" title="{{ $lastActivity }}">{{ $lastActivity }}</span>
                    </div>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="filter-section animate-fade" style="animation-delay: 0.1s;">
                <form method="GET" action="{{ route('admin.audit') }}" class="filter-form">
                    <div class="filter-label"><i class="fas fa-filter"></i> Filters:</div>

                    <select name="module" class="filter-input" style="min-width: 160px;">
                        <option value="">All Modules</option>
                        @foreach($moduleList as $m)
                            <option value="{{ $m }}" {{ $filterModule === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="user" class="filter-input" placeholder="Username..." value="{{ $filterUser }}">

                    <input type="date" name="date" class="filter-input" value="{{ $filterDate }}">

                    <button type="submit" class="btn-apply">
                        <i class="fas fa-search"></i> Apply
                    </button>

                    @if($filterModule || $filterUser || $filterDate)
                        <a href="{{ route('admin.audit') }}" class="btn-clear">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="logs-card animate-fade" style="animation-delay: 0.15s;">
            <div class="scrollable-table-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $userInitial = strtoupper(substr($log->username ?? 'U', 0, 1));
                                $relTime = \Carbon\Carbon::parse($log->created_at)->diffForHumans();

                                $modClass = match (strtolower($log->module)) {
                                    'attendance' => 'mod-attendance',
                                    'employee' => 'mod-employee',
                                    'holiday' => 'mod-holiday',
                                    'system' => 'mod-system',
                                    default => ''
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="time-cell">
                                        <span class="time-main">{{ date('M d, Y', strtotime($log->created_at)) }}</span>
                                        <span class="time-rel">{{ date('h:i A', strtotime($log->created_at)) }} •
                                            {{ $relTime }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">
                                            {{ $userInitial }}
                                        </div>
                                        <span class="user-name">{{ $log->username }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge-base badge-action {{ str_contains(strtolower($log->action), 'update') ? 'update' : '' }}">{{ $log->action }}</span>
                                </td>
                                <td>
                                    <span class="badge-base badge-module {{ $modClass }}">{{ $log->module }}</span>
                                </td>
                                <td>
                                    <div class="details-cell">{{ $log->details }}</div>
                                </td>
                                <td class="ip-cell">
                                    {{ $log->ip_address }}
                                </td>
                            </tr>
                        @endforeach
                        @if(count($logs) === 0)
                            <tr>
                                <td colspan="6" style="text-align:center;padding:100px 0;color:#94a3b8;">
                                    <i class="fas fa-inbox"
                                        style="font-size:3rem;display:block;margin-bottom:16px;opacity:0.2;"></i>
                                    <div style="font-weight:700;font-size:1.1rem;opacity:0.5;">No activity logs found</div>
                                    <p style="font-size:0.85rem;margin-top:4px;">Try adjusting your filters or search terms</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($totalPages > 1)
                <div class="pagination-bar">
                    {{-- Previous Arrow --}}
                    @if($page > 1)
                        <a href="{{ route('admin.audit', array_merge(request()->query(), ['page' => $page - 1])) }}" class="pag-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @else
                        <span class="pag-link disabled" style="opacity: 0.5; cursor: not-allowed;"><i class="fas fa-chevron-left"></i></span>
                    @endif

                    {{-- Page Numbers (Max 5 shown in a window) --}}
                    @php
                        $start = max(1, min($page - 2, $totalPages - 4));
                        $end = min($totalPages, $start + 4);
                        if ($totalPages <= 5) {
                            $start = 1;
                            $end = $totalPages;
                        }
                    @endphp

                    @for($p = $start; $p <= $end; $p++)
                        @if($p == $page)
                            <span class="pag-link active">{{ $p }}</span>
                        @else
                            <a href="{{ route('admin.audit', array_merge(request()->query(), ['page' => $p])) }}"
                                class="pag-link">{{ $p }}</a>
                        @endif
                    @endfor

                    {{-- Next Arrow --}}
                    @if($page < $totalPages)
                        <a href="{{ route('admin.audit', array_merge(request()->query(), ['page' => $page + 1])) }}" class="pag-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="pag-link disabled" style="opacity: 0.5; cursor: not-allowed;"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            @endif
        </div>

    </div>
@endsection