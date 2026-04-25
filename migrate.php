<?php
require_once __DIR__ . '/proses/bootstrap.php';
$sql = file_get_contents(__DIR__ . '/migration.sql');
try {
    $pdo->exec($sql);
    echo "Migration successful.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
