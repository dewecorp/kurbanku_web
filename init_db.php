<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = getDB();

    $pdo->exec("CREATE TABLE IF NOT EXISTS tahap (
        id VARCHAR(50) PRIMARY KEY,
        nama VARCHAR(255) NOT NULL DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'Aktif',
        updatedAt BIGINT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS kelompok (
        id VARCHAR(50) PRIMARY KEY,
        nama VARCHAR(255) NOT NULL DEFAULT '',
        tipe VARCHAR(50) NOT NULL DEFAULT 'Sapi',
        targetTotal BIGINT NOT NULL DEFAULT 0,
        tahapId VARCHAR(50) NOT NULL DEFAULT '',
        updatedAt BIGINT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS anggota (
        id VARCHAR(50) PRIMARY KEY,
        nama VARCHAR(255) NOT NULL DEFAULT '',
        whatsapp VARCHAR(50) NOT NULL DEFAULT '',
        pin VARCHAR(10) NOT NULL DEFAULT '1234',
        kelompok_id VARCHAR(50) NOT NULL DEFAULT '',
        target_tabungan BIGINT NOT NULL DEFAULT 3000000,
        tahapId VARCHAR(50) NOT NULL DEFAULT '',
        updatedAt BIGINT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS setoran (
        id VARCHAR(50) PRIMARY KEY,
        tanggal VARCHAR(20) NOT NULL DEFAULT '',
        anggota_id VARCHAR(50) NOT NULL DEFAULT '',
        jumlah BIGINT NOT NULL DEFAULT 0,
        bulan INT NOT NULL DEFAULT 1,
        recorded_by VARCHAR(255) NOT NULL DEFAULT '',
        catatan TEXT,
        tahapId VARCHAR(50) NOT NULL DEFAULT '',
        updatedAt BIGINT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pengaturan (
        kunci VARCHAR(100) PRIMARY KEY,
        nilai TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed data default
    $count = $pdo->query("SELECT COUNT(*) FROM tahap")->fetchColumn();
    if ($count == 0) {
        $t = now();
        $pdo->prepare("INSERT INTO tahap (id, nama, status, updatedAt) VALUES (?, ?, ?, ?)")
            ->execute(['t-1', 'Qurban 1447 H / 2026 M', 'Aktif', $t]);
    }

    $settings = [
        ['ketuaName', 'H. Rozikin Dimyati'],
        ['bendaharaName', 'Sudarlim'],
        ['monthlyInstallment', '150000'],
        ['lastUpdated', '0'],
    ];
    foreach ($settings as $s) {
        $exists = $pdo->prepare("SELECT COUNT(*) FROM pengaturan WHERE kunci = ?");
        $exists->execute([$s[0]]);
        if ($exists->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?)")->execute($s);
        }
    }

    echo "Database berhasil diinisialisasi! <a href='index.php'>Buka Aplikasi</a>";
} catch (Exception $e) {
    echo "Gagal inisialisasi database: " . $e->getMessage();
}
