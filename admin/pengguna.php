<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header('Location: ../index.php'); exit; }
$page = 'pengguna';
include '../includes/head.php';
include '../includes/sidebar_admin.php';
include '../includes/topbar.php';
?>
<section class="space-y-6" id="tab-pengguna" data-page="pengguna">
  <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 shrink-0">
      <div>
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2"><i class="w-4.5 h-4.5 text-emerald-600" data-lucide="users"></i><span>Manajemen Pengguna</span></h3>
        <p class="text-[10px] text-slate-400 mt-1">Kelola akun pengguna (admin & shohibul) beserta username dan password.</p>
      </div>
      <div class="flex items-center gap-2">
        <input class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 min-w-[200px]" id="user-search" oninput="resetUserPagination()" placeholder="Cari nama/username..." type="text"/>
        <button class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5 transition shadow-md shadow-emerald-500/10" onclick="openAddUser()"><i class="w-3.5 h-3.5" data-lucide="plus"></i> Tambah Pengguna</button>
      </div>
    </div>
    <div class="overflow-x-auto custom-scrollbar -mx-6 sm:mx-0">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="text-slate-400 font-bold border-b border-slate-100 text-[11px] uppercase tracking-wider bg-slate-50/50">
            <th class="p-4 pl-6 w-12 text-center">No</th>
            <th class="p-4">Foto</th>
            <th class="p-4">Nama</th>
            <th class="p-4">Username</th>
            <th class="p-4">Password</th>
            <th class="p-4">Role</th>
            <th class="p-4 pr-6 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100" id="user-table-body"></tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal Pengguna -->
<div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[9998] flex items-center justify-center hidden" id="user-modal">
  <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-slate-100">
    <h3 class="text-base font-bold text-slate-900 mb-4" id="user-modal-title">Form Pengguna</h3>
    <form class="space-y-4" id="user-form" onsubmit="handleUserSubmit(event)" enctype="multipart/form-data">
      <input id="edit-user-id" type="hidden"/>
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-lg flex-shrink-0 overflow-hidden" id="user-avatar-preview">?</div>
        <div class="flex-1">
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Foto Profil</label>
          <input class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" id="user-foto" type="file" accept="image/*" onchange="previewUserAvatar(this)"/>
          <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengubah foto.</p>
        </div>
      </div>
      <div id="user-nama-wrapper">
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
        <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="user-nama" placeholder="Nama lengkap" required type="text"/>
      </div>
      <div id="user-anggota-wrapper" class="hidden">
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pilih Anggota</label>
        <select class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="user-anggota-select" onchange="onUserAnggotaChange()">
          <option value="">-- Pilih Anggota --</option>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Username</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="user-username" placeholder="username" required type="text"/>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Role</label>
          <select class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="user-role" onchange="onUserRoleChange()">
            <option value="shohibul">Shohibul</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
        <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="user-password" placeholder="Kosongkan jika tidak diubah" type="password"/>
      </div>
      <input id="user-foto-path" type="hidden"/>
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
        <button class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-200 transition" onclick="closeModal('user-modal')" type="button">Batal</button>
        <button class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl transition shadow-md shadow-emerald-500/10" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/modals.php'; include '../includes/footer.php'; ?>
