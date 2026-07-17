<?php
session_start();
require_once __DIR__ . '/config.php';

$username = trim($_POST['username'] ?? '');
$pin = trim($_POST['pin'] ?? '');
$role = trim($_POST['role'] ?? 'admin');

if ($role === 'admin') {
    if (strtolower($username) === 'admin' && $pin === 'admin2026') {
        $_SESSION['user'] = ['role' => 'admin', 'data' => ['id' => 'ADMIN', 'nama' => 'Administrator']];
        header('Location: admin/dashboard.php');
        exit;
    }
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM anggota WHERE LOWER(nama) LIKE ? AND pin = ?");
    $stmt->execute(['%admin%', $pin]);
    $adminUser = $stmt->fetch();
    if ($adminUser) {
        $_SESSION['user'] = ['role' => 'admin', 'data' => ['id' => $adminUser['id'], 'nama' => $adminUser['nama']]];
        header('Location: admin/dashboard.php');
        exit;
    }
} else {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM anggota WHERE id = ? AND pin = ?");
    $stmt->execute([$username, $pin]);
    $member = $stmt->fetch();
    if ($member) {
        $_SESSION['user'] = ['role' => 'shohibul', 'data' => ['id' => $member['id'], 'nama' => $member['nama'], 'kelompok_id' => $member['kelompok_id'], 'target_saving' => (int)$member['target_tabungan']]];
        header('Location: anggota/dashboard.php');
        exit;
    }
}

$_SESSION['login_error'] = 'Username/NAMA dan PIN tidak cocok.';
header('Location: index.php');
exit;
