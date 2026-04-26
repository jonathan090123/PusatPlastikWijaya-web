<?php
/**
 * Test Race Condition - simulasi 2 admin klik "Completed" bersamaan
 *
 * CARA PAKAI:
 * 1. Pastikan app Laravel sudah jalan (php artisan serve)
 * 2. Isi ORDER_ID, SESSION_COOKIE_A, SESSION_COOKIE_B di bawah
 * 3. Jalankan: php test_race_condition.php
 */

// ─── CONFIG ───────────────────────────────────────────────────────────────────

// ID order yang akan ditest (harus statusnya 'processing' atau bukan 'completed')
define('ORDER_ID', 1);

// Base URL aplikasi
define('BASE_URL', 'http://localhost:8000');

// Cookie session admin (ambil dari browser DevTools → Application → Cookies → laravel_session)
// Bisa pakai 1 akun admin yang sama (cookie yang sama), karena kita test concurrency di server
define('ADMIN_COOKIE', 'laravel_session=GANTI_DENGAN_COOKIE_SESSION_ADMIN_KAMU');

// ─── SETUP ────────────────────────────────────────────────────────────────────

// Reset order ke status 'processing' dulu sebelum test
echo "🔄 Persiapan: reset order #" . ORDER_ID . " ke status 'processing'...\n";
resetOrderStatus('processing');
sleep(1);

// Reset point_histories: hapus earned untuk order ini
echo "🔄 Persiapan: hapus point_histories untuk order #" . ORDER_ID . "...\n";
resetPointHistories();
sleep(1);

// ─── SIMULASI 2 REQUEST BERSAMAAN ─────────────────────────────────────────────

echo "\n🚀 Mengirim 2 request 'Completed' secara bersamaan...\n";
$results = sendConcurrentRequests();

echo "\n📊 Hasil:\n";
echo "  Request 1: HTTP " . $results[0]['status'] . "\n";
echo "  Request 2: HTTP " . $results[1]['status'] . "\n";

// ─── CEK HASIL ────────────────────────────────────────────────────────────────

echo "\n🔍 Mengecek database...\n";

// Koneksi langsung ke MySQL untuk cek hasil
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pusat_plastik_wijaya', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Cek berapa kali poin diberikan
$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(amount) as total_points FROM point_histories WHERE order_id = ? AND type = 'earned'");
$stmt->execute([ORDER_ID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\n  point_histories (type=earned) untuk order #" . ORDER_ID . ":\n";
echo "  → Jumlah baris : " . $row['total'] . "\n";
echo "  → Total poin   : " . ($row['total_points'] ?? 0) . "\n";

if ($row['total'] == 1) {
    echo "\n✅ LULUS - Poin hanya diberikan 1x. Race condition berhasil dicegah!\n";
} elseif ($row['total'] == 0) {
    echo "\n⚠️  Poin belum diberikan (mungkin order belum sampai 'completed' atau poin = 0 karena nominal kecil).\n";
} else {
    echo "\n❌ GAGAL - Poin diberikan " . $row['total'] . "x! Ada race condition!\n";
}

// Cek status order akhir
$stmt2 = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
$stmt2->execute([ORDER_ID]);
$order = $stmt2->fetch(PDO::FETCH_ASSOC);
echo "\n  Status order akhir: " . ($order['status'] ?? 'tidak ditemukan') . "\n\n";

// ─── FUNCTIONS ────────────────────────────────────────────────────────────────

function sendConcurrentRequests(): array
{
    $url = BASE_URL . '/admin/orders/' . ORDER_ID . '/status';

    // Ambil CSRF token dulu
    $csrfToken = getCsrfToken();
    if (!$csrfToken) {
        echo "❌ Gagal ambil CSRF token. Pastikan cookie session benar.\n";
        exit(1);
    }
    echo "  CSRF token: " . substr($csrfToken, 0, 20) . "...\n";

    $ch1 = curl_init($url);
    $ch2 = curl_init($url);

    $postData = http_build_query([
        '_token'  => $csrfToken,
        '_method' => 'PATCH',
        'status'  => 'completed',
    ]);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: ' . ADMIN_COOKIE,
            'X-XSRF-TOKEN: ' . $csrfToken,
        ],
    ];

    curl_setopt_array($ch1, $options);
    curl_setopt_array($ch2, $options);

    // Kirim bersamaan pakai curl_multi
    $mh = curl_multi_init();
    curl_multi_add_handle($mh, $ch1);
    curl_multi_add_handle($mh, $ch2);

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    $results = [
        ['status' => curl_getinfo($ch1, CURLINFO_HTTP_CODE), 'body' => curl_multi_getcontent($ch1)],
        ['status' => curl_getinfo($ch2, CURLINFO_HTTP_CODE), 'body' => curl_multi_getcontent($ch2)],
    ];

    curl_multi_remove_handle($mh, $ch1);
    curl_multi_remove_handle($mh, $ch2);
    curl_multi_cleanup($mh);
    curl_close($ch1);
    curl_close($ch2);

    return $results;
}

function getCsrfToken(): ?string
{
    $ch = curl_init(BASE_URL . '/admin/orders/' . ORDER_ID);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => ['Cookie: ' . ADMIN_COOKIE],
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="_token".*?value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return null;
}

function resetOrderStatus(string $status): void
{
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pusat_plastik_wijaya', 'root', '');
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, ORDER_ID]);
    echo "  ✓ Order #" . ORDER_ID . " direset ke '$status'\n";
}

function resetPointHistories(): void
{
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pusat_plastik_wijaya', 'root', '');
    $deleted = $pdo->prepare("DELETE FROM point_histories WHERE order_id = ? AND type = 'earned'")->execute([ORDER_ID]);
    echo "  ✓ point_histories dihapus untuk order #" . ORDER_ID . "\n";
}
