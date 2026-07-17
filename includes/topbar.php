<!-- MAIN CONTENT AREA -->
<main class="flex-1 flex flex-col min-w-0 overflow-y-auto custom-scrollbar relative">
  <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-xs border-b border-slate-200/80 px-6 py-4 flex items-center justify-between no-print shadow-xs shrink-0">
    <div class="flex items-center gap-3">
      <button class="md:hidden text-slate-700 hover:text-slate-900 p-1.5 rounded-lg hover:bg-slate-100" onclick="toggleMobileMenu()"><i class="w-5 h-5" data-lucide="menu"></i></button>
      <div>
        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600" id="header-subtitle">Ringkasan Tabungan</span>
        <h2 class="text-lg font-bold text-slate-900 leading-tight" id="header-title">Dashboard Utama</h2>
      </div>
    </div>
    <div class="hidden lg:flex items-center gap-2" id="header-tahap-badge"></div>
    <div class="flex items-center gap-4">
      <div class="text-right hidden md:block mr-1">
        <p class="text-xs font-bold text-slate-800" id="header-clock-date">-</p>
        <p class="text-[10px] text-slate-400 font-medium" id="header-clock-time">-</p>
      </div>
      <div class="text-right hidden sm:block">
        <p class="text-xs font-bold text-slate-800" id="header-user-display">Mengambil data...</p>
        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider" id="header-user-role">Sesi Pengguna</p>
      </div>
      <button type="button" onclick="confirmLogout()" class="px-3 py-2 text-xs font-bold rounded-xl text-red-600 hover:bg-red-50 transition flex items-center gap-1.5 border border-transparent hover:border-red-100">
        <i class="w-4 h-4" data-lucide="log-out"></i>
        <span class="hidden sm:inline">Log Out</span>
      </button>
    </div>
  </header>
  <div class="flex-1 p-6 overflow-x-hidden">
