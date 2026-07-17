<?php
session_start();
require_once __DIR__ . '/config.php';

// Brute force protection
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lock_time'] = 0;
}

if ($_SESSION['login_lock_time'] > time()) {
    $_SESSION['login_error'] = 'Terlalu banyak percobaan. Coba lagi dalam ' . ceil(($_SESSION['login_lock_time'] - time()) / 60) . ' menit.';
    header('Location: index.php');
    exit;
}

// CSRF token check
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['login_error'] = 'Sesi tidak valid. Muat ulang halaman.';
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$pin = trim($_POST['pin'] ?? '');
$role = trim($_POST['role'] ?? 'admin');

// Validate input format
if (!preg_match('/^[a-zA-Z0-9 _.\-@]+$/', $username) && $role === 'admin') {
    $_SESSION['login_attempts']++;
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_lock_time'] = time() + 900; // 15 minutes
        $_SESSION['login_attempts'] = 0;
    }
    $_SESSION['login_error'] = 'Username/NAMA dan PIN tidak cocok.';
    header('Location: index.php');
    exit;
}

$pdo = getDB();
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([$username]);
    $adminUser = $stmt->fetch();
    if ($adminUser && password_verify($pin, $adminUser['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['role' => 'admin', 'data' => ['id' => $adminUser['id'], 'nama' => $adminUser['nama']]];
        header('Location: admin/dashboard.php');
        exit;
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'shohibul' LIMIT 1");
    $stmt->execute([$username]);
    $member = $stmt->fetch();
    if ($member && password_verify($pin, $member['password'])) {
        $anggota = $pdo->prepare("SELECT * FROM anggota WHERE id = ? LIMIT 1");
        $anggota->execute([$username]);
        $anggotaData = $anggota->fetch();
        session_regenerate_id(true);
        $_SESSION['user'] = ['role' => 'shohibul', 'data' => ['id' => $member['username'], 'nama' => $member['nama'], 'kelompok_id' => $anggotaData['kelompok_id'] ?? '', 'target_saving' => (int)($anggotaData['target_tabungan'] ?? 0)]];
        header('Location: anggota/dashboard.php');
        exit;
    }
}

$_SESSION['login_attempts']++;
if ($_SESSION['login_attempts'] >= 5) {
    $_SESSION['login_lock_time'] = time() + 900; // 15 minutes lock
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_error'] = 'Terlalu banyak percobaan gagal. Akun dikunci 15 menit.';
} else {
    $_SESSION['login_error'] = 'Username/NAMA dan PIN tidak cocok.';
}
header('Location: index.php');
exit;

