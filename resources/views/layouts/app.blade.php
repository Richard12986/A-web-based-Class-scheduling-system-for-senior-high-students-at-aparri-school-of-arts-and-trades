<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Class Scheduling System' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f7fb;
            margin: 0;
        }

        .app-shell {
            min-height: 100vh;
        }

        .sidebar {
            width: 270px;
            min-height: 100vh;
            background: #111827;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            padding: 24px 18px;
            z-index: 1030;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 28px;
        }

        .sidebar-subtitle {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-top: 4px;
        }

        .nav-section-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin: 22px 0 10px;
        }

        .sidebar .nav-link {
            color: #d1d5db;
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            text-decoration: none;
            transition: 0.2s ease-in-out;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: #2563eb;
            color: #fff;
        }

        .main-content {
            margin-left: 270px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 28px;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .page-wrapper {
            padding: 28px;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: #111827;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .content-card {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .mobile-menu-btn {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            background: #fff;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #111827;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.45);
            z-index: 1025;
            display: none;
        }

        .sidebar-close-btn {
            display: none;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            align-items: center;
            justify-content: center;
        }

        .sidebar-top-mobile {
            display: none;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: 270px;
                min-height: 100vh;
            }

            .sidebar.sidebar-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 16px 18px;
            }

            .page-wrapper {
                padding: 18px;
            }

            .mobile-menu-btn {
                display: inline-flex;
            }

            .sidebar-overlay.show {
                display: block;
            }

            .sidebar-top-mobile {
                display: flex;
            }

            .sidebar-close-btn {
                display: inline-flex;
            }

            .page-title {
                font-size: 1.2rem;
            }

            .page-subtitle {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-shell">
        @include('partials.sidebar')

        <div class="main-content">
            @include('partials.topbar')

            <div class="page-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('appSidebar');
            const openBtn = document.getElementById('openSidebarBtn');
            const closeBtn = document.getElementById('closeSidebarBtn');
            const overlay = document.getElementById('sidebarOverlay');

            function openSidebar() {
                sidebar?.classList.add('sidebar-open');
                overlay?.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar?.classList.remove('sidebar-open');
                overlay?.classList.remove('show');
                document.body.style.overflow = '';
            }

            openBtn?.addEventListener('click', openSidebar);
            closeBtn?.addEventListener('click', closeSidebar);
            overlay?.addEventListener('click', closeSidebar);

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });
        });
    </script>

    @stack('scripts')
    @yield('modals')
</body>
</html>