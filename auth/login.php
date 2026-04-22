<?php
require_once '../proses/bootstrap.php';
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Event Tiket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f8fafc; 
            color: #334155;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            min-height: 100vh;
        }
        .card { 
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025) !important; 
        }
        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
            font-weight: 500;
            border-radius: 8px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }
        input.form-control {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            padding: 0.6rem 0.8rem;
        }
        input.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        a { color: #2563eb; text-decoration: none; font-weight: 500; }
        a:hover { color: #1d4ed8; text-decoration: underline; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-1">Masuk</h4>
                    <p class="text-secondary mb-4">Sistem Informasi Pemesanan Tiket Event</p>
                    <?php if ($flash = flash_get()): ?>
                        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                    <?php endif; ?>
                    <form action="login_proses.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Login</button>
                    </form>
                    <p class="mb-0 mt-3 text-center">Belum punya akun?
                        <a href="register.php">Daftar</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
