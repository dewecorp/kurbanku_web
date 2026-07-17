<?php
require_once __DIR__ . '/config.php';

$pdo = getDB();

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

// --- GET handler ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = safeString($_GET['action'] ?? '');

    // Ping test
    if (($_GET['test'] ?? '') === 'ping') {
        jsonResponse(['status' => 'success', 'message' => 'Koneksi berhasil terhubung ke pangkalan data MySQL!']);
    }

    if ($action === 'getData' || $action === 'sync') {
        jsonResponse(buildResponse($pdo));
    }

    jsonResponse(['status' => 'error', 'message' => 'Aksi tidak dikenal.']);
}

// --- POST handler ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        jsonResponse(['status' => 'error', 'message' => 'Data JSON tidak valid.']);
    }

    if (!empty($input['testPing'])) {
        jsonResponse(['status' => 'success', 'message' => 'Tes tulis sukses!']);
    }

    saveRequest($pdo, $input);
    jsonResponse(buildResponse($pdo));
}

jsonResponse(['status' => 'error', 'message' => 'Metode request tidak didukung.']);

// ===================================================================
// FUNCTIONS
// ===================================================================

function buildResponse($pdo) {
    $now = now();

    $tahaps = fetchAll($pdo, "SELECT id, nama, status, updatedAt FROM tahap ORDER BY updatedAt DESC");
    if (empty($tahaps)) {
        $tahaps = [['id' => 't-1', 'nama' => 'Tahap 1', 'status' => 'Aktif', 'updatedAt' => $now]];
    }

    $groups = fetchAll($pdo, "SELECT id, nama, tipe, targetTotal, tahapId, updatedAt FROM kelompok ORDER BY nama ASC");
    $participants = fetchAll($pdo, "SELECT id, nama, whatsapp, pin, kelompok_id, target_tabungan, tahapId, updatedAt FROM anggota ORDER BY nama ASC");
    $deposits = fetchAll($pdo, "SELECT id, tanggal, anggota_id, jumlah, bulan, recorded_by, catatan, tahapId, updatedAt FROM setoran ORDER BY updatedAt DESC");
    $settings = fetchPengaturan($pdo);

    // Map fields to match frontend expectations
    $groups = array_map(function($g) {
        return [
            'id' => $g['id'],
            'name' => $g['nama'],
            'type' => $g['tipe'],
            'targetTotal' => (int)$g['targetTotal'],
            'tahapId' => $g['tahapId'],
            'updatedAt' => (int)$g['updatedAt'],
        ];
    }, $groups);

    $participants = array_map(function($p) {
        return [
            'id' => $p['id'],
            'name' => $p['nama'],
            'whatsapp' => $p['whatsapp'],
            'pin' => $p['pin'],
            'groupId' => $p['kelompok_id'],
            'targetAmount' => (int)$p['target_tabungan'],
            'tahapId' => $p['tahapId'],
            'updatedAt' => (int)$p['updatedAt'],
        ];
    }, $participants);

    $deposits = array_map(function($d) {
        return [
            'id' => $d['id'],
            'date' => $d['tanggal'],
            'participantId' => $d['anggota_id'],
            'amount' => (int)$d['jumlah'],
            'bulan' => (int)$d['bulan'],
            'recordedBy' => $d['recorded_by'],
            'note' => $d['catatan'],
            'tahapId' => $d['tahapId'],
            'updatedAt' => (int)$d['updatedAt'],
        ];
    }, $deposits);

    $tahaps = array_map(function($t) {
        return [
            'id' => $t['id'],
            'name' => $t['nama'],
            'status' => $t['status'],
            'updatedAt' => (int)$t['updatedAt'],
        ];
    }, $tahaps);

    $currentTahapId = getActiveTahapId($tahaps);

    return [
        'status' => 'success',
        'currentTahapId' => $currentTahapId,
        'tahaps' => $tahaps,
        'groups' => $groups,
        'participants' => $participants,
        'deposits' => $deposits,
        'deletedRecords' => [],
        'settings' => $settings,
        'lastUpdated' => $now,
        'data' => toClientData($tahaps, $groups, $participants, $deposits),
    ];
}

