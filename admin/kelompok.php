<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header('Location: ../index.php'); exit; }
$page = 'kelompok';
include '../includes/head.php';
include '../includes/sidebar_admin.php';
include '../includes/topbar.php';
?>
<section class="space-y-6" id="tab-kelompok">
  <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
    <div class="flex justify-between items-center mb-5 shrink-0">
      <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2"><i class="w-4.5 h-4.5 text-emerald-600" data-lucide="users-2"></i><span>Daftar Kelompok (Periode Aktif)</span></h3>
      <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-2 rounded-xl transition flex items-center gap-1.5 shadow-md shadow-emerald-500/10" onclick="openAddGroup()"><i class="w-3.5 h-3.5" data-lucide="plus"></i> Tambah Kelompok</button>
    </div>
    <div class="overflow-x-auto custom-scrollbar -mx-6 sm:mx-0">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="text-slate-400 font-bold border-b border-slate-100 text-[11px] uppercase tracking-wider bg-slate-50/50">
            <th class="p-4 pl-6">Nama Kelompok</th><th class="p-4">Jenis Hewan</th><th class="p-4">Target Anggaran</th><th class="p-4">Anggota Terdaftar</th><th class="p-4 pr-6 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100" id="group-table-body"></tbody>
      </table>
    </div>
  </div>
</section>
<?php include '../includes/modals.php'; include '../includes/footer.php'; ?>
