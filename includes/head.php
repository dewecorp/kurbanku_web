<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
  <title>QurbanKu - Sistem Manajemen Tabungan Qurban</title>
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg?v=<?= filemtime(__DIR__ . '/../assets/favicon.svg') ?>"/>
  <script src="../assets/css/tailwind.js?v=<?= filemtime(__DIR__ . '/../assets/css/tailwind.js') ?>"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="../assets/fontawesome/css/all.min.css?v=<?= filemtime(__DIR__ . '/../assets/fontawesome/css/all.min.css') ?>" rel="stylesheet"/>
  <script src="../assets/js/vendor/lucide.js?v=<?= filemtime(__DIR__ . '/../assets/js/vendor/lucide.js') ?>"></script>
  <style>
    html, body { height: 100%; margin: 0; }
    #qurbanku-app-root { display: block; min-height: 100vh; }
    #qurbanku-app-root > aside { position: fixed; top: 0; left: 0; bottom: 0; width: 256px; overflow-y: auto; z-index: 40; }
    #qurbanku-app-root > main { margin-left: 256px; min-height: 100vh; display: flex; flex-direction: column; padding-bottom: 90px; }
    .app-footer { position: fixed; bottom: 0; left: 256px; right: 0; background: #fff; border-top: 1px solid #e2e8f0; padding: 14px 24px; z-index: 30; box-sizing: border-box; }

    /* Mobile: sidebar overlay + content full width */
    @media (max-width: 767px) {
      #qurbanku-app-root > main { margin-left: 0; padding-bottom: 90px; }
      .app-footer { left: 0; padding: 12px 16px; }
      #header-clock-date, #header-clock-time { display: none; }
      .app-footer-copy { display: none; }
      #qurbanku-app-root > aside { box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
    }
    @media (min-width: 768px) {
      #sidebar-backdrop { display: none !important; }
      #app-sidebar { transform: none !important; }
    }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .role-shohibul .admin-only { display: none !important; }
    footer:not(.app-footer) { display: none !important; }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen" data-page="<?= htmlspecialchars($page ?? '') ?>">
<div class="flex flex-col min-h-screen text-sm" id="qurbanku-app-root">