function toClientData($tahaps, $groups, $participants, $deposits) {
    return [
        'tahap' => array_map(function($t) {
            return ['id' => $t['id'], 'nama' => $t['name'], 'status' => $t['status'], 'updatedAt' => $t['updatedAt']];
        }, $tahaps),
        'kelompok' => array_map(function($g) {
            return ['id' => $g['id'], 'nama' => $g['name'], 'tipe' => $g['type'], 'target' => $g['targetTotal'], 'tahapId' => $g['tahapId'], 'updatedAt' => $g['updatedAt']];
        }, $groups),
        'anggota' => array_map(function($p) {
            return ['id' => $p['id'], 'nama' => $p['name'], 'whatsapp' => $p['whatsapp'], 'pin' => $p['pin'], 'kelompok_id' => $p['groupId'], 'target_saving' => $p['targetAmount'], 'tahapId' => $p['tahapId'], 'updatedAt' => $p['updatedAt']];
        }, $participants),
        'setoran' => array_map(function($d) {
            return ['id' => $d['id'], 'tanggal' => $d['date'], 'anggota_id' => $d['participantId'], 'jumlah' => $d['amount'], 'bulan' => $d['bulan'], 'recorded_by' => $d['recordedBy'], 'catatan' => $d['note'], 'tahapId' => $d['tahapId'], 'updatedAt' => $d['updatedAt']];
        }, $deposits),
    ];
}

function getActiveTahapId($tahaps) {
    foreach ($tahaps as $t) {
        if (($t['status'] ?? '') === 'Aktif') return $t['id'];
    }
    return !empty($tahaps) ? $tahaps[0]['id'] : 't-1';
}

function saveRequest($pdo, $request) {
    $action = safeString($request['action'] ?? '');
    $data = $request['data'] ?? [];
    $settings = $request['settings'] ?? [];
    $tNow = now();

    // Handle bulk sync format
    if (!empty($request['tahaps']) || !empty($request['groups']) || !empty($request['participants']) || !empty($request['deposits'])) {
        handleBulkSync($pdo, $request, $tNow);
        return;
    }

    if ($action === 'saveTahap' || $action === 'saveKelompok' || $action === 'saveAnggota' || $action === 'saveSetoran') {
        $data['updatedAt'] = $tNow;
    }

    if ($action === 'saveTahap') {
        $id = safeString($data['id'] ?? '') ?: generateId('t');
        $status = safeString($data['status'] ?? 'Aktif');
        if ($status === 'Aktif') {
            $pdo->prepare("UPDATE tahap SET status = 'Nonaktif', updatedAt = ? WHERE status = 'Aktif' AND id != ?")
                ->execute([$tNow, $id]);
        }
        $pdo->prepare("INSERT INTO tahap (id, nama, status, updatedAt) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nama = VALUES(nama), status = VALUES(status), updatedAt = VALUES(updatedAt)")
            ->execute([$id, safeString($data['nama'] ?? $data['name'] ?? ''), $status, $tNow]);
    }

    if ($action === 'saveKelompok') {
        $id = safeString($data['id'] ?? '') ?: generateId('g');
        $pdo->prepare("INSERT INTO kelompok (id, nama, tipe, targetTotal, tahapId, updatedAt) VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nama = VALUES(nama), tipe = VALUES(tipe), targetTotal = VALUES(targetTotal), tahapId = VALUES(tahapId), updatedAt = VALUES(updatedAt)")
            ->execute([
                $id,
                safeString($data['nama'] ?? $data['name'] ?? ''),
                safeString($data['tipe'] ?? $data['type'] ?? 'Sapi'),
                safeNumber($data['target'] ?? $data['targetTotal'] ?? 0),
                safeString($data['tahapId'] ?? getActiveTahapId(fetchAll($pdo, "SELECT id, nama, status, updatedAt FROM tahap ORDER BY updatedAt DESC"))),
                $tNow,
            ]);
    }

    if ($action === 'saveAnggota') {
        $id = safeString($data['id'] ?? '') ?: generateId('p');
        $pdo->prepare("INSERT INTO anggota (id, nama, whatsapp, pin, kelompok_id, target_tabungan, tahapId, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nama = VALUES(nama), whatsapp = VALUES(whatsapp), pin = VALUES(pin), kelompok_id = VALUES(kelompok_id), target_tabungan = VALUES(target_tabungan), tahapId = VALUES(tahapId), updatedAt = VALUES(updatedAt)")
            ->execute([
                $id,
                safeString($data['nama'] ?? $data['name'] ?? ''),
                safeString($data['whatsapp'] ?? ''),
                safeString($data['pin'] ?? '1234'),
                safeString($data['kelompok_id'] ?? $data['groupId'] ?? ''),
                safeNumber($data['target_saving'] ?? $data['targetAmount'] ?? 3000000),
                safeString($data['tahapId'] ?? getActiveTahapId(fetchAll($pdo, "SELECT id, nama, status, updatedAt FROM tahap ORDER BY updatedAt DESC"))),
                $tNow,
            ]);
    }

    if ($action === 'saveSetoran') {
        $id = safeString($data['id'] ?? '') ?: generateId('d');
        $pdo->prepare("INSERT INTO setoran (id, tanggal, anggota_id, jumlah, bulan, recorded_by, catatan, tahapId, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE tanggal = VALUES(tanggal), anggota_id = VALUES(anggota_id), jumlah = VALUES(jumlah), bulan = VALUES(bulan), recorded_by = VALUES(recorded_by), catatan = VALUES(catatan), tahapId = VALUES(tahapId), updatedAt = VALUES(updatedAt)")
            ->execute([
                $id,
                safeString($data['tanggal'] ?? $data['date'] ?? ''),
                safeString($data['anggota_id'] ?? $data['participantId'] ?? ''),
                safeNumber($data['jumlah'] ?? $data['amount'] ?? 0),
                safeNumber($data['bulan'] ?? 1),
                safeString($data['recorded_by'] ?? $data['recordedBy'] ?? ''),
                safeString($data['catatan'] ?? $data['note'] ?? ''),
                safeString($data['tahapId'] ?? getActiveTahapId(fetchAll($pdo, "SELECT id, nama, status, updatedAt FROM tahap ORDER BY updatedAt DESC"))),
                $tNow,
            ]);
    }

    if ($action === 'saveCloudUrl') {
        $url = safeString($data['cloudUrl'] ?? $settings['cloudUrl'] ?? '');
        upsertSetting($pdo, 'cloudUrl', $url, $tNow);
    }

    if ($action === 'saveSettings') {
        if (!empty($data['ketuaName'])) upsertSetting($pdo, 'ketuaName', safeString($data['ketuaName']), $tNow);
        if (!empty($data['bendaharaName'])) upsertSetting($pdo, 'bendaharaName', safeString($data['bendaharaName']), $tNow);
        if (!empty($data['monthlyInstallment'])) upsertSetting($pdo, 'monthlyInstallment', (string)safeNumber($data['monthlyInstallment']), $tNow);
        if (!empty($settings['ketuaName'])) upsertSetting($pdo, 'ketuaName', safeString($settings['ketuaName']), $tNow);
        if (!empty($settings['bendaharaName'])) upsertSetting($pdo, 'bendaharaName', safeString($settings['bendaharaName']), $tNow);
        if (!empty($settings['monthlyInstallment'])) upsertSetting($pdo, 'monthlyInstallment', (string)safeNumber($settings['monthlyInstallment']), $tNow);
    }

    // Handle deletes
    $deleteMap = [
        'deleteTahap' => 'tahap',
        'deleteKelompok' => 'kelompok',
        'deleteAnggota' => 'anggota',
        'deleteSetoran' => 'setoran',
    ];
    if (isset($deleteMap[$action]) && !empty($data['id'])) {
        $id = safeString($data['id']);
        if ($action === 'deleteTahap') $pdo->prepare("DELETE FROM tahap WHERE id = ?")->execute([$id]);
        if ($action === 'deleteKelompok') $pdo->prepare("DELETE FROM kelompok WHERE id = ?")->execute([$id]);
        if ($action === 'deleteAnggota') $pdo->prepare("DELETE FROM anggota WHERE id = ?")->execute([$id]);
        if ($action === 'deleteSetoran') $pdo->prepare("DELETE FROM setoran WHERE id = ?")->execute([$id]);
    }
}

