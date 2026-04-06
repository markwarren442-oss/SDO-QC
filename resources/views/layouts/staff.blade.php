{{-- Staff layout - separate from admin (no sidebar, card-based header) --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Personnel Directory | SDO QC')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
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
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 20px 50px -12px rgba(0, 0, 0, 0.12);
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f4ff;
            background-image:
                radial-gradient(ellipse at 10% 20%, rgba(147, 197, 253, 0.5) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 80%, rgba(196, 181, 253, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(251, 207, 232, 0.2) 0%, transparent 70%);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--dark);
            min-height: 100vh;
            overflow-x: hidden;
            padding: 0;
            margin: 0;
        }

        /* Scene layer animations */
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
            color: rgba(255, 255, 255, 0.9);
            animation: floatCloud linear infinite;
            font-size: 5rem;
        }

        .c1 {
            top: 8%;
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
            right: 50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, #fde68a, #fcd34d);
            border-radius: 50%;
            opacity: 0.5;
            filter: blur(40px);
            animation: pulseSun 5s infinite alternate;
        }

        @keyframes floatCloud {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(110vw);
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

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
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

        /* Custom Overlay for logout */
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
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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
    </style>

    @yield('styles')
</head>

<body>
    <!-- Scene Layer -->
    <div class="scene-layer">
        <div class="sun"></div>
        <i class="fas fa-cloud cloud c1"></i>
        <i class="fas fa-cloud cloud c2"></i>
        <i class="fas fa-cloud cloud c3"></i>
    </div>

    @yield('content')

    <!-- LOGOUT MODAL -->
    <div id="logoutModal" class="custom-overlay">
        <div class="custom-box">
            <div
                style="width:60px;height:60px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 15px auto;">
                <i class="fas fa-sign-out-alt" style="font-size:1.5rem;color:#ef4444;"></i>
            </div>
            <h2 style="margin-bottom:10px;">Log Out?</h2>
            <p style="color:#64748b;margin-bottom:25px;">Are you sure you want to end your session?</p>
            <div style="display:flex;justify-content:center;gap:10px;">
                <button class="modal-btn" style="background:#f1f5f9;color:#475569;"
                    onclick="closeLogout()">Cancel</button>
                <a href="{{ route('logout') }}" class="modal-btn" style="background:#ef4444;color:white;">Log Out</a>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout(e) { e.preventDefault(); document.getElementById('logoutModal').style.display = 'flex'; }
        function closeLogout() { document.getElementById('logoutModal').style.display = 'none'; }
    </script>

    @yield('scripts')
</body>

</html>