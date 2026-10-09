<?php
/**
 * includes/helpers.php
 * Fungsi bantu: escape output, flash message, redirect, validasi, log aktivitas.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape output HTML (pencegahan XSS) — dipakai SETIAP mencetak data ke HTML. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Format angka ke Rupiah, contoh: Rp 8.500.000 */
function rupiah($amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

/** Redirect lalu hentikan script (Post/Redirect/Get pattern). */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Simpan flash message (type: success | error) untuk ditampilkan 1x di halaman berikutnya. */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Ambil lalu hapus flash message. */
function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** Ambil & rapikan input form produk dari $_POST. */
function read_product_input(array $src): array
{
    return [
        'name'        => trim((string) ($src['name'] ?? '')),
        'category_id' => (int) ($src['category_id'] ?? 0),
        'supplier_id' => (int) ($src['supplier_id'] ?? 0),
        'price'       => trim((string) ($src['price'] ?? '')),
        'stock'       => trim((string) ($src['stock'] ?? '')),
    ];
}

/** Validasi input produk. Mengembalikan array pesan error (kosong = valid). */
function validate_product(array $d): array
{
    $errors = [];

    if ($d['name'] === '') {
        $errors[] = 'Nama produk wajib diisi.';
    } elseif (mb_strlen($d['name']) > 150) {
        $errors[] = 'Nama produk maksimal 150 karakter.';
    }

    if ($d['category_id'] <= 0) {
        $errors[] = 'Kategori wajib dipilih.';
    }

    if ($d['supplier_id'] <= 0) {
        $errors[] = 'Supplier wajib dipilih.';
    }

    if (!is_numeric($d['price']) || (float) $d['price'] < 0 || (float) $d['price'] > 9999999999) {
        $errors[] = 'Harga harus berupa angka antara 0 s.d. 9.999.999.999.';
    }

    if (filter_var($d['stock'], FILTER_VALIDATE_INT) === false || (int) $d['stock'] < 0) {
        $errors[] = 'Stok harus berupa bilangan bulat 0 atau lebih.';
    }

    return $errors;
}

/** Catat aktivitas ke tabel activity_logs (dipanggil di dalam transaction). */
function log_activity(PDO $pdo, string $action, ?int $productId, string $productName, string $details): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO activity_logs (action, product_id, product_name, details) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$action, $productId, $productName, $details]);
}