function handleBulkSync($pdo, $request, $tNow) {
    // Tahap
    if (!empty($request['tahaps'])) {
        foreach ($request['tahaps'] as $t) {
            $id = safeString($t['id'] ?? '') ?: generateId('t');
            $status = safeString($t['status'] ?? 'Aktif');
            if ($status === 'Aktif') {
                $pdo->prepare("UPDATE tahap SET status = 'Nonaktif', updatedAt = ? WHERE status = 'Aktif' AND id != ?")
                    ->execute([$tNow, $id]);
            }
            $pdo->prepare("INSERT INTO tahap (id, nama, status, updatedAt) VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nama = VALUES(nama), status = VALUES(status), updatedAt = VALUES(updatedAt)")
                ->execute([$id, safeString($t['name'] ?? $t['nama'] ?? ''), $status, $tNow]);
        }
    }

    // Kelompok
    if (!empty($request['groups'])) {
        foreach ($request['groups'] as $g) {
            $id = safeString($g['id'] ?? '') ?: generateId('g');
            $pdo->prepare("INSERT INTO kelompok (id, nama, tipe, targetTotal, tahapId, updatedAt) VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nama = VALUES(nama), tipe = VALUES(tipe), targetTotal = VALUES(targetTotal), tahapId = VALUES(tahapId), updatedAt = VALUES(updatedAt)")
                ->execute([
                    $id,
                    safeString($g['name'] ?? $g['nama'] ?? ''),
                    safeString($g['type'] ?? $g['tipe'] ?? 'Sapi'),
                    safeNumber($g['targetTotal'] ?? $g['target'] ?? 0),
                    safeString($g['tahapId'] ?? ''),
                    $tNow,
                ]);
        }
    }

    // Anggota
    if (!empty($request['participants'])) {
        foreach ($request['participants'] as $p) {
            $id = safeString($p['id'] ?? '') ?: generateId('p');
            $pdo->prepare("INSERT INTO anggota (id, nama, whatsapp, pin, kelompok_id, target_tabungan, tahapId, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nama = VALUES(nama), whatsapp = VALUES(whatsapp), pin = VALUES(pin), kelompok_id = VALUES(kelompok_id), target_tabungan = VALUES(target_tabungan), tahapId = VALUES(tahapId), updatedAt = VALUES(updatedAt)")
                ->execute([
                    $id,
                    safeString($p['name'] ?? $p['nama'] ?? ''),
                    safeString($p['whatsapp'] ?? ''),
                    safeString($p['pin'] ?? '1234'),
                    safeString($p['groupId'] ?? $p['kelompok_id'] ?? ''),
                    safeNumber($p['targetAmount'] ?? $p['target_saving'] ?? 3000000),
                    safeString($p['tahapId'] ?? ''),
                    $tNow,
                ]);
        }
    }

    // Setoran
    if (!empty($request['deposits'])) {
        foreach ($request['deposits'] as $d) {
            $id = safeString($d['id'] ?? '') ?: generateId('d');
            $pdo->prepare("INSERT INTO setoran (id, tanggal, anggota_id, jumlah, bulan, recorded_by, catatan, tahapId, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE tanggal = VALUES(tanggal), anggota_id = VALUES(anggota_id), jumlah = VALUES(jumlah), bulan = VALUES(bulan), recorded_by = VALUES(recorded_by), catatan = VALUES(catatan), tahapId = VALUES(tahapId), updatedAt = VALUES(updatedAt)")
                ->execute([
                    $id,
                    safeString($d['date'] ?? $d['tanggal'] ?? ''),
                    safeString($d['participantId'] ?? $d['anggota_id'] ?? ''),
                    safeNumber($d['amount'] ?? $d['jumlah'] ?? 0),
                    safeNumber($d['bulan'] ?? 1),
                    safeString($d['recordedBy'] ?? $d['recorded_by'] ?? ''),
                    safeString($d['note'] ?? $d['catatan'] ?? ''),
                    safeString($d['tahapId'] ?? ''),
                    $tNow,
                ]);
        }
    }

    // Settings
    if (!empty($request['settings'])) {
        $s = $request['settings'];
        if (!empty($s['ketuaName'])) upsertSetting($pdo, 'ketuaName', safeString($s['ketuaName']), $tNow);
        if (!empty($s['bendaharaName'])) upsertSetting($pdo, 'bendaharaName', safeString($s['bendaharaName']), $tNow);
        if (!empty($s['monthlyInstallment'])) upsertSetting($pdo, 'monthlyInstallment', (string)safeNumber($s['monthlyInstallment']), $tNow);
        if (!empty($s['cloudUrl'])) upsertSetting($pdo, 'cloudUrl', safeString($s['cloudUrl']), $tNow);
    }
}

