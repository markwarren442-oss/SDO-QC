{{-- login.blade.php - Standalone login page (no layout) --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SDO QC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --bg-sky: #cffafe;
            --bg-floor: #f1f5f9;
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --glass: rgba(255, 255, 255, 0.85);
            --text: #1e293b;
            --input-bg: #fff;
            --subtitle: #64748b;
        }

        [data-theme="dark"] {
            --bg-sky: #0f172a;
            --bg-floor: #1e293b;
            --primary: #60a5fa;
            --primary-dark: #3b82f6;
            --glass: rgba(30, 41, 59, 0.85);
            --text: #f1f5f9;
            --input-bg: #334155;
            --subtitle: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to bottom, var(--bg-sky) 55%, var(--bg-floor) 55%);
            perspective: 1200px;
            transition: background 0.5s ease;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--glass);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 50%;
            cursor: pointer;
            color: var(--primary);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
        }

        .sky-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 55%;
            z-index: 0;
            overflow: hidden;
        }

        .sun {
            position: absolute;
            top: -80px;
            right: -80px;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, #fde68a, #fbbf24);
            border-radius: 50%;
            opacity: 0.8;
            filter: blur(30px);
            animation: pulseSun 6s infinite alternate;
            box-shadow: 0 0 80px #fcd34d;
            transition: 0.5s;
        }

        [data-theme="dark"] .sun {
            background: radial-gradient(circle, #334155, #1e293b);
            box-shadow: 0 0 40px rgba(255, 255, 255, 0.1);
        }

        .cloud {
            position: absolute;
            color: #fff;
            opacity: 0.9;
            animation: floatCloud linear infinite;
            filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.1));
            transition: 0.5s;
        }

        [data-theme="dark"] .cloud {
            opacity: 0.2;
            color: #94a3b8;
        }

        .c1 {
            font-size: 6rem;
            top: 10%;
            left: -15%;
            animation-duration: 55s;
        }

        .c2 {
            font-size: 9rem;
            top: 25%;
            left: -25%;
            animation-duration: 70s;
            animation-delay: -10s;
        }

        .c3 {
            font-size: 4rem;
            top: 5%;
            left: -10%;
            animation-duration: 40s;
            animation-delay: -25s;
        }

        .floating-icons {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .float-icon {
            position: absolute;
            color: rgba(59, 130, 246, 0.08);
            font-size: 2rem;
            animation: floatUp linear infinite;
        }

        .fi-1 {
            left: 10%;
            bottom: -50px;
            animation-duration: 15s;
            font-size: 3rem;
        }

        .fi-2 {
            left: 80%;
            bottom: -50px;
            animation-duration: 20s;
            animation-delay: 2s;
            font-size: 2.5rem;
        }

        .fi-3 {
            left: 30%;
            bottom: -50px;
            animation-duration: 18s;
            animation-delay: 5s;
            font-size: 1.5rem;
        }

        .fi-4 {
            left: 60%;
            bottom: -50px;
            animation-duration: 25s;
            animation-delay: 1s;
            font-size: 4rem;
        }

        .plane {
            position: absolute;
            font-size: 2.5rem;
            color: #fff;
            top: 15%;
            left: -10%;
            animation: flyPlane 25s linear infinite;
        }

        .balloon {
            position: absolute;
            font-size: 3.5rem;
            color: #ff7e7e;
            top: 30%;
            right: -10%;
            animation: flyBalloon 40s linear infinite;
            opacity: 0.9;
        }

        .bird {
            position: absolute;
            font-size: 1rem;
            color: #334155;
            animation: flyBird 10s linear infinite;
        }

        .bird-1 {
            top: 20%;
            left: -5%;
            animation-delay: 0s;
        }

        .bird-2 {
            top: 22%;
            left: -8%;
            animation-delay: 0.5s;
        }

        .city-skyline {
            position: absolute;
            bottom: 45%;
            width: 100%;
            text-align: center;
            color: #94a3b8;
            font-size: 7rem;
            white-space: nowrap;
            opacity: 0.4;
            z-index: 1;
            transform: scaleY(1.5);
            pointer-events: none;
            transition: 0.5s;
        }

        [data-theme="dark"] .city-skyline {
            color: #334155;
            opacity: 0.6;
        }

        .road-container {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 45%;
            z-index: 5;
            overflow: hidden;
            pointer-events: none;
        }

        .runner {
            position: absolute;
            bottom: 40px;
            left: -10%;
            font-size: 4rem;
            color: #475569;
            animation: runAcross 10s linear infinite;
        }

        .scooter {
            position: absolute;
            bottom: 90px;
            left: -20%;
            font-size: 5rem;
            color: var(--primary);
            animation: driveAcross 7s linear infinite;
            z-index: 6;
        }

        .bus {
            position: absolute;
            bottom: 150px;
            left: -30%;
            font-size: 8rem;
            color: #fbbf24;
            animation: driveAcross 18s linear infinite;
            animation-delay: 5s;
            z-index: 2;
            opacity: 0.8;
        }

        .worker {
            position: absolute;
            bottom: 30px;
            left: -15%;
            font-size: 3rem;
            color: #334155;
            animation: walkAcross 25s linear infinite;
            animation-delay: 3s;
        }

        @keyframes floatCloud {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(120vw);
            }
        }

        @keyframes floatUp {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }

            20% {
                opacity: 0.4;
            }

            80% {
                opacity: 0.4;
            }

            100% {
                transform: translateY(-120vh) rotate(360deg);
                opacity: 0;
            }
        }

        @keyframes flyPlane {
            0% {
                transform: translate(0, 0) rotate(10deg);
            }

            100% {
                transform: translate(120vw, -10vh) rotate(10deg);
            }
        }

        @keyframes flyBalloon {
            0% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(-60vw) translateY(-50px);
            }

            100% {
                transform: translateX(-120vw);
            }
        }

        @keyframes flyBird {
            0% {
                left: -5%;
                transform: translateY(0);
            }

            25% {
                transform: translateY(10px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                left: 110%;
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

        @keyframes pulseSun {
            0% {
                transform: scale(1);
            }

            100% {
                transform: scale(1.15);
            }
        }

        @keyframes cardEntrance {
            from {
                transform: translateY(100px) scale(0.8);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes confettiFall {
            0% {
                transform: translateY(-10vh) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translateY(110vh) rotate(720deg);
                opacity: 0;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes popUp {
            to {
                transform: scale(1);
            }
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-15px);
            }

            75% {
                transform: translateX(15px);
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

        .login-card-wrapper {
            position: relative;
            z-index: 100;
            width: 90%;
            max-width: 440px;
            transform-style: preserve-3d;
            transition: transform 0.1s;
            animation: cardEntrance 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .login-card {
            background: var(--glass);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 45px;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: background 0.5s;
        }

        .card-decoration {
            position: absolute;
            border-radius: 50%;
            z-index: -1;
            opacity: 0.3;
        }

        .cd-1 {
            width: 100px;
            height: 100px;
            background: #e0f2fe;
            top: -30px;
            left: -30px;
        }

        .cd-2 {
            width: 120px;
            height: 120px;
            background: #fef3c7;
            bottom: -40px;
            right: -40px;
        }

        .logo {
            height: 80px;
            margin-bottom: 20px;
            filter: drop-shadow(0 8px 15px rgba(0, 0, 0, 0.15));
            transition: transform 0.3s;
        }

        .logo:hover {
            transform: scale(1.1) rotate(5deg);
        }

        h1 {
            color: var(--text);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        p.subtitle {
            color: var(--subtitle);
            font-size: 0.95rem;
            margin-bottom: 35px;
            font-weight: 500;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
            transition: 0.3s;
        }

        .input-group:hover {
            transform: translateY(-2px);
        }

        .input-group input {
            width: 100%;
            padding: 18px 18px 18px 50px;
            border: 2px solid transparent;
            background: var(--input-bg);
            color: var(--text);
            border-radius: 16px;
            font-size: 1rem;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            font-family: inherit;
        }

        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
            transform: scale(1.02);
        }

        .input-group i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        .input-group input:focus+i {
            color: var(--primary);
        }

        .input-group .icon {
            left: 20px;
        }

        .input-group .toggle {
            right: 20px;
            cursor: pointer;
            z-index: 10;
            padding: 5px;
        }

        .input-group .toggle:hover {
            color: var(--primary);
            transform: translateY(-50%) scale(1.2);
        }

        .remember-group {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 25px;
            color: var(--subtitle);
            font-size: 0.9rem;
            font-weight: 600;
            padding-left: 8px;
        }

        .remember-group input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember-group label {
            cursor: pointer;
            user-select: none;
            transition: 0.2s;
        }

        .remember-group:hover label {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 18px;
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            margin-top: 5px;
            transition: 0.3s;
            box-shadow: 0 14px 28px rgba(59, 130, 246, 0.45), 0 4px 8px rgba(59, 130, 246, 0.25);
            position: relative;
            overflow: hidden;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 22px 44px rgba(59, 130, 246, 0.55), 0 6px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-login:hover::after {
            left: 100%;
        }

        .spinner {
            display: none;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        .confetti-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 3000;
            overflow: hidden;
            display: none;
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #f00;
            animation: confettiFall 3s linear forwards;
        }

        .success-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease-out;
        }

        .success-card {
            background: var(--glass);
            padding: 50px;
            width: 90%;
            max-width: 450px;
            border-radius: 40px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.5);
            animation: popUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            position: relative;
            overflow: hidden;
            color: var(--text);
        }

        .check-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #34d399, #10b981);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px auto;
            font-size: 3rem;
            animation: bounce 1s infinite;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }

        .success-card h2 {
            color: #10b981;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .error-block {
            display: none;
            background: #fef2f2;
            border: 1.5px solid #fca5a5;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 16px;
            color: #dc2626;
            font-size: 0.875rem;
            font-weight: 700;
            text-align: left;
            animation: fadeInUp 0.3s ease;
            align-items: center;
            gap: 10px;
        }

        .error-block.visible {
            display: flex;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: var(--glass);
            color: var(--text);
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 6px solid #ef4444;
            transform: translateX(150%);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            min-width: 300px;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast i {
            color: #ef4444;
            font-size: 1.5rem;
        }

        .t-body h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .t-body p {
            font-size: 0.85rem;
            color: var(--subtitle);
        }
    </style>
</head>

<body>
    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <div class="sky-layer">
        <div class="sun"></div>
        <i class="fas fa-cloud cloud c1"></i>
        <i class="fas fa-cloud cloud c2"></i>
        <i class="fas fa-cloud cloud c3"></i>
        <i class="fas fa-paper-plane plane"></i>
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

    <div class="toast-container" id="toastContainer"></div>
    <div class="confetti-container" id="confettiBox"></div>

    <div class="success-overlay" id="successModal">
        <div class="success-card">
            <div class="check-icon"><i class="fas fa-check"></i></div>
            <h2>Access Granted!</h2>
            <p>Welcome back to SDO Quezon City.</p>
            <p style="font-size:0.9rem; margin-top:15px; color:#10b981; font-weight:700;">
                <i class="fas fa-spinner fa-spin"></i> Entering Dashboard...
            </p>
        </div>
    </div>

    <div class="login-card-wrapper" id="tiltCard">
        <div class="login-card">
            <div class="card-decoration cd-1"></div>
            <div class="card-decoration cd-2"></div>

            <img src="{{ asset('logo.png') }}" alt="Logo" class="logo"
                onerror="this.src='https://ui-avatars.com/api/?name=SDO&background=3b82f6&color=fff&rounded=true&bold=true'">
            <h1>SDO Quezon City</h1>
            <p class="subtitle">Personnel Management System</p>

            <form id="loginForm">
                @csrf
                <input type="hidden" name="ajax_login" value="1">

                <div class="error-block" id="errorBlock">
                    <i class="fas fa-circle-exclamation" style="font-size:1.1rem;flex-shrink:0;"></i>
                    <span id="errorMsg">Invalid credentials.</span>
                </div>

                <div class="input-group">
                    <label
                        style="display:block;font-size:0.75rem;font-weight:800;color:var(--subtitle);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px;">Username</label>
                    <i class="fas fa-user icon" style="top:calc(50% + 11px);"></i>
                    <input type="text" name="username" placeholder="e.g. admin" required autocomplete="off">
                </div>

                <div class="input-group">
                    <label
                        style="display:block;font-size:0.75rem;font-weight:800;color:var(--subtitle);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px;">Password</label>
                    <i class="fas fa-lock icon" style="top:calc(50% + 11px);"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-eye toggle" id="eyeIcon" style="top:calc(50% + 11px);"></i>
                </div>

                <div class="remember-group">
                    <input type="checkbox" id="remember" name="remember_me">
                    <label for="remember">Remember me for 30 days</label>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span id="btnText">Sign In <i class="fas fa-arrow-right" style="margin-left:8px;"></i></span>
                    <div class="spinner" id="btnSpinner"></div>
                </button>
            </form>

            <div style="margin-top:25px; font-size:0.8rem; color:var(--subtitle); font-weight:600;">
                &copy; {{ date('Y') }} HR-NON TEACHING
            </div>
        </div>
    </div>

    <script>
        // Dark Mode
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const savedTheme = localStorage.getItem('sdo_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeUI(savedTheme);
        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('sdo_theme', next);
            updateThemeUI(next);
        });
        function updateThemeUI(theme) {
            if (theme === 'dark') themeIcon.classList.replace('fa-moon', 'fa-sun');
            else themeIcon.classList.replace('fa-sun', 'fa-moon');
        }

        // 3D Tilt
        const card = document.getElementById('tiltCard');
        document.addEventListener('mousemove', (e) => {
            const x = (window.innerWidth / 2 - e.pageX) / 30;
            const y = (window.innerHeight / 2 - e.pageY) / 30;
            card.style.transform = `rotateY(${x}deg) rotateX(${y}deg)`;
        });

        // Password Toggle
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        eyeIcon.addEventListener('click', () => {
            const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passInput.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });

        // Login Logic
        const form = document.getElementById('loginForm');
        const btn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const spinner = document.getElementById('btnSpinner');
        const successModal = document.getElementById('successModal');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            btn.disabled = true;
            btnText.style.display = 'none';
            spinner.style.display = 'block';

            const formData = new FormData(form);
            try {
                const req = await fetch('{{ route("login.post") }}', { method: 'POST', body: formData });
                const res = await req.json();

                if (res.success) {
                    fireConfetti();
                    successModal.style.display = 'flex';
                    setTimeout(() => { window.location.href = res.redirect; }, 2500);
                } else {
                    const errBlock = document.getElementById('errorBlock');
                    document.getElementById('errorMsg').textContent = res.message;
                    errBlock.classList.add('visible');
                    showErrorToast('Login Failed', res.message);
                    resetButton();
                    document.querySelector('.login-card').style.animation = 'none';
                    document.querySelector('.login-card').offsetHeight;
                    document.querySelector('.login-card').style.animation = 'shake 0.4s ease-in-out';
                }
            } catch (err) {
                showErrorToast('System Error', 'Connection failed.');
                resetButton();
            }
        });

        function resetButton() { btn.disabled = false; btnText.style.display = 'block'; spinner.style.display = 'none'; }

        function showErrorToast(title, msg) {
            const container = document.getElementById('toastContainer');
            const div = document.createElement('div');
            div.className = 'toast';
            div.innerHTML = `<i class="fas fa-exclamation-triangle"></i><div class="t-body"><h4>${title}</h4><p>${msg}</p></div>`;
            container.appendChild(div);
            requestAnimationFrame(() => div.classList.add('show'));
            setTimeout(() => { div.classList.remove('show'); setTimeout(() => div.remove(), 400); }, 3000);
        }

        function fireConfetti() {
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
            const box = document.getElementById('confettiBox');
            box.style.display = 'block';
            for (let i = 0; i < 100; i++) {
                const conf = document.createElement('div');
                conf.className = 'confetti';
                conf.style.left = Math.random() * 100 + 'vw';
                conf.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                conf.style.animationDuration = (Math.random() * 2 + 2) + 's';
                box.appendChild(conf);
            }
        }
    </script>
</body>

</html>