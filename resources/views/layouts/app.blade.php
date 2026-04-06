<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SDO QC')</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* --- CORE THEME STYLES --- */
        :root {
            --bg-sky: #e0f2fe;
            --bg-floor: #f1f5f9;
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --dark: #1e293b;
            --glass: rgba(255, 255, 255, 0.85);
            --glass-strong: rgba(255, 255, 255, 0.95);
            --white: #ffffff;
            --accent-green: #10b981;
            --accent-amber: #f59e0b;
            --accent-red: #ef4444;
            --accent-violet: #8b5cf6;
            --sidebar-width: 260px;
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 20px 50px -12px rgba(0, 0, 0, 0.12);
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 10px;
            --border: rgba(255, 255, 255, 0.6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(180deg, var(--bg-sky) 0%, var(--bg-floor) 100%);
            background-attachment: fixed;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--dark);
            padding: 20px;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            transition: all 0.3s ease;
        }

        /* --- SCENE ANIMATIONS --- */
        .scene-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }

        .cloud {
            position: absolute;
            color: #fff;
            opacity: 0.8;
            animation: floatCloud linear infinite;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.05));
        }

        .c1 {
            font-size: 5rem;
            top: 10%;
            left: -10%;
            animation-duration: 45s;
        }

        .c2 {
            font-size: 8rem;
            top: 20%;
            left: -20%;
            animation-duration: 60s;
            animation-delay: -10s;
        }

        .c3 {
            font-size: 4rem;
            top: 5%;
            left: -5%;
            animation-duration: 35s;
            animation-delay: -20s;
        }

        .sun {
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, #fde68a, #fcd34d);
            border-radius: 50%;
            opacity: 0.5;
            filter: blur(40px);
            animation: pulseSun 5s infinite alternate;
        }

        .paper-plane {
            position: absolute;
            font-size: 2rem;
            color: #fff;
            top: 30%;
            left: -10%;
            animation: flyPlane 15s linear infinite;
            opacity: 0.9;
        }

        .city-skyline {
            position: absolute;
            top: 60%;
            left: 0;
            width: 100%;
            text-align: center;
            color: #cbd5e1;
            font-size: 6rem;
            white-space: nowrap;
            opacity: 0.6;
            transform: translateY(-100%) scaleY(1.2);
            z-index: -1;
        }

        .runner {
            position: absolute;
            top: 62%;
            left: -10%;
            font-size: 3.5rem;
            color: #475569;
            animation: runAcross 12s linear infinite, bob 0.6s ease-in-out infinite;
        }

        .scooter {
            position: absolute;
            top: 65%;
            left: -20%;
            font-size: 4rem;
            color: var(--primary);
            animation: driveAcross 8s linear infinite;
        }

        .worker {
            position: absolute;
            top: 63%;
            left: -10%;
            font-size: 3rem;
            color: #334155;
            animation: walkAcross 25s linear infinite, bob 0.8s ease-in-out infinite;
            animation-delay: 2s;
        }

        @keyframes floatCloud {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(110vw);
            }
        }

        @keyframes flyPlane {
            0% {
                transform: translate(0, 0) rotate(10deg);
            }

            25% {
                transform: translate(30vw, -10vh) rotate(5deg);
            }

            50% {
                transform: translate(60vw, 5vh) rotate(15deg);
            }

            100% {
                transform: translate(110vw, -20vh) rotate(10deg);
            }
        }

        @keyframes runAcross {
            0% {
                left: -10%;
            }

            100% {
                left: 110%;
            }
        }

        @keyframes driveAcross {
            0% {
                left: -20%;
            }

            100% {
                left: 120%;
            }
        }

        @keyframes walkAcross {
            0% {
                left: -10%;
            }

            100% {
                left: 110%;
            }
        }

        @keyframes bob {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes pulseSun {
            0% {
                transform: scale(1);
            }

            100% {
                transform: scale(1.1);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatIcon {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-5px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 24px;
            height: calc(100vh - 40px);
            position: sticky;
            top: 20px;
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-lg);
            z-index: 100;
            flex-shrink: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 800;
            font-size: 1.25rem;
        }

        .logo-area img {
            width: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .toggle-btn {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .toggle-btn:hover {
            background: var(--primary);
            color: white;
            transform: rotate(180deg);
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 5px;
            scrollbar-width: none; /* Hide for Firefox */
            -ms-overflow-style: none; /* Hide for IE/Edge */
        }

        .nav-links::-webkit-scrollbar {
            display: none; /* Hide for Chrome/Safari */
        }

        .nav-link {
            padding: 14px 16px;
            border-radius: 14px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            padding-left: 20px;
        }

        .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.5);
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
        }

        .user-profile {
            background: rgba(0, 0, 0, 0.2);
            padding: 16px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: auto;
            border: 1px solid rgba(255, 255, 255, 0.05);
            flex-shrink: 0;
        }

        /* Collapsed Sidebar */
        body.collapsed {
            --sidebar-width: 90px;
        }

        body.collapsed .sidebar {
            padding: 24px 12px;
        }

        body.collapsed .logo-text,
        body.collapsed .link-text,
        body.collapsed .user-info {
            display: none;
        }

        body.collapsed .sidebar-header {
            justify-content: center;
        }

        body.collapsed .logo-area {
            display: none;
        }

        body.collapsed .nav-link {
            justify-content: center;
            padding: 14px 0;
        }

        body.collapsed .nav-link:hover {
            padding-left: 0;
        }

        body.collapsed .user-profile {
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 16px 8px;
        }

        body.collapsed .logout-btn {
            margin-left: 0 !important;
            padding: 8px 0 !important;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 30px;
            width: 100%;
            transition: margin 0.3s ease;
        }

        /* --- LOGOUT MODAL --- */
        .custom-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(8px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .custom-box {
            background: white;
            padding: 40px;
            border-radius: 28px;
            width: 90%;
            max-width: 1200px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            animation: fadeInUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .modal-btn {
            padding: 0 24px;
            height: 45px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
        }

        /* ── Mobile Top Navbar ── */
        .mobile-topbar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            z-index: 1040;
            background: rgba(30, 41, 59, 0.97);
            backdrop-filter: blur(20px);
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        }

        .mobile-topbar .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .mobile-topbar .logo-area img {
            width: 34px;
            border-radius: 8px;
        }

        .mobile-ham-btn {
            background: rgba(255, 255, 255, 0.08);
            color: #94a3b8;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: 0.2s;
        }

        .mobile-ham-btn:hover {
            background: var(--primary);
            color: white;
        }

        /* ── Offcanvas Sidebar (Mobile) ── */
        .offcanvas-sidebar {
            width: 270px !important;
            background: rgba(30, 41, 59, 0.98) !important;
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        .offcanvas-sidebar .offcanvas-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .offcanvas-sidebar .offcanvas-title {
            color: white;
            font-weight: 800;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .offcanvas-sidebar .offcanvas-title img {
            width: 32px;
            border-radius: 8px;
        }

        .offcanvas-sidebar .btn-close {
            filter: invert(1) brightness(0.7);
        }

        .offcanvas-sidebar .offcanvas-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .offcanvas-nav-link {
            padding: 13px 16px;
            border-radius: 14px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .offcanvas-nav-link i {
            width: 22px;
            text-align: center;
        }

        .offcanvas-nav-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: white;
        }

        .offcanvas-nav-link.active {
            background: var(--primary);
            color: white;
        }

        .offcanvas-user-profile {
            background: rgba(0, 0, 0, 0.2);
            padding: 14px 16px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: auto;
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: white;
        }

        /* ── Responsive Breakpoints ── */
        @media (max-width: 768px) {
            body {
                padding: 10px;
                padding-top: 72px;
            }

            .sidebar {
                display: none !important;
            }

            .mobile-topbar {
                display: flex !important;
            }

            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }

            /* General table responsiveness */
            .table-responsive-mobile {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Stack flex rows on mobile */
            .mobile-stack {
                flex-direction: column !important;
                gap: 10px !important;
            }

            /* Modals full-width on mobile */
            .custom-box {
                width: 95% !important;
                padding: 24px !important;
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    <!-- Scene Layer (Animations) -->
    <div class="scene-layer">
        <div class="sun"></div>
        <div class="city-skyline"><i class="fas fa-building"></i> <i class="fas fa-city"></i> <i
                class="fas fa-landmark"></i></div>
        <i class="fas fa-person-running runner"></i>
        <i class="fas fa-motorcycle scooter"></i>
    </div>

    <!-- Mobile Top Navbar (visible only on small screens) -->
    <div class="mobile-topbar" id="mobileTopbar">
        <div class="logo-area">
            <img src="{{ asset('logo.png') }}" alt="Logo"
                onerror="this.src='https://ui-avatars.com/api/?name=SDO+QC&background=3b82f6&color=fff'">
            <span>SDO QC</span>
        </div>
        <button class="mobile-ham-btn" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"
            aria-controls="mobileSidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header">
            <span class="offcanvas-title">
                <img src="{{ asset('logo.png') }}" alt="Logo"
                    onerror="this.src='https://ui-avatars.com/api/?name=SDO+QC&background=3b82f6&color=fff'">
                SDO QC
            </span>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <a href="{{ route('admin.dashboard') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i
                    class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="{{ route('admin.index') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}"><i
                    class="fas fa-users"></i> Employees</a>
            <a href="{{ route('admin.absent') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.absent') ? 'active' : '' }}"><i
                    class="fas fa-user-xmark"></i> Absences</a>
            <a href="{{ route('admin.calendar') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.calendar') ? 'active' : '' }}"><i
                    class="fas fa-calendar-alt"></i> Attendance</a>
            <a href="{{ route('admin.forms') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.forms') ? 'active' : '' }}"><i
                    class="fas fa-folder-open"></i> Forms</a>
            <a href="{{ route('admin.audit') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.audit') ? 'active' : '' }}"><i
                    class="fas fa-clock-rotate-left"></i> Audit Logs</a>
            <a href="{{ route('admin.profile') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}"><i
                    class="fas fa-cog"></i> Profile</a>
            <a href="{{ route('admin.reports') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}"><i
                    class="fas fa-chart-line"></i> Reports</a>
            <a href="{{ route('admin.manual') }}"
                class="offcanvas-nav-link {{ request()->routeIs('admin.manual') ? 'active' : '' }}"><i
                    class="fas fa-book-open"></i> User Manual</a>

            <div class="offcanvas-user-profile" style="margin-top: auto;">
                @if(session('user_photo'))
                    <img src="{{ asset(session('user_photo')) }}"
                        style="width:38px;height:38px;border-radius:10px;object-fit:cover;flex-shrink:0;">
                @else
                    <div
                        style="width:38px;height:38px;background:linear-gradient(135deg,#475569,#1e293b);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;flex-shrink:0;">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                <div style="font-size:0.82rem;">
                    <div style="font-weight:700;">{{ session('username', 'Admin') }}</div>
                    <div style="color:#94a3b8;font-size:0.72rem;">{{ session('role', 'Online') }}</div>
                </div>
                <a href="{{ route('logout') }}" onclick="confirmLogout(event)"
                    style="color:#ef4444;margin-left:auto;padding:6px;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-area">
                <img src="{{ asset('logo.png') }}" alt="Logo"
                    onerror="this.src='https://ui-avatars.com/api/?name=SDO+QC&background=3b82f6&color=fff'">
                <span class="logo-text">SDO QC</span>
            </div>
            <button class="toggle-btn" id="toggleBtn"><i class="fas fa-bars"></i></button>
        </div>
        <div class="nav-links">
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> <span class="link-text">Dashboard</span></a>
            <a href="{{ route('admin.index') }}"
                class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                <i class="fas fa-users"></i> <span class="link-text">Employees</span></a>
            <a href="{{ route('admin.absent') }}"
                class="nav-link {{ request()->routeIs('admin.absent') ? 'active' : '' }}">
                <i class="fas fa-user-xmark"></i> <span class="link-text">Absences</span></a>
            <a href="{{ route('admin.calendar') }}"
                class="nav-link {{ request()->routeIs('admin.calendar') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> <span class="link-text">Attendance</span></a>
            <a href="{{ route('admin.forms') }}"
                class="nav-link {{ request()->routeIs('admin.forms') ? 'active' : '' }}">
                <i class="fas fa-folder-open"></i> <span class="link-text">Forms</span></a>
            <a href="{{ route('admin.audit') }}"
                class="nav-link {{ request()->routeIs('admin.audit') ? 'active' : '' }}">
                <i class="fas fa-clock-rotate-left"></i> <span class="link-text">Audit Logs</span></a>
            <a href="{{ route('admin.profile') }}"
                class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> <span class="link-text">Profile</span></a>
            <a href="{{ route('admin.reports') }}"
                class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> <span class="link-text">Reports</span></a>
            <a href="{{ route('admin.manual') }}"
                class="nav-link {{ request()->routeIs('admin.manual') ? 'active' : '' }}">
                <i class="fas fa-book-open"></i> <span class="link-text">User Manual</span></a>
        </div>
        <div class="user-profile">
            @if(session('user_photo'))
                <img src="{{ asset(session('user_photo')) }}"
                    style="width:40px;height:40px;border-radius:12px;object-fit:cover;flex-shrink:0;box-shadow:0 5px 10px rgba(0,0,0,0.2);">
            @else
                <div
                    style="width:40px;height:40px;background:linear-gradient(135deg,#475569,#1e293b);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;flex-shrink:0;box-shadow:0 5px 10px rgba(0,0,0,0.2);">
                    <i class="fas fa-user"></i>
                </div>
            @endif
            <div class="user-info" style="font-size:0.85rem;color:white;">
                <div style="font-weight:700;">{{ session('username', 'Admin') }}</div>
                <div style="color:#94a3b8;font-size:0.75rem;">{{ session('role', 'Online') }}</div>
            </div>
            <a href="{{ route('logout') }}" class="logout-btn" onclick="confirmLogout(event)"
                style="color:#ef4444; margin-left:auto; padding:8px;"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- LOGOUT MODAL -->
    <div id="logoutModal" class="custom-overlay">
        <div class="custom-box" style="max-width: 400px;">
            <div
                style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                <i class="fas fa-sign-out-alt" style="font-size: 1.5rem; color: #ef4444;"></i>
            </div>
            <h2 style="margin-bottom:10px;">Log Out?</h2>
            <p style="color:#64748b; margin-bottom:25px;">Are you sure you want to end your session?</p>
            <div style="display:flex; justify-content:center; gap:10px;">
                <button class="modal-btn" style="background:#f1f5f9; color:#475569;"
                    onclick="closeLogout()">Cancel</button>
                <a href="{{ route('logout') }}" class="modal-btn" style="background:#ef4444; color:white;">Log Out</a>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle
        const toggleBtn = document.getElementById('toggleBtn');
        if (localStorage.getItem('sidebar-collapsed') === 'true') document.body.classList.add('collapsed');
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', document.body.classList.contains('collapsed'));
        });

        // Logout Modal
        function confirmLogout(e) {
            e.preventDefault();
            document.getElementById('logoutModal').style.display = 'flex';
        }
        function closeLogout() {
            document.getElementById('logoutModal').style.display = 'none';
        }
    </script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>