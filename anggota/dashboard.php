<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'shohibul') { header('Location: ../index.php'); exit; }
$page = 'dashboard';
include '../includes/head.php';
include '../includes/sidebar_anggota.php';
include '../includes/topbar.php';
?>
<section class="space-y-6" id="tab-dashboard">
  <?php
  $loginSuccess = $_SESSION['login_success'] ?? null;
  unset($_SESSION['login_success']);
  ?>
  <?php if ($loginSuccess): ?>
  <script>document.addEventListener('DOMContentLoaded',function(){setTimeout(function(){toast(<?= json_encode($loginSuccess) ?>,'success')},300)});</script>
  <?php endif; ?>
  <div id="dashboard-hero"></div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="dashboard-stats"></div>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
      <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2"><i class="fa-solid fa-kaaba text-emerald-600"></i> Kemajuan Kelompok Tahap Aktif</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="dashboard-group-progress"></div>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4" id="personal-status-box">
      <div class="space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2"><i class="fa-solid fa-chart-line text-emerald-600"></i> Status Tabungan Anda</h3>
        <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100/50 space-y-3" id="personal-card-details"></div>
      </div>
    </div>
  </div>
</section>
<?php include '../includes/modals.php'; include '../includes/footer.php'; ?>
