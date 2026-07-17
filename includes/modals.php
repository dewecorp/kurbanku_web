<!-- MODAL CONFIRM -->
<div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center hidden" id="confirm-modal" onclick="if(event.target===this)closeModal('confirm-modal')">
  <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-xl border border-slate-100">
    <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-500 mb-4 mx-auto">
      <i class="fa-solid fa-circle-question text-xl"></i>
    </div>
    <h3 class="text-base font-bold text-slate-900 mb-1 text-center" id="modal-title">Konfirmasi Tindakan</h3>
    <p class="text-slate-500 text-xs mb-6 text-center leading-relaxed" id="modal-message">Apakah Anda yakin ingin melakukan tindakan ini?</p>
    <div class="flex gap-3">
      <button class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition" id="modal-btn-cancel" type="button" onclick="closeModal('confirm-modal')">Batal</button>
      <button class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition shadow-md shadow-emerald-500/10" id="modal-btn-confirm" type="button">Ya, Lanjutkan</button>
    </div>
  </div>
</div>

<!-- MODAL TAHAP -->
<div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[9998] flex items-center justify-center hidden" id="tahap-modal">
  <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-slate-100">
    <h3 class="text-base font-bold text-slate-900 mb-4" id="tahap-modal-title">Form Tahap Qurban</h3>
    <form class="space-y-4" id="tahap-form" onsubmit="handleTahapSubmit(event)">
      <input id="edit-tahap-id" type="hidden"/>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Periode/Tahap</label>
        <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="tahap-name" placeholder="Contoh: Qurban 1447 H / 2026 M" required type="text"/>
      </div>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status Default</label>
        <select class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="tahap-status">
          <option value="Aktif">Aktif (Tahap Saat Ini)</option>
          <option value="Nonaktif">Nonaktif / Diarsipkan</option>
        </select>
      </div>
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
        <button class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-200 transition" onclick="closeModal('tahap-modal')" type="button">Batal</button>
        <button class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl transition shadow-md shadow-emerald-500/10" type="submit">Simpan Tahap</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL KELOMPOK -->
<div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[9998] flex items-center justify-center hidden" id="kelompok-modal">
  <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-slate-100">
    <h3 class="text-base font-bold text-slate-900 mb-4" id="kelompok-modal-title">Form Kelompok Qurban</h3>
    <form class="space-y-4" id="group-form" onsubmit="handleGroupSubmit(event)">
      <input id="edit-group-id" type="hidden"/>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Kelompok</label>
        <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="group-name" placeholder="Contoh: Kelompok Sapi A" required type="text"/>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jenis Hewan</label>
          <select class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="group-type" onchange="suggestTargetPrice()">
            <option value="Sapi">Sapi (7 Orang)</option>
            <option value="Kambing">Kambing (1 Orang)</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Target Total (Rp)</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="group-target" placeholder="Contoh: 21000000" required type="number"/>
        </div>
      </div>
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
        <button class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-200 transition" onclick="closeModal('kelompok-modal')" type="button">Batal</button>
        <button class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl transition shadow-md shadow-emerald-500/10" type="submit">Simpan Kelompok</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL ANGGOTA -->
<div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[9998] flex items-center justify-center hidden" id="anggota-modal">
  <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-slate-100">
    <h3 class="text-base font-bold text-slate-900 mb-4" id="anggota-modal-title">Form Anggota Shohibul</h3>
    <form class="space-y-4" id="participant-form" onsubmit="handleParticipantSubmit(event)">
      <input id="edit-participant-id" type="hidden"/>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap Shohibul</label>
        <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="participant-name" placeholder="Contoh: Ahmad Sulaiman" required type="text"/>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nomor WhatsApp</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="participant-whatsapp" placeholder="0812..." type="text"/>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">PIN Keamanan Akun</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="participant-pin" placeholder="Default (1234)" type="text"/>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Kelompok</label>
          <select class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="participant-group">
            <option value="">-- Tanpa Kelompok --</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Target Tabungan (Rp)</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="participant-target" placeholder="Contoh: 3.000.000" required type="text" inputmode="numeric" oninput="formatMoneyInput(this)"/>
        </div>
      </div>
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
        <button class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-200 transition" onclick="closeModal('anggota-modal')" type="button">Batal</button>
        <button class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl transition shadow-md shadow-emerald-500/10" type="submit">Simpan Anggota</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL SETORAN -->
<div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[9998] flex items-center justify-center hidden" id="setoran-modal">
  <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl border border-slate-100">
    <h3 class="text-base font-bold text-slate-900 mb-4" id="setoran-modal-title">Form Transaksi Setoran</h3>
    <form class="space-y-4" id="deposit-form" onsubmit="handleDepositSubmit(event)">
      <input id="edit-deposit-id" type="hidden"/>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pilih Shohibul</label>
        <select class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white shadow-xs" id="deposit-participant" required>
          <option value="">-- Pilih Anggota --</option>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Setor</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="deposit-date" required type="date" lang="id"/>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nominal Setoran (Rp)</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="deposit-amount" inputmode="numeric" oninput="formatMoneyInput(this)" placeholder="Contoh: 250.000" required type="text"/>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pertemuan Ke (Bulan)</label>
          <select class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="deposit-bulan" required>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Penerima Uang (Petugas)</label>
          <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="deposit-recorded" required type="text"/>
        </div>
      </div>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Catatan Tambahan</label>
        <input class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition text-xs text-slate-700 bg-white" id="deposit-note" placeholder="Contoh: Pembayaran cicilan ke-3" type="text"/>
      </div>
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
        <button class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-200 transition" onclick="closeModal('setoran-modal')" type="button">Batal</button>
        <button class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl transition shadow-md shadow-emerald-500/10" type="submit">Simpan Setoran</button>
      </div>
    </form>
  </div>
</div>
