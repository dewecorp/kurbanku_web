<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header('Location: ../index.php'); exit; }
$page = 'rekap';
include '../includes/head.php';
include '../includes/sidebar_admin.php';
include '../includes/topbar.php';
?>
<section class="space-y-6" id="tab-rekap">
  <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 shrink-0">
      <div>
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2"><i class="w-4.5 h-4.5 text-emerald-600" data-lucide="table-properties"></i><span>Matrix Rekapitulasi Setoran Bulanan (Bulan 1 - 12)</span></h3>
        <p class="text-[10px] text-slate-400 mt-1">Visualisasi progres kepatuhan setoran tabungan shohibul per bulan pertemuan.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <select class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 min-w-[160px]" id="rekap-stage-filter" onchange="changeStageFilter(this.value)"></select>
        <button class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5 transition shadow-md shadow-emerald-500/10" onclick="exportRekapExcel()"><i class="w-3.5 h-3.5" data-lucide="file-spreadsheet"></i> Ekspor Excel</button>
        <button class="px-3 py-2 border border-slate-200 hover:border-slate-300 text-slate-600 text-xs font-semibold rounded-xl flex items-center gap-1.5 transition bg-white" onclick="printRekapReport()"><i class="w-3.5 h-3.5" data-lucide="printer"></i> Cetak Rekap Rapi</button>
      </div>
    </div>
    <div class="overflow-x-auto custom-scrollbar -mx-6 sm:mx-0 border border-slate-100 rounded-xl">
      <table class="w-full text-center text-xs border-collapse min-w-[1000px]">
        <thead id="rekap-table-head"></thead>
        <tbody class="divide-y divide-slate-100" id="rekap-table-body"></tbody>
      </table>
    </div>
  </div>
</section>
<?php include '../includes/modals.php'; include '../includes/footer.php'; ?>