function upsertSetting($pdo, $key, $value, $tNow) {
    $pdo->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)")
        ->execute([$key, $value]);
    upsertSettingTimestamp($pdo, $tNow);
}

function upsertSettingTimestamp($pdo, $tNow) {
    $pdo->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES ('lastUpdated', ?)
        ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)")
        ->execute([(string)$tNow]);
}

function fetchAll($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchPengaturan($pdo) {
    $settings = [
        'ketuaName' => '',
        'bendaharaName' => '',
        'monthlyInstallment' => 150000,
        'cloudUrl' => '',
        'gatheringDates' => [],
        'lastUpdated' => 0,
    ];
    $rows = fetchAll($pdo, "SELECT kunci, nilai FROM pengaturan");
    foreach ($rows as $row) {
        $key = $row['kunci'];
        $val = $row['nilai'];
        if ($key === 'ketuaName') $settings['ketuaName'] = safeString($val);
        if ($key === 'bendaharaName') $settings['bendaharaName'] = safeString($val);
        if ($key === 'monthlyInstallment') $settings['monthlyInstallment'] = safeNumber($val, 150000);
        if ($key === 'cloudUrl') $settings['cloudUrl'] = safeString($val);
        if ($key === 'lastUpdated') $settings['lastUpdated'] = safeNumber($val, 0);
        if ($key === 'gatheringDates') {
            try { $settings['gatheringDates'] = json_decode(safeString($val), true) ?: []; } catch (Exception $e) { $settings['gatheringDates'] = []; }
        }
    }
    return $settings;
}
