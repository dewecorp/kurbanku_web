<aside class="fixed md:static inset-y-0 left-0 w-64 bg-emerald-950 text-white flex flex-col shrink-0 shadow-xl z-40 transition-transform duration-300 -translate-x-full md:translate-x-0 no-print overflow-y-auto custom-scrollbar" id="app-sidebar">
  <div class="p-6 border-b border-emerald-900/60 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-3">
      <div class="bg-emerald-800 p-2.5 rounded-xl flex items-center justify-center text-amber-300 shadow-md">
        <i class="fa-solid fa-kaaba text-lg"></i>
      </div>
      <div>
        <h1 class="font-bold text-base leading-tight tracking-wide">Qurban<span class="text-amber-300">Ku</span></h1>
        <span class="text-[10px] text-emerald-400 font-medium uppercase tracking-wider">Manajer Tabungan</span>
      </div>
    </div>
    <button class="md:hidden text-emerald-300 hover:text-white p-1 rounded-lg hover:bg-emerald-900/50" onclick="toggleMobileMenu()"><i class="w-5 h-5" data-lucide="x"></i></button>
  </div>
  <nav class="flex-1 p-4 flex flex-col gap-1.5">
    <?php
    $current = basename($_SERVER['PHP_SELF']);
    function navItemAnggota($href, $icon, $label, $isCurrent) {
      $active = $isCurrent ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-900/50';
      return "<a href=\"$href\" class=\"flex items-center gap-3.5 px-4 py-3 rounded-xl text-xs font-semibold transition $active w-full\"><i class=\"w-4.5 h-4.5 shrink-0\" data-lucide=\"$icon\"></i><span>$label</span></a>";
    }
    echo navItemAnggota('dashboard.php', 'layout-dashboard', 'Dashboard Anggota', $current === 'dashboard.php');
    echo navItemAnggota('rekap.php', 'table-properties', 'Rekap Setoran Saya', $current === 'rekap.php');
    echo navItemAnggota('riwayat.php', 'receipt-text', 'Riwayat Setoran', $current === 'riwayat.php');
    ?>
  </nav>
</aside>
<div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-30 hidden md:hidden" id="sidebar-backdrop" onclick="toggleMobileMenu()"></div>
