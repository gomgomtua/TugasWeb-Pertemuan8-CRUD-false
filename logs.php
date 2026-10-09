<?php
/**
 * logs.php — BONUS: riwayat aktivitas (50 terbaru) hasil log dari transaction CRUD
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/database.php';

$pdo       = Database::getInstance()->getConnection();
$logs      = [];
$loadError = null;

try {
    $logs = $pdo->query(
        'SELECT action, product_name, details, created_at
           FROM activity_logs
          ORDER BY id DESC
          LIMIT 50'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('DB Error (logs): ' . $e->getMessage());
    $loadError = 'Terjadi kesalahan database saat mengambil riwayat.';
}

$pageTitle = 'Riwayat Aktivitas';
$activeNav = 'riwayat';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Riwayat Aktivitas</h1>
        <p class="muted">50 aktivitas terakhir (tambah, ubah, hapus produk).</p>
    </div>
</div>

<?php if ($loadError): ?>
    <div class="alert alert-error" role="alert"><?= e($loadError) ?></div>
<?php endif; ?>

<div class="card table-wrap">
    <table>
        <thead>
        <tr>
            <th>Waktu</th>
            <th>Aksi</th>
            <th>Produk</th>
            <th>Keterangan</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($logs)): ?>
            <tr>
                <td colspan="4" class="empty">Belum ada aktivitas.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['created_at']) ?></td>
                <td><span class="badge badge-<?= e($log['action']) ?>"><?= e($log['action']) ?></span></td>
                <td><?= e($log['product_name']) ?></td>
                <td><?= e($log['details']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
