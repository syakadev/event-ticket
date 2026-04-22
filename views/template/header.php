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
    </style>
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
        <a class="navbar-brand fw-semibold" href="index.php">
            
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
