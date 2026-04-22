<?php
declare(strict_types=1);

$idAttendee = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT a.id_attendee, a.kode_tiket, u.nama AS nama_user,
        e.nama_event, e.tanggal AS tanggal_event, t.nama_tiket
    FROM attendee a
    JOIN order_detail od ON od.id_detail = a.id_detail
    JOIN orders o ON o.id_order = od.id_order
    JOIN tiket t ON t.id_tiket = od.id_tiket
    JOIN event e ON e.id_event = t.id_event
    LEFT JOIN users u ON u.id_user = o.id_user
    WHERE a.id_attendee = ? AND o.status = 'paid'");

$stmt->execute([$idAttendee]);
$tiketData = $stmt->fetch();

if (!$tiketData) {
    echo "<div class='alert alert-danger m-4'>Data tiket tidak valid atau belum lunas.</div>";
    die();
}
?>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printable-wristband, #printable-wristband * {
        visibility: visible;
    }
    #printable-wristband {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    nav, footer, .navbar, .sidebar {
        display: none !important;
    }
    @page {
        size: auto;
        margin: 0mm;
    }
}

.wristband-container {
    padding: 2rem;
    display: flex;
    justify-content: center;
    background-color: #f1f3f5;
    border-radius: 8px;
    margin-top: 1rem;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
}

.wristband {
    /* Lebar standar gelang ditambah sedikit tinggi agar rileks */
    width: 260mm;
    height: 35mm;
    /* Tema Elegan / Premium Festival: Navy & Gold */
    background: #111a2c;
    color: #ffffff;
    display: flex;
    font-family: 'Inter', system-ui, sans-serif;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #ddd; /* subtle border for light printers */
}

/* Aksesoris garis tepi */
.wristband::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 4px;
    background: #d4af37; /* Warna Gold */
    z-index: 5;
}

.wristband-left {
    width: 70mm; /* Diperlebar agar barcode tidak kecil */
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    border-right: 2px dashed #999;
    padding: 2mm;
    z-index: 1;
}

.wristband-left img {
    width: 100%;
    /* Tinggi maksimal dimaksimalkan untuk scan mudah */
    max-height: 25mm;
    object-fit: contain;
}

.wristband-body {
    flex-grow: 1;
    padding: 3mm 5mm;
    display: flex;
    flex-direction: column;
    justify-content: center;
    z-index: 1;
    position: relative;
}

.wb-event {
    font-size: 14pt;
    font-weight: 800;
    text-transform: uppercase;
    margin: 0 0 2px 0;
    line-height: 1.2;
    color: #ffffff;
    /* Mencegah terpotong kasar: biarkan menjadi baris baru jika sangat panjang */
    white-space: normal;
}

.wb-info-group {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 3px;
}

.wb-date {
    font-size: 9pt;
    font-weight: 600;
    color: #a8b2c1;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wb-type-badge {
    background: #d4af37; /* Warna Emas */
    color: #000000;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 8pt;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.wb-user {
    font-size: 11pt;
    font-weight: 600;
    color: #e2e8f0;
    margin-top: auto;
    /* Mencegah nama kepanjangan merusak layout, elipsis jika terlalu panjang */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.wristband-right {
    width: 35mm;
    background: #0f1626;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-left: 1px solid rgba(255,255,255,0.1);
    color: #ffffff;
    z-index: 1;
    padding: 0 10px;
}

.wb-code-rotated {
    font-size: 9pt;
    font-family: monospace;
    font-weight: bold;
    color: #d4af37;
    text-transform: uppercase;
    letter-spacing: 1px;
    white-space: nowrap;
    text-align: center;
}
.wb-code-title {
    font-size: 6pt;
    color: #a8b2c1;
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: 2px;
}
</style>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h4 class="d-print-none">Pratinjau Gelang Fisik </h4>
            <button onclick="window.print()" class="btn btn-primary d-print-none shadow-sm me-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer me-1" viewBox="0 0 16 16">
                  <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                  <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
                </svg>
                Cetak Gelang Tiket
            </button>
            <a href="index.php?page=checkin" class="btn btn-outline-secondary d-print-none shadow-sm">Kembali</a>
        </div>
    </div>
    
    <div class="wristband-container d-print-block p-print-0 m-print-0 bg-print-transparent">
        <div id="printable-wristband" class="wristband text-start mx-auto ms-print-0">
            <div class="wristband-left">
                <!-- Barcode dibuat lebih jelas dengan ruang putih yang luas -->
                <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= urlencode($tiketData['kode_tiket']) ?>&code=Code128&dpi=120" alt="Barcode">
            </div>
            
            <div class="wristband-body">
                <h4 class="wb-event"><?= e($tiketData['nama_event']) ?></h4>
                <div class="wb-info-group">
                    <span class="wb-date"><?= date('d F Y', strtotime($tiketData['tanggal_event'])) ?></span>
                    <span class="wb-type-badge"><?= e($tiketData['nama_tiket']) ?></span>
                </div>
                <div class="wb-user"><?= e($tiketData['nama_user']) ?></div>
            </div>
            
            <div class="wristband-right">
                <div class="wb-code-title">TICKET CODE</div>
                <span class="wb-code-rotated"><?= e($tiketData['kode_tiket']) ?></span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Beri jeda secukupnya agar gambar barcode ukuran besar muat sempurna sebelum terpotong
        setTimeout(function(){
            window.print();
        }, 1500);
    });
</script>
