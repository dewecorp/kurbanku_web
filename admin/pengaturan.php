<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header('Location: ../index.php'); exit; }
$page = 'pengaturan';
include '../includes/head.php';
include '../includes/sidebar_admin.php';
include '../includes/topbar.php';
?>
<section class="space-y-6" id="tab-pengaturan">
  <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs max-w-2xl">
    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-4"><i class="w-4.5 h-4.5 text-emerald-600" data-lucide="settings"></i><span>Pengaturan Aplikasi</span></h3>
    <div class="space-y-4">
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status Koneksi Database</label>
        <div class="flex items-center gap-2" id="status-mode-preview-pill"></div>
      </div>
      <form class="grid grid-cols-1 sm:grid-cols-2 gap-4" onsubmit="handleArisanSettingsSubmit(event)">
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Ketua Arisan</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none bg-white text-slate-700 text-xs" id="settings-ketua-name" placeholder="Nama ketua arisan" type="text"/>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Bendahara Arisan</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none bg-white text-slate-700 text-xs" id="settings-bendahara-name" placeholder="Nama bendahara arisan" type="text"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nominal Arisan Bulanan</label>
          <div class="flex flex-col sm:flex-row gap-2">
            <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none bg-white text-slate-700 text-xs" id="settings-monthly-installment" min="0" step="1000" placeholder="150000" type="number"/>
            <button class="px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-extrabold hover:bg-slate-800 transition shrink-0" type="submit">Simpan Pengaturan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>
<?php include '../includes/modals.php'; include '../includes/footer.php'; ?>
