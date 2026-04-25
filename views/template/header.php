<?php
/** Kerangka atas: navbar + area utama + tempat pesan flash. */
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Tiket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f8fafc; 
            color: #334155;
            font-family: 'Inter', sans-serif;
        }
        .navbar {
            background: #ffffff !important;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .navbar-brand {
            color: #0f172a !important;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .nav-link {
            color: #64748b !important;
            font-weight: 500;
            transition: 0.2s ease;
        }
        .nav-link.active, .nav-link:hover {
            color: #2563eb !important;
        }
        .card-modern { 
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); 
        }
        .btn {
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
        }
        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }
        .btn-outline-info { border-radius: 8px; }
        .table {
            color: #334155;
        }
        .table td, .table th {
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff !important;
        }
        .table-striped>tbody>tr:nth-of-type(odd)>* {
            background-color: #f8fafc !important;
        }
        input.form-control, select.form-select, textarea.form-control {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        input.form-control:focus, select.form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .modal-header {
            border-bottom: 1px solid #f1f5f9;
        }
        .modal-footer {
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
        }
        
        @media (max-width: 575.98px) {
            main.container { padding-left: 0.75rem; padding-right: 0.75rem; }
            .navbar .btn { width: 100%; margin-top: 0.5rem; }
        }

        /* Dark Mode Overrides */
        html[data-bs-theme="dark"] body { background: #0f172a; color: #f8fafc; }
        html[data-bs-theme="dark"] .navbar { background: #1e293b !important; border-bottom-color: #334155; }
        html[data-bs-theme="dark"] .navbar-brand, html[data-bs-theme="dark"] .nav-link, html[data-bs-theme="dark"] .navbar-text { color: #e2e8f0 !important; }
        html[data-bs-theme="dark"] .nav-link.active, html[data-bs-theme="dark"] .nav-link:hover { color: #60a5fa !important; }
        html[data-bs-theme="dark"] .card-modern { background: #1e293b; border-color: #334155; }
        html[data-bs-theme="dark"] .table td, html[data-bs-theme="dark"] .table th { background: #1e293b !important; border-bottom-color: #334155; color: #f8fafc; }
        html[data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* { background-color: #334155 !important; color: #f8fafc; }
        html[data-bs-theme="dark"] .table { color: #f8fafc; }
        html[data-bs-theme="dark"] input.form-control, html[data-bs-theme="dark"] select.form-select, html[data-bs-theme="dark"] textarea.form-control { background: #334155; border-color: #475569; color: #f8fafc; }
        html[data-bs-theme="dark"] input.form-control:focus, html[data-bs-theme="dark"] select.form-select:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25); background: #1e293b; color:#fff;}
        html[data-bs-theme="dark"] .modal-content { background: #1e293b; color: #f8fafc; }
        html[data-bs-theme="dark"] .modal-header, html[data-bs-theme="dark"] .modal-footer { border-color: #334155; background: #1e293b; }
        html[data-bs-theme="dark"] .text-secondary, html[data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        html[data-bs-theme="dark"] footer.bg-white { background-color: #1e293b !important; border-top-color: #334155 !important; }
    </style>
    <script>
        // Init dark mode before body renders to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>
</head>
<body>
<?php
$activePage = $_GET['page'] ?? '';
if ($activePage === '' && (($_SESSION['role'] ?? '') === 'petugas')) {
    $activePage = 'checkin';
}
?>
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold d-flex align-items-center" href="index.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-ticket-perforated-fill me-2" viewBox="0 0 16 16">
                <path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 0 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 1 0 0-3A.5.5 0 0 1 0 6V4.5Zm4-1v1h1v-1H4Zm7 0v1h1v-1h-1Zm-7 2v1h1v-1H4Zm7 0v1h1v-1h-1Zm-7 2v1h1v-1H4Zm7 0v1h1v-1h-1Zm-7 2v1h1v-1H4Zm7 0v1h1v-1h-1Z"/>
            </svg>
            Event Tiket
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'admin_dashboard' ? 'active' : '' ?>" href="index.php?page=admin_dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'venue' ? 'active' : '' ?>" href="index.php?page=venue">Venue</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'event' ? 'active' : '' ?>" href="index.php?page=event">Event</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'tiket' ? 'active' : '' ?>" href="index.php?page=tiket">Tiket</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'voucher' ? 'active' : '' ?>" href="index.php?page=voucher">Voucher</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'metode_pembayaran' ? 'active' : '' ?>" href="index.php?page=metode_pembayaran">Metode Bayar</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'report_transaksi' ? 'active' : '' ?>" href="index.php?page=report_transaksi">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'checkin' ? 'active' : '' ?>" href="index.php?page=checkin">Check-in</a></li>
                <?php elseif (($_SESSION['role'] ?? '') === 'petugas'): ?>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'report_transaksi' ? 'active' : '' ?>" href="index.php?page=report_transaksi">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'checkin' ? 'active' : '' ?>" href="index.php?page=checkin">Check-in</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'user_dashboard' ? 'active' : '' ?>" href="index.php?page=user_dashboard">Event</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'my_orders' ? 'active' : '' ?>" href="index.php?page=my_orders">Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'my_tickets' ? 'active' : '' ?>" href="index.php?page=my_tickets">Tiket Saya</a></li>
                <?php endif; ?>
            </ul>
            <button class="btn btn-sm btn-outline-secondary me-3 d-flex align-items-center" id="darkModeToggle" title="Toggle Dark/Light Mode">
                <svg id="themeIconDark" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16" style="display: none;">
                    <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
                </svg>
                <svg id="themeIconLight" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16">
                    <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                </svg>
            </button>
            <span class="navbar-text me-3"><?= e($_SESSION['name'] ?? '') ?></span>
            <a class="btn btn-light btn-sm" href="auth/logout.php">Logout</a>
        </div>
    </div>
</nav>

<main class="container py-4">
<?php if ($flash = flash_get()): ?>
    <div id="app-flash"
         data-flash-type="<?= e($flash['type']) ?>"
         data-flash-message="<?= e($flash['message']) ?>"
         class="d-none"></div>
<?php endif; ?>
