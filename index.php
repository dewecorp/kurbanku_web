<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <title>QurbanKu - Sistem Manajemen Tabungan Qurban</title>
  <script src="assets/css/tailwind.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="assets/fontawesome/css/all.min.css" rel="stylesheet"/>
  <script src="assets/js/vendor/lucide.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">
<?php
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-200/60 overflow-hidden">
  <div class="bg-gradient-to-br from-emerald-800 to-emerald-950 p-8 text-center text-white relative">
    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 text-white shadow-lg mx-auto mb-3 backdrop-blur-md">
      <i class="fa-solid fa-kaaba text-2xl text-amber-300"></i>
    </div>
    <h2 class="text-2xl font-extrabold tracking-tight">Qurban<span class="text-amber-300">Ku</span></h2>
    <p class="text-xs text-emerald-200 font-semibold uppercase tracking-widest mt-1">Sistem Tabungan Qurban</p>
    <p class="text-sm text-amber-300 font-bold mt-1.5">Keluarga Besar H. Dimyati</p>
    <div class="absolute -bottom-1 left-0 right-0 h-4 bg-white rounded-t-3xl"></div>
  </div>
  <div class="p-8 pt-4">
    <?php if ($error): ?>
      <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-xs font-semibold"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="flex p-1 bg-slate-100 rounded-2xl border border-slate-200/80 mb-6">
      <button class="flex-1 py-2 text-xs font-bold rounded-xl bg-white text-slate-950 shadow-sm transition duration-200" id="tab-login-admin" onclick="switchLoginTab('admin')" type="button">
        <i class="fa-solid fa-user-shield mr-1.5 text-emerald-600"></i> Pengurus
      </button>
      <button class="flex-1 py-2 text-xs font-semibold text-slate-500 hover:text-slate-900 rounded-xl transition duration-200" id="tab-login-peserta" onclick="switchLoginTab('peserta')" type="button">
        <i class="fa-solid fa-user mr-1.5"></i> Shohibul
      </button>
    </div>

    <form class="space-y-4" id="form-login-admin" method="post" action="auth.php">
      <input type="hidden" name="role" value="admin"/>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Username Pengurus</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"><i class="fa-solid fa-user-tie text-xs"></i></span>
          <input class="w-full pl-10 pr-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 bg-white" name="username" placeholder="admin" required type="text"/>
        </div>
      </div>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">PIN Keamanan</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"><i class="fa-solid fa-lock text-xs"></i></span>
          <input class="w-full pl-10 pr-10 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 bg-white" name="pin" placeholder="PIN Default (admin2026)" required type="password" id="login-admin-pass"/>
          <button class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-650" onclick="togglePass('login-admin-pass')" type="button"><i class="fa-solid fa-eye text-xs" id="eye-icon-login-admin-pass"></i></button>
        </div>
      </div>
      <button class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition flex items-center justify-center gap-2" type="submit">
        <span>Masuk Portal Pengurus</span> <i class="w-4 h-4" data-lucide="arrow-right"></i>
      </button>
    </form>

    <form class="hidden space-y-4" id="form-login-peserta" method="post" action="auth.php">
      <input type="hidden" name="role" value="shohibul"/>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pilih Nama Shohibul</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"><i class="fa-solid fa-circle-user text-xs"></i></span>
          <select class="w-full pl-10 pr-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 bg-white" name="username" id="login-peserta-select" required>
            <option value="">-- Pilih Nama Anda --</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">PIN Anggota</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"><i class="fa-solid fa-lock text-xs"></i></span>
          <input class="w-full pl-10 pr-10 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 bg-white" name="pin" placeholder="PIN default (1234)" required type="password" id="login-peserta-pass"/>
          <button class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-650" onclick="togglePass('login-peserta-pass')" type="button"><i class="fa-solid fa-eye text-xs" id="eye-icon-login-peserta-pass"></i></button>
        </div>
      </div>
      <button class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition flex items-center justify-center gap-2" type="submit">
        <span>Masuk Portal Shohibul</span> <i class="w-4 h-4" data-lucide="arrow-right"></i>
      </button>
    </form>

    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
      <p class="text-[10px] text-slate-400 font-medium leading-relaxed">
        Jika belum terdaftar atau lupa PIN, silakan hubungi Pengurus Arisan untuk pendaftaran data akun Anda.
      </p>
    </div>
  </div>
</div>

<script>
const API_URL = "api.php";

function switchLoginTab(role) {
  const tabAdmin = document.getElementById("tab-login-admin");
  const tabPeserta = document.getElementById("tab-login-peserta");
  const formAdmin = document.getElementById("form-login-admin");
  const formPeserta = document.getElementById("form-login-peserta");
  if (role === "admin") {
    tabAdmin.className = "flex-1 py-2 text-xs font-bold rounded-xl bg-white text-slate-950 shadow-sm transition duration-200";
    tabPeserta.className = "flex-1 py-2 text-xs font-semibold text-slate-500 hover:text-slate-900 rounded-xl transition duration-200";
    formAdmin.classList.remove("hidden");
    formPeserta.classList.add("hidden");
  } else {
    tabAdmin.className = "flex-1 py-2 text-xs font-semibold text-slate-500 hover:text-slate-900 rounded-xl transition duration-200";
    tabPeserta.className = "flex-1 py-2 text-xs font-bold rounded-xl bg-white text-slate-950 shadow-sm transition duration-200";
    formAdmin.classList.add("hidden");
    formPeserta.classList.remove("hidden");
  }
}

function togglePass(id) {
  const input = document.getElementById(id);
  const icon = document.getElementById("eye-icon-" + id);
  if (input.type === "password") { input.type = "text"; icon.className = "fa-solid fa-eye-slash text-xs"; }
  else { input.type = "password"; icon.className = "fa-solid fa-eye text-xs"; }
}

async function loadAnggota() {
  try {
    const res = await fetch(API_URL + "?action=getData&cache=" + Date.now());
    const data = await res.json();
    const source = data.data || data;
    const anggota = source.anggota || source.participants || [];
    const select = document.getElementById("login-peserta-select");
    select.innerHTML = "<option value=''>-- Pilih Nama Anda --</option>";
    anggota.sort(function(a,b){ return (a.nama||a.name||"").localeCompare(b.nama||b.name||"","id-ID"); }).forEach(function(a){
      const opt = document.createElement("option");
      opt.value = a.id;
      opt.innerText = a.nama || a.name || "";
      select.appendChild(opt);
    });
  } catch(e) { console.error("Gagal memuat data anggota", e); }
}

document.addEventListener("DOMContentLoaded", function() {
  if (window.lucide) lucide.createIcons();
  loadAnggota();
});
</script>
</body>
</html>
