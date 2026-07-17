<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'shohibul') { header('Location: ../index.php'); exit; }
$page = 'riwayat';
include '../includes/head.php';
include '../includes/sidebar_anggota.php';
include '../includes/topbar.php';
?>
<section class="space-y-6" id="tab-riwayat" data-page="riwayat">
  <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 shrink-0">
      <div>
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2"><i class="w-4.5 h-4.5 text-emerald-600" data-lucide="receipt-text"></i><span>Riwayat Setoran Saya</span></h3>
        <p class="text-[10px] text-slate-400 mt-1">Daftar seluruh transaksi tabungan yang telah Anda bayarkan.</p>
      </div>
      <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
        <div class="relative flex-1 min-w-[200px] sm:max-w-xs">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="w-4 h-4" data-lucide="search"></i></span>
          <input class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-slate-700 bg-white shadow-xs" id="deposit-search" oninput="resetDepositPagination()" placeholder="Cari transaksi..." type="text"/>
        </div>
        <select class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 min-w-[160px]" id="deposit-stage-filter" onchange="changeStageFilter(this.value)"></select>
      </div>
    </div>
    <div class="overflow-x-auto custom-scrollbar -mx-6 sm:mx-0">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="text-slate-400 font-bold border-b border-slate-100 text-[11px] uppercase tracking-wider bg-slate-50/50">
            <th class="p-4 pl-6 w-14 text-center">No</th><th class="p-4">Tanggal</th><th class="p-4">Bulan Pertemuan Ke</th><th class="p-4">Jumlah Setor</th><th class="p-4">Penerima (Petugas)</th><th class="p-4">Catatan</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100" id="deposit-table-body"></tbody>
      </table>
    </div>
    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100 pt-4">
      <div class="flex items-center gap-2 text-xs text-slate-500">
        <span class="font-semibold">Tampilkan</span>
        <select class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10" id="deposit-page-size" onchange="changeDepositPageSize(this.value)">
          <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
        </select>
        <span class="font-semibold">data</span>
      </div>
      <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <span class="text-[11px] font-semibold text-slate-500" id="deposit-page-info">Menampilkan 0 data</span>
        <div class="flex items-center gap-1.5" id="deposit-pagination-controls"></div>
      </div>
    </div>
  </div>
</section>
<?php include '../includes/modals.php'; include '../includes/footer.php'; ?>
