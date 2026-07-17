// QurbanKu - minimal app.js
(function () {
  'use strict';

  var state = {
    user: null,
    data: { tahaps: [], groups: [], participants: [], deposits: [], settings: {} },
    currentTahapId: null
  };

  // ---------- helpers ----------
  function $(sel) { return document.querySelector(sel); }
  function el(id) { return document.getElementById(id); }

  function fmtMoney(n) {
    n = Number(n) || 0;
    return 'Rp ' + n.toLocaleString('id-ID');
  }

  function fmtShort(n) {
    n = Number(n) || 0;
    if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1).replace(/\.0$/, '') + ' Jt';
    if (n >= 1000) return 'Rp ' + Math.round(n / 1000) + ' Rb';
    return 'Rp ' + n;
  }

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function toast(msg, type) {
    if (!document.getElementById('toast-anim-style')) {
      var st = document.createElement('style');
      st.id = 'toast-anim-style';
      st.textContent = '@keyframes toastIn{from{opacity:0;transform:translateX(40px) scale(.96)}to{opacity:1;transform:translateX(0) scale(1)}}' +
        '@keyframes toastOut{from{opacity:1;transform:translateX(0) scale(1)}to{opacity:0;transform:translateX(40px) scale(.96)}}' +
        '.qk-toast{animation:toastIn .35s cubic-bezier(.21,1.02,.73,1) both;}' +
        '.qk-toast.hiding{animation:toastOut .3s ease forwards;}';
      document.head.appendChild(st);
    }
    var c = el('toast-container');
    if (!c) {
      c = document.createElement('div');
      c.id = 'toast-container';
      c.style.cssText = 'position:fixed;top:20px;right:20px;z-index:2147483647;display:flex;flex-direction:column;gap:8px;pointer-events:none;';
      document.body.appendChild(c);
    }
    var palette = {
      success: { bg: 'linear-gradient(135deg,#059669,#10b981)', icon: '✓' },
      error: { bg: 'linear-gradient(135deg,#dc2626,#ef4444)', icon: '✕' },
      info: { bg: 'linear-gradient(135deg,#334155,#475569)', icon: 'ℹ' }
    };
    var p = palette[type] || palette.info;
    var t = document.createElement('div');
    t.className = 'qk-toast';
    t.style.cssText = 'pointer-events:auto;color:#fff;font:600 13px/1.4 system-ui,sans-serif;background:' + p.bg +
      ';padding:12px 16px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.28);max-width:320px;display:flex;align-items:center;gap:10px;';
    var ic = document.createElement('span');
    ic.textContent = p.icon;
    ic.style.cssText = 'font-weight:800;width:20px;height:20px;flex-shrink:0;border-radius:50%;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:12px;';
    var tx = document.createElement('span');
    tx.textContent = msg || '';
    t.appendChild(ic);
    t.appendChild(tx);
    c.appendChild(t);
    setTimeout(function () {
      t.classList.add('hiding');
      setTimeout(function () { t.remove(); }, 320);
    }, 4000);
  }

  function openModal(id) { var m = el(id); if (m) m.classList.remove('hidden'); }

  window.confirmLogout = function () {
    var title = el('modal-title'), msg = el('modal-message'), confirmBtn = el('modal-btn-confirm');
    if (!title || !msg || !confirmBtn) { window.location.href = '../logout.php'; return; }
    title.textContent = 'Konfirmasi Log Out';
    msg.textContent = 'Apakah Anda yakin ingin keluar dari sesi ini?';
    confirmBtn.textContent = 'Ya, Log Out';
    openModal('confirm-modal');
    var onConfirm = function () {
      window.location.href = '../logout.php';
    };
    confirmBtn.onclick = function () {
      confirmBtn.onclick = null;
      onConfirm();
    };
  };
  function closeModal(id) { var m = el(id); if (m) m.classList.add('hidden'); }
  window.closeModal = closeModal;
  window.openModal = openModal;

  function toggleMobileMenu() {
    var s = el('app-sidebar');
    if (!s) return;
    s.classList.toggle('-translate-x-full');
    var b = el('sidebar-backdrop');
    if (b) b.classList.toggle('hidden');
  }
  window.toggleMobileMenu = toggleMobileMenu;

  function setCloud(text, ok) {
    var t = el('cloud-indicator-text');
    var d = el('cloud-indicator-dot');
    if (t) t.textContent = text;
    if (d) d.className = 'w-2 h-2 rounded-full ' + (ok ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500');
  }

  // ---------- data layer ----------
  function apiBase() {
    var origin = window.location.origin || '';
    var p = (window.location.pathname || '/').replace(/\\/g, '/');
    // buang nama file terakhir
    var i = p.lastIndexOf('/');
    var dir = p.substring(0, i); // mis. /qurbanku_web/admin
    // buang folder admin/anggota jika ada
    if (dir.indexOf('/admin') === dir.length - 6) dir = dir.substring(0, dir.length - 6);
    if (dir.indexOf('/anggota') === dir.length - 8) dir = dir.substring(0, dir.length - 8);
    if (!dir) dir = '';
    return origin + dir + '/api.php';
  }
  function api(path, opts) {
    var url = apiBase() + path;
    console.log('API FETCH ->', url);
    return fetch(url, Object.assign({ credentials: 'same-origin' }, opts))
      .then(function (r) {
        return r.text().then(function (txt) {
          console.log('API RESP first 60:', txt.slice(0, 60));
          try { return JSON.parse(txt); }
          catch (e) { throw new Error('Bukan JSON (dapat HTML?): ' + txt.slice(0, 40)); }
        });
      });
  }

  function loadData() {
    setCloud('Memuat data...', false);
    return api('?action=getData').then(function (res) {
      if (res.status !== 'success' || !res.data) throw new Error('no data');
      state.data = {
        tahaps: res.data.tahap || [],
        groups: res.data.kelompok || [],
        participants: res.data.anggota || [],
        deposits: res.data.setoran || [],
        settings: res.settings || {}
      };
      state.currentTahapId = res.currentTahapId || (state.data.tahaps[0] && state.data.tahaps[0].id) || null;
      state.settings = res.settings || {};
      setCloud('Tersambung', true);
      return res;
    });
  }

  function save(payload) {
    return api('', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function (res) {
      if (res.status !== 'success') throw new Error(res.message || 'gagal');
      return res;
    });
  }

  // ---------- compute ----------
  // Anggota & kelompok: TETAP di semua tahap (tidak di-reset saat ganti tahap)
  // Setoran & rekap: per tahap aktif (reset saat ganti tahap)
  function activeData() {
    var groups = state.data.groups.slice();
    var parts = state.data.participants.slice();
    var tId = state.currentTahapId;
    var deps = state.data.deposits.filter(function (d) { return !tId || !d.tahapId || d.tahapId === tId; });
    return { groups: groups, parts: parts, deps: deps };
  }

  function allDeposits() {
    return state.data.deposits.slice();
  }

  function depositSum(partId, deps) {
    return deps.filter(function (d) { return d.anggota_id === partId; })
      .reduce(function (s, d) { return s + (Number(d.jumlah) || 0); }, 0);
  }

  function progressOf(ids, deps) {
    return ids.reduce(function (s, id) { return s + depositSum(id, deps); }, 0);
  }

  // ---------- render: dashboard ----------
  function renderDashboard() {
    var ad = activeData();
    var parts = ad.parts, deps = ad.deps, groups = ad.groups;
    var isShohibul = state.user && state.user.role === 'shohibul';
    var me = null;
    if (isShohibul) {
      me = parts.find(function (p) { return String(p.id) === String(state.user.participantId); }) || null;
    }

    // hero (anggota)
    var hero = el('dashboard-hero');
    if (hero) {
      if (isShohibul && me) {
        hero.innerHTML =
          '<div class="bg-gradient-to-br from-emerald-900 to-emerald-700 rounded-2xl p-6 text-white shadow-lg">' +
            '<p class="text-emerald-200/80 text-sm font-medium">Assalamu\'alaikum Warahmatullahi Wabarakatuh</p>' +
            '<h2 class="text-2xl font-extrabold mt-1">Selamat datang, ' + esc(me.nama) + '</h2>' +
            '<p class="text-emerald-100/80 text-xs mt-1">Keluarga Besar H. Dimyati &middot; Semoga ibadah qurban kita diterima Allah SWT.</p>' +
          '</div>';
      } else {
        hero.innerHTML = '';
      }
    }

    // stats
    var statBox = el('dashboard-stats');
    if (statBox) {
      if (isShohibul && me) {
        var myTarget = Number(me.target_saving) || 0;
        var myAll = deps.filter(function (d) { return String(d.anggota_id) === String(me.id); });
        var mySetor = myAll.reduce(function (s, d) { return s + (Number(d.jumlah) || 0); }, 0);
        var myMonthsPaid = myAll.filter(function (d) { return d.bulan >= 1 && d.bulan <= 12; })
          .reduce(function (acc, d) { acc[d.bulan] = true; return acc; }, {});
        var myMonthsCount = Object.keys(myMonthsPaid).length;
        var bulanBerjalan = myAll.reduce(function (m, d) { return Math.max(m, Number(d.bulan) || 0); }, 0);
        var myTunggak = bulanBerjalan > 0 ? Math.max(0, bulanBerjalan - myMonthsCount) : 0;
        var myPct = myTarget > 0 ? Math.min(100, Math.round((mySetor / myTarget) * 100)) : 0;
        statBox.innerHTML =
          statCard('Setoran Saya', fmtMoney(mySetor), 'wallet', 'text-sky-600', 'bg-sky-50') +
          statCard('Target Tabungan', fmtMoney(myTarget), 'target', 'text-violet-600', 'bg-violet-50') +
          statCard('Tunggakan/Belum Bayar', myTunggak + ' bln', 'alert-triangle', 'text-rose-600', 'bg-rose-50') +
          statCard('Progress', myPct + '%', 'check-circle', 'text-amber-600', 'bg-amber-50');
      } else {
        var totalTarget = parts.reduce(function (s, p) { return s + (Number(p.target_saving) || 0); }, 0);
        var totalSetor = progressOf(parts.map(function (p) { return p.id; }), allDeposits());
        var lunas = parts.filter(function (p) {
          return (Number(p.target_saving) || 0) > 0 && depositSum(p.id, deps) >= Number(p.target_saving);
        }).length;
        statBox.innerHTML =
          statCard('Total Shohibul', parts.length, 'users', 'text-emerald-600', 'bg-emerald-50') +
          statCard('Total Setoran', fmtMoney(totalSetor), 'wallet', 'text-sky-600', 'bg-sky-50') +
          statCard('Target Tabungan', fmtMoney(totalTarget), 'target', 'text-violet-600', 'bg-violet-50') +
          statCard('Lunas', lunas + ' / ' + parts.length, 'check-circle', 'text-amber-600', 'bg-amber-50');
      }
    }

    // group progress
    var gp = el('dashboard-group-progress');
    if (gp) {
      if (!groups.length) {
        gp.innerHTML = emptyMsg('Belum ada kelompok pada tahap aktif.');
      } else {
        gp.innerHTML = groups.map(function (g) {
          var grpParts = parts.filter(function (p) { return p.kelompok_id === g.id; });
          var ids = grpParts.map(function (p) { return p.id; });
          var t = grpParts.reduce(function (s, p) { return s + (Number(p.target_saving) || 0); }, 0);
          var cur = progressOf(ids, deps);
          var pct = t > 0 ? Math.min(100, Math.round((cur / t) * 100)) : 0;
          return '<div class="bg-slate-50/60 p-4 rounded-xl border border-slate-100">' +
            '<div class="flex justify-between items-center mb-1"><span class="text-xs font-bold text-slate-800">' + esc(g.nama) + '</span>' +
            '<span class="text-[10px] font-bold text-emerald-600">' + pct + '%</span></div>' +
            '<div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:' + pct + '%"></div></div>' +
            '<div class="flex justify-between text-[10px] text-slate-400 mt-1"><span>' + fmtMoney(cur) + '</span><span>' + fmtMoney(t) + '</span></div>' +
            '</div>';
        }).join('');
      }
    }

    // personal card
    renderPersonalCard(parts, deps);
  }

  function statCard(label, val, icon, color, bg) {
    return '<div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">' +
      '<div class="flex items-center justify-between"><span class="text-[11px] font-bold text-slate-400 uppercase">' + label + '</span>' +
      '<span class="w-8 h-8 rounded-lg ' + bg + ' flex items-center justify-center"><i class="w-4 h-4 ' + color + '" data-lucide="' + icon + '"></i></span></div>' +
      '<p class="text-xl font-extrabold text-slate-900 mt-2">' + esc(val) + '</p></div>';
  }

  function renderPersonalCard(parts, deps) {
    var box = el('personal-card-details');
    if (!box) return;
    var allDeps = allDeposits();
    if (state.user && state.user.role === 'shohibul') {
      var me = parts.find(function (p) { return String(p.id) === String(state.user.participantId); });
      if (!me) { box.innerHTML = emptyMsg('Data anggota tidak ditemukan.'); return; }
      var t = Number(me.target_saving) || 0;
      var cur = depositSum(me.id, allDeps);
      var pct = t > 0 ? Math.min(100, Math.round((cur / t) * 100)) : 0;
      box.innerHTML =
        '<div class="flex justify-between text-xs"><span class="text-slate-500">Nama</span><span class="font-bold text-slate-800">' + esc(me.nama) + '</span></div>' +
        '<div class="flex justify-between text-xs"><span class="text-slate-500">Terkumpul</span><span class="font-bold text-emerald-600">' + fmtMoney(cur) + '</span></div>' +
        '<div class="flex justify-between text-xs"><span class="text-slate-500">Target</span><span class="font-bold text-slate-800">' + fmtMoney(t) + '</span></div>' +
        '<div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:' + pct + '%"></div></div>' +
        '<p class="text-[10px] text-slate-400 text-center">' + pct + '% tercapai</p>';
    } else {
      var totalTarget = parts.reduce(function (s, p) { return s + (Number(p.target_saving) || 0); }, 0);
      var totalSetor = progressOf(parts.map(function (p) { return p.id; }), allDeps);
      var pct = totalTarget > 0 ? Math.round((totalSetor / totalTarget) * 100) : 0;
      box.innerHTML =
        '<div class="flex justify-between text-xs"><span class="text-slate-500">Terkumpul</span><span class="font-bold text-emerald-600">' + fmtMoney(totalSetor) + '</span></div>' +
        '<div class="flex justify-between text-xs"><span class="text-slate-500">Target</span><span class="font-bold text-slate-800">' + fmtMoney(totalTarget) + '</span></div>' +
        '<div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:' + pct + '%"></div></div>' +
        '<p class="text-[10px] text-slate-400 text-center">' + pct + '% progres keseluruhan</p>';
    }
  }

  function emptyMsg(m) { return '<div class="col-span-full text-center text-xs text-slate-400 py-6">' + esc(m) + '</div>'; }

  // ---------- render: tahap ----------
  function renderTahap() {
    var tb = el('tahap-table-body');
    if (!tb) return;
    if (!state.data.tahaps.length) { tb.innerHTML = emptyMsg('Belum ada tahap.'); return; }
    tb.innerHTML = state.data.tahaps.map(function (t) {
      var active = t.id === state.currentTahapId;
      return '<tr class="hover:bg-slate-50/60">' +
        '<td class="p-4 pl-6 font-mono text-[11px] text-slate-400">' + esc(t.id) + '</td>' +
        '<td class="p-4 font-semibold text-slate-800">' + esc(t.nama) + '</td>' +
        '<td class="p-4">' + (active ? '<span class="text-[10px] font-bold px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full">AKTIF</span>' : '<span class="text-[10px] font-bold px-2 py-1 bg-slate-100 text-slate-500 rounded-full">Nonaktif</span>') + '</td>' +
        '<td class="p-4 pr-6 text-right"><div class="flex justify-end gap-1.5">' +
        '<button class="px-2.5 py-1.5 text-xs rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold" onclick="openEditTahap(\'' + esc(t.id) + '\')">Edit</button>' +
        '<button class="px-2.5 py-1.5 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold" onclick="confirmDelete(\'deleteTahap\',\'' + esc(t.id) + '\',\'' + esc(t.nama) + '\')">Hapus</button>' +
        '</div></td></tr>';
    }).join('');
  }

  // ---------- render: kelompok ----------
  function renderGroups() {
    var tb = el('group-table-body');
    if (!tb) return;
    var ad = activeData();
    var groups = ad.groups;
    if (!groups.length) { tb.innerHTML = emptyMsg('Belum ada kelompok pada tahap aktif.'); return; }
    tb.innerHTML = groups.map(function (g) {
      var count = ad.parts.filter(function (p) { return p.kelompok_id === g.id; }).length;
      return '<tr class="hover:bg-slate-50/60">' +
        '<td class="p-4 pl-6 font-semibold text-slate-800">' + esc(g.nama) + '</td>' +
        '<td class="p-4"><span class="text-[10px] font-bold px-2 py-1 bg-slate-100 text-slate-600 rounded-full">' + esc(g.tipe || '-') + '</span></td>' +
        '<td class="p-4 font-semibold text-slate-700">' + fmtMoney(g.target) + '</td>' +
        '<td class="p-4 text-slate-600">' + count + ' orang</td>' +
        '<td class="p-4 pr-6 text-right"><div class="flex justify-end gap-1.5">' +
        '<button class="px-2.5 py-1.5 text-xs rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold" onclick="openEditGroup(\'' + esc(g.id) + '\')">Edit</button>' +
        '<button class="px-2.5 py-1.5 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold" onclick="confirmDelete(\'deleteKelompok\',\'' + esc(g.id) + '\',\'' + esc(g.nama) + '\')">Hapus</button>' +
        '</div></td></tr>';
    }).join('');
  }

  // ---------- render: anggota ----------
  function renderParticipants() {
    var tb = el('participant-table-body');
    if (!tb) return;
    var ad = activeData();
    var q = (el('participant-search') && el('participant-search').value || '').toLowerCase();
    var list = ad.parts.filter(function (p) { return !q || (p.nama || '').toLowerCase().indexOf(q) >= 0; });
    if (!list.length) { tb.innerHTML = emptyMsg('Belum ada anggota.'); return; }
    tb.innerHTML = list.map(function (p, i) {
      var g = ad.groups.find(function (x) { return x.id === p.kelompok_id; });
      return '<tr class="hover:bg-slate-50/60">' +
        '<td class="p-4 pl-6 text-center text-slate-400 font-mono text-[11px]">' + (i + 1) + '</td>' +
        '<td class="p-4 font-semibold text-slate-800">' + esc(p.nama) + '</td>' +
        '<td class="p-4 text-slate-600">' + esc(p.whatsapp || '-') + '</td>' +
        '<td class="p-4 font-mono text-xs text-slate-500">' + esc(p.pin || '-') + '</td>' +
        '<td class="p-4 text-slate-600">' + (g ? esc(g.nama) : '<span class="text-slate-300">-</span>') + '</td>' +
        '<td class="p-4 font-semibold text-slate-700">' + fmtMoney(p.target_saving) + '</td>' +
        '<td class="p-4 pr-6 text-right"><div class="flex justify-end gap-1.5">' +
        '<button class="px-2.5 py-1.5 text-xs rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold" onclick="openEditParticipant(\'' + esc(p.id) + '\')">Edit</button>' +
        '<button class="px-2.5 py-1.5 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold" onclick="confirmDelete(\'deleteAnggota\',\'' + esc(p.id) + '\',\'' + esc(p.nama) + '\')">Hapus</button>' +
        '</div></td></tr>';
    }).join('');
  }

  // ---------- render: setoran ----------
  var depositPage = 1, depositSize = 10, depositFiltered = [];
  function renderDeposits() {
    var tb = el('deposit-table-body');
    if (!tb) return;
    var isShohibul = !!(state.user && state.user.role === 'shohibul');
    var ad = activeData();
    var q = (el('deposit-search') && el('deposit-search').value || '').toLowerCase();
    var stage = (el('deposit-stage-filter') && el('deposit-stage-filter').value) || '';
    var list = ad.deps.slice();
    if (state.user && state.user.role === 'shohibul' && state.user.participantId != null) {
      list = list.filter(function (d) { return String(d.anggota_id) === String(state.user.participantId); });
    }
    if (stage) list = list.filter(function (d) { return d.tahapId === stage; });
    if (q) {
      list = list.filter(function (d) {
        var p = ad.parts.find(function (x) { return x.id === d.anggota_id; });
        return (p && (p.nama || '').toLowerCase().indexOf(q) >= 0) || (d.catatan || '').toLowerCase().indexOf(q) >= 0;
      });
    }
    list.sort(function (a, b) { return (b.updatedAt || 0) - (a.updatedAt || 0); });
    depositFiltered = list;
    var total = list.length;
    var pages = Math.max(1, Math.ceil(total / depositSize));
    if (depositPage > pages) depositPage = pages;
    var start = (depositPage - 1) * depositSize;
    var slice = list.slice(start, start + depositSize);
    if (!slice.length) { tb.innerHTML = emptyMsg('Belum ada transaksi.'); }
    else {
      tb.innerHTML = slice.map(function (d, i) {
        var p = ad.parts.find(function (x) { return x.id === d.anggota_id; });
        var nameCell = isShohibul ? '' : '<td class="p-4 font-semibold text-slate-800">' + (p ? esc(p.nama) : '<span class="text-slate-300">-</span>') + '</td>';
        return '<tr class="hover:bg-slate-50/60">' +
          '<td class="p-4 pl-6 text-center text-slate-400 font-mono text-[11px]">' + (start + i + 1) + '</td>' +
          '<td class="p-4 text-slate-600">' + fmtTanggalID(d.tanggal) + '</td>' +
          nameCell +
          '<td class="p-4 text-slate-600">Bulan ' + (d.bulan || '-') + '</td>' +
          '<td class="p-4 font-bold text-emerald-700">' + fmtMoney(d.jumlah) + '</td>' +
          '<td class="p-4 text-slate-600">' + esc(d.recorded_by || '-') + '</td>' +
          '<td class="p-4 text-slate-500 text-xs">' + esc(d.catatan || '-') + '</td>' +
          (isShohibul ? '' :
          '<td class="p-4 pr-6 text-right"><div class="flex justify-end gap-1.5">' +
          '<button class="px-2.5 py-1.5 text-xs rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold" onclick="openEditDeposit(\'' + esc(d.id) + '\')">Edit</button>' +
          '<button class="px-2.5 py-1.5 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold" onclick="confirmDelete(\'deleteSetoran\',\'' + esc(d.id) + '\')">Hapus</button>' +
          '</div></td>') +
          '</tr>';
      }).join('');
    }
    if (el('deposit-page-info')) el('deposit-page-info').textContent = 'Menampilkan ' + total + ' data';
    renderDepositPagination(pages);
  }

  function renderDepositPagination(pages) {
    var c = el('deposit-pagination-controls');
    if (!c) return;
    var html = '<button class="px-2.5 py-1.5 text-xs rounded-lg border ' + (depositPage <= 1 ? 'text-slate-300 border-slate-100' : 'text-slate-600 border-slate-200 hover:bg-slate-50') + '" onclick="changeDepositPage(' + (depositPage - 1) + ')">Prev</button>';
    html += '<span class="text-xs font-semibold text-slate-500 px-2">' + depositPage + '/' + pages + '</span>';
    html += '<button class="px-2.5 py-1.5 text-xs rounded-lg border ' + (depositPage >= pages ? 'text-slate-300 border-slate-100' : 'text-slate-600 border-slate-200 hover:bg-slate-50') + '" onclick="changeDepositPage(' + (depositPage + 1) + ')">Next</button>';
    c.innerHTML = html;
  }

  window.changeDepositPage = function (p) { depositPage = p; renderDeposits(); };
  window.changeDepositPageSize = function (v) { depositSize = Number(v) || 10; depositPage = 1; renderDeposits(); };
  window.resetDepositPagination = function () { depositPage = 1; renderDeposits(); };

  // ---------- render: rekap matrix ----------
  function buildRekapRows(forParticipantId) {
    var ad = activeData();
    var parts = forParticipantId ? ad.parts.filter(function (p) { return String(p.id) === String(forParticipantId); }) : ad.parts;
    var validIds = {};
    ad.parts.forEach(function (p) { validIds[String(p.id)] = true; });
    var deps = ad.deps.filter(function (d) { return validIds[String(d.anggota_id)]; });
    if (forParticipantId) deps = deps.filter(function (d) { return String(d.anggota_id) === String(forParticipantId); });
    var months = [];
    for (var m = 1; m <= 12; m++) months.push(m);
    var monthDates = {};
    deps.forEach(function (d) {
      var b = Number(d.bulan) || 0;
      if (b >= 1 && b <= 12 && d.tanggal) { (monthDates[b] = monthDates[b] || []).push(d.tanggal); }
    });
    function fmtDate(x) { var p = String(x).split('-'); return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : x; }
    var head = '<tr>' +
      '<th class="p-3 pl-4 sticky left-0 bg-white text-left" rowspan="2">Shohibul</th>' +
      months.map(function (m) { return '<th class="p-3 text-center">Bulan ' + m + '</th>'; }).join('') +
      '<th class="p-3 pr-4 text-center" rowspan="2">Total</th></tr>' +
      '<tr>' + months.map(function (m) {
        var ds = monthDates[m] || [];
        var txt = ds.length ? fmtDate(ds.slice().sort()[0]) : '-';
        return       '<th class="p-1 text-center text-[10px] font-normal text-slate-400">' + txt + '</th>';
      }).join('') + '</tr>';
    if (el('rekap-table-head')) el('rekap-table-head').innerHTML = head;
    var body = el('rekap-table-body');
    if (!body) return;
    if (!parts.length) { body.innerHTML = '<tr><td colspan="14" class="p-6 text-center text-xs text-slate-400">Belum ada anggota.</td></tr>'; return; }
    body.innerHTML = parts.map(function (p) {
      var paidMonths = {}; var total = 0;
      deps.filter(function (d) { return d.anggota_id === p.id; }).forEach(function (d) {
        var b = Number(d.bulan) || 0; if (b >= 1 && b <= 12) paidMonths[b] = (paidMonths[b] || 0) + (Number(d.jumlah) || 0);
        total += (Number(d.jumlah) || 0);
      });
      var cells = months.map(function (m) {
        var v = paidMonths[m] || 0;
        if (v > 0) return '<td class="p-2 bg-emerald-50/60 text-emerald-700 font-bold text-center text-[11px] whitespace-nowrap">' + Number(v).toLocaleString('id-ID') + '</td>';
        return '<td class="p-2 text-slate-200 text-center text-[11px]">-</td>';
      }).join('');
      return '<tr class="hover:bg-slate-50/40"><td class="p-3 pl-4 sticky left-0 bg-white text-left font-semibold text-slate-800 whitespace-nowrap">' + esc(p.nama) + '</td>' + cells +
        '<td class="p-3 pr-4 font-bold text-slate-700 text-right">' + Number(total).toLocaleString('id-ID') + '</td></tr>';
    }).join('');
    var totalRow = '<tr class="bg-slate-100 font-bold text-slate-800"><td class="p-2 pl-4 sticky left-0 bg-slate-100 text-left whitespace-nowrap">TOTAL KESELURUHAN</td>';
    months.forEach(function (m) {
      var sum = 0;
      deps.forEach(function (d) { if (Number(d.bulan) === m) sum += (Number(d.jumlah) || 0); });
      totalRow += '<td class="p-2 text-center text-[11px] whitespace-nowrap">' + (sum > 0 ? Number(sum).toLocaleString('id-ID') : '-') + '</td>';
    });
    var grand = 0; deps.forEach(function (d) { grand += (Number(d.jumlah) || 0); });
    totalRow += '<td class="p-2 pr-4 text-right">' + Number(grand).toLocaleString('id-ID') + '</td></tr>';
    body.innerHTML = parts.map(function (p) {
      var paidMonths = {}; var total2 = 0;
      deps.filter(function (d) { return d.anggota_id === p.id; }).forEach(function (d) {
        var b = Number(d.bulan) || 0; if (b >= 1 && b <= 12) paidMonths[b] = (paidMonths[b] || 0) + (Number(d.jumlah) || 0);
        total2 += (Number(d.jumlah) || 0);
      });
      var c = months.map(function (m) {
        var v = paidMonths[m] || 0;
        if (v > 0) return '<td class="p-2 bg-emerald-50/60 text-emerald-700 font-bold text-center text-[11px] whitespace-nowrap">' + Number(v).toLocaleString('id-ID') + '</td>';
        return '<td class="p-2 text-slate-200 text-center text-[11px]">-</td>';
      }).join('');
      return '<tr class="hover:bg-slate-50/40"><td class="p-3 pl-4 sticky left-0 bg-white text-left font-semibold text-slate-800 whitespace-nowrap">' + esc(p.nama) + '</td>' + c +
        '<td class="p-3 pr-4 font-bold text-slate-700 text-right">' + Number(total2).toLocaleString('id-ID') + '</td></tr>';
    }).join('') + totalRow;
  }

  // ---------- stage filter population ----------
  function fillStageFilters() {
    ['deposit-stage-filter', 'rekap-stage-filter'].forEach(function (id) {
      var sel = el(id);
      if (!sel) return;
      var cur = sel.value;
      sel.innerHTML = '<option value="">Semua Tahap</option>' + state.data.tahaps.map(function (t) {
        return '<option value="' + esc(t.id) + '"' + (t.id === state.currentTahapId ? ' selected' : '') + '>' + esc(t.nama) + '</option>';
      }).join('');
      if (cur) sel.value = cur;
    });
  }
  window.changeStageFilter = function () { renderCurrentPage(); };

  // ---------- modals populate ----------
  function fillGroupSelect() {
    var sel = el('participant-group');
    if (!sel) return;
    var ad = activeData();
    sel.innerHTML = '<option value="">-- Tanpa Kelompok --</option>' + ad.groups.map(function (g) {
      return '<option value="' + esc(g.id) + '">' + esc(g.nama) + '</option>';
    }).join('');
  }

  function fillDepositSelects() {
    var sel = el('deposit-participant');
    if (sel) {
      var ad = activeData();
      sel.innerHTML = '<option value="">-- Pilih Anggota --</option>' + ad.parts.map(function (p) {
        return '<option value="' + esc(p.id) + '">' + esc(p.nama) + '</option>';
      }).join('');
    }
    var b = el('deposit-bulan');
    if (b && !b.options.length) {
      b.innerHTML = '';
      for (var i = 1; i <= 12; i++) {
        var o = document.createElement('option');
        o.value = i; o.textContent = 'Bulan ' + i;
        b.appendChild(o);
      }
    }
  }

  function suggestTargetPrice() {
    var t = el('group-type'), tg = el('group-target');
    if (!t || !tg) return;
    tg.value = t.value === 'Kambing' ? 3000000 : 21000000;
  }
  window.suggestTargetPrice = suggestTargetPrice;

  // ---------- header ----------
  function renderHeader() {
    var page = document.body.getAttribute('data-page');
    var titles = {
      dashboard: ['Ringkasan Tabungan', 'Dashboard Utama'],
      tahap: ['Manajemen Tahap', 'Tahap Aktif'],
      kelompok: ['Daftar Kelompok', 'Kelompok Qurban'],
      anggota: ['Daftar Anggota', 'Anggota Shohibul'],
      setoran: ['Transaksi', 'Catat Setoran'],
      rekap: ['Rekapitulasi', 'Rekapitulasi Tabungan'],
      riwayat: ['Riwayat', 'Riwayat Setoran Saya'],
      pengaturan: ['Pengaturan', 'Pengaturan Cloud']
    };
    var t = titles[page] || ['QurbanKu', 'Dashboard'];
    if (el('header-subtitle')) el('header-subtitle').textContent = t[0];
    if (el('header-title')) el('header-title').textContent = t[1];
    if (el('header-user-display')) el('header-user-display').textContent = (state.user && (state.user.name || state.user.nama)) || 'Pengguna';
    if (el('header-user-role')) el('header-user-role').textContent = state.user && state.user.role === 'admin' ? 'Pengurus' : 'Shohibul';
    // tahap badge
    var badge = el('header-tahap-badge');
    if (badge) {
      var aktif = state.data.tahaps.find(function (t) { return t.id === state.currentTahapId; });
      badge.innerHTML = aktif ? '<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-[11px] font-bold rounded-full border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>' + esc(aktif.nama) + '</span>' : '';
    }
  }

  // ---------- page dispatch ----------
  function renderCurrentPage() {
    try {
      var page = document.body.getAttribute('data-page');
      if (page === 'dashboard') renderDashboard();
      else if (page === 'tahap') renderTahap();
      else if (page === 'kelompok') renderGroups();
      else if (page === 'anggota') renderParticipants();
      else if (page === 'setoran') { fillStageFilters(); fillDepositSelects(); renderDeposits(); }
      else if (page === 'rekap') { fillStageFilters(); buildRekapRows(state.user && state.user.role === 'shohibul' ? state.user.participantId : null); }
      else if (page === 'riwayat') { fillStageFilters(); renderDeposits(); }
      else if (page === 'pengaturan') renderSettings();
      if (window.lucide) lucide.createIcons();
    } catch (e) {
      var b = document.getElementById('debug-banner');
      if (b) b.textContent = 'RENDER ERROR: ' + (e.message || e) + ' @ ' + (e.stack ? e.stack.split('\n')[1] : '?');
      var dbg = document.getElementById('dashboard-stats');
      if (dbg) dbg.innerHTML = '<div class="col-span-full bg-red-50 border border-red-200 text-red-700 text-xs p-4 rounded-xl font-mono">RENDER ERROR: ' + (e.message || e) + '<br>' + (e.stack || '') + '</div>';
      console.error('RENDER ERROR', e);
    }
    renderFooter();
  }

  function renderFooter() {
    var host = document.body;
    if (!host) return;
    var f = document.getElementById('app-footer-bar');
    if (!f) {
      f = document.createElement('footer');
      f.id = 'app-footer-bar';
      f.className = 'app-footer';
      host.appendChild(f);
    }
    f.innerHTML =
      '<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">' +
      '<div style="display:flex;align-items:center;gap:10px;">' +
      '<div style="width:32px;height:32px;border-radius:8px;background:#059669;display:flex;align-items:center;justify-content:center;color:#fff;"><i class="fa-solid fa-kaaba" style="font-size:13px;color:#fcd34d;"></i></div>' +
      '<div><div style="font-size:12px;font-weight:700;color:#1e293b;line-height:1.2;">QurbanKu <span style="color:#d97706;">·</span> Sistem Tabungan Qurban Bersama</div>' +
      '<div style="font-size:10px;color:#94a3b8;line-height:1.2;">Keluarga Besar H. Dimyati</div></div></div>' +
      '<div class="app-footer-copy" style="font-size:10px;color:#94a3b8;text-align:right;line-height:1.4;">&copy; ' + new Date().getFullYear() + ' QurbanKu.<br>Dikembangkan untuk pengelolaan arisan tabungan qurban keluarga.</div>' +
      '</div>';
  }

  // ---------- settings ----------
  function renderSettings() {
    var s = state.data.settings || {};
    if (el('settings-ketua-name')) el('settings-ketua-name').value = s.ketuaName || '';
    if (el('settings-bendahara-name')) el('settings-bendahara-name').value = s.bendaharaName || '';
    if (el('settings-monthly-installment')) el('settings-monthly-installment').value = s.monthlyInstallment || 150000;
    var pill = el('status-mode-preview-pill');
    if (pill) pill.innerHTML = '<span class="text-[10px] font-bold px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-full flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Pangkalan Data MySQL Lokal</span>';
  }

  window.handleArisanSettingsSubmit = function (e) {
    e.preventDefault();
    save({
      action: 'saveSettings',
      data: {
        ketuaName: el('settings-ketua-name').value,
        bendaharaName: el('settings-bendahara-name').value,
        monthlyInstallment: Number(el('settings-monthly-installment').value) || 0
      }
    }).then(function () { toast('Pengaturan disimpan', 'success'); return loadData(); })
      .then(renderCurrentPage).catch(function (e) { toast(e.message || 'Gagal', 'error'); });
  };

  // ---------- CRUD modal openers ----------
  window.openAddTahap = function () { el('tahap-modal-title').textContent = 'Tambah Tahap'; el('edit-tahap-id').value = ''; el('tahap-form').reset(); openModal('tahap-modal'); };
  window.openEditTahap = function (id) {
    var t = state.data.tahaps.find(function (x) { return x.id === id; }); if (!t) return;
    el('tahap-modal-title').textContent = 'Edit Tahap'; el('edit-tahap-id').value = t.id;
    el('tahap-name').value = t.nama; el('tahap-status').value = t.status || 'Aktif';
    openModal('tahap-modal');
  };
  window.handleTahapSubmit = function (e) {
    e.preventDefault();
    var id = el('edit-tahap-id').value;
    save({ action: 'saveTahap', data: { id: id, nama: el('tahap-name').value, status: el('tahap-status').value } })
      .then(function () { closeModal('tahap-modal'); toast('Tersimpan', 'success'); return loadData(); })
      .then(function () { fillStageFilters(); renderCurrentPage(); }).catch(function (e) { toast(e.message, 'error'); });
  };

  window.openAddGroup = function () { el('kelompok-modal-title').textContent = 'Tambah Kelompok'; el('edit-group-id').value = ''; el('group-form').reset(); suggestTargetPrice(); openModal('kelompok-modal'); };
  window.openEditGroup = function (id) {
    var g = state.data.groups.find(function (x) { return x.id === id; }); if (!g) return;
    el('kelompok-modal-title').textContent = 'Edit Kelompok'; el('edit-group-id').value = g.id;
    el('group-name').value = g.nama; el('group-type').value = g.tipe || 'Sapi'; el('group-target').value = g.target || '';
    openModal('kelompok-modal');
  };
  window.handleGroupSubmit = function (e) {
    e.preventDefault();
    var id = el('edit-group-id').value;
    save({ action: 'saveKelompok', data: { id: id, nama: el('group-name').value, tipe: el('group-type').value, target: Number(String(el('group-target').value).replace(/[^\d]/g, '')) || 0 } })
      .then(function () { closeModal('kelompok-modal'); toast('Tersimpan', 'success'); return loadData(); })
      .then(renderCurrentPage).catch(function (e) { toast(e.message, 'error'); });
  };

  window.openAddParticipant = function () { el('anggota-modal-title').textContent = 'Tambah Anggota'; el('edit-participant-id').value = ''; el('participant-form').reset(); fillGroupSelect(); openModal('anggota-modal'); };
  window.openEditParticipant = function (id) {
    var p = state.data.participants.find(function (x) { return x.id === id; }); if (!p) return;
    el('anggota-modal-title').textContent = 'Edit Anggota'; el('edit-participant-id').value = p.id;
    fillGroupSelect();
    el('participant-name').value = p.nama; el('participant-whatsapp').value = p.whatsapp || '';
    el('participant-pin').value = p.pin || ''; el('participant-group').value = p.kelompok_id || '';
    el('participant-target').value = p.target_saving ? Number(p.target_saving).toLocaleString('id-ID') : '';
    openModal('anggota-modal');
  };
  window.handleParticipantSubmit = function (e) {
    e.preventDefault();
    var id = el('edit-participant-id').value;
    save({ action: 'saveAnggota', data: { id: id, nama: el('participant-name').value, whatsapp: el('participant-whatsapp').value, pin: el('participant-pin').value, kelompok_id: el('participant-group').value, target_saving: Number(String(el('participant-target').value).replace(/[^\d]/g, '')) || 0 } })
      .then(function () { closeModal('anggota-modal'); toast('Tersimpan', 'success'); return loadData(); })
      .then(function () { fillGroupSelect(); renderCurrentPage(); }).catch(function (e) { toast(e.message, 'error'); });
  };

  function todayStr() {
    var n = new Date();
    var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return days[n.getDay()] + ', ' + n.getDate() + ' ' + months[n.getMonth()] + ' ' + n.getFullYear();
  }
  function todayInput() {
    var n = new Date();
    var m = String(n.getMonth() + 1).padStart(2, '0');
    var d = String(n.getDate()).padStart(2, '0');
    return n.getFullYear() + '-' + m + '-' + d;
  }
  window.openAddDeposit = function () {
    el('setoran-modal-title').textContent = 'Catat Setoran';
    el('edit-deposit-id').value = '';
    el('deposit-form').reset();
    fillDepositSelects();
    if (el('deposit-date')) { el('deposit-date').value = todayInput(); }
    openModal('setoran-modal');
  };
  window.openEditDeposit = function (id) {
    var d = state.data.deposits.find(function (x) { return x.id === id; }); if (!d) return;
    el('setoran-modal-title').textContent = 'Edit Setoran'; el('edit-deposit-id').value = d.id;
    fillDepositSelects();
    el('deposit-participant').value = d.anggota_id;
    if (el('deposit-date')) {
      el('deposit-date').value = String(d.tanggal || '').split(' ')[0] || todayInput();
    }
    el('deposit-amount').value = Number(d.jumlah) || 0; el('deposit-bulan').value = d.bulan || 1;
    el('deposit-recorded').value = d.recorded_by || ''; el('deposit-note').value = d.catatan || '';
    openModal('setoran-modal');
  };
  window.handleDepositSubmit = function (e) {
    e.preventDefault();
    var id = el('edit-deposit-id').value;
    save({ action: 'saveSetoran', data: { id: id, anggota_id: el('deposit-participant').value, tanggal: el('deposit-date').value, jumlah: Number(String(el('deposit-amount').value).replace(/[^\d]/g, '')) || 0, bulan: Number(el('deposit-bulan').value) || 1, recorded_by: el('deposit-recorded').value, catatan: el('deposit-note').value, tahapId: state.currentTahapId } })
      .then(function () { closeModal('setoran-modal'); toast('Tersimpan', 'success'); return loadData(); })
      .then(renderCurrentPage).catch(function (e) { toast(e.message, 'error'); });
  };

  window.formatMoneyInput = function (inp) {
    var v = String(inp.value).replace(/[^\d]/g, '');
    inp.value = v ? Number(v).toLocaleString('id-ID') : '';
  };

  // ---------- confirm delete ----------
  var pendingDelete = null;
  window.confirmDelete = function (action, id, name) {
    pendingDelete = { action: action, id: id };
    if (el('modal-message')) el('modal-message').textContent = 'Yakin hapus ' + (name || 'data') + '? Tindakan tidak dapat dibatalkan.';
    var c = el('modal-btn-confirm');
    if (c) c.onclick = function () {
      closeModal('confirm-modal');
      if (!pendingDelete) return;
      save({ action: pendingDelete.action, data: { id: pendingDelete.id } })
        .then(function () { toast('Terhapus', 'success'); return loadData(); })
        .then(function () { fillGroupSelect(); renderCurrentPage(); }).catch(function (e) { toast(e.message, 'error'); });
      pendingDelete = null;
    };
    var x = el('modal-btn-cancel');
    if (x) x.onclick = function () { closeModal('confirm-modal'); pendingDelete = null; };
    openModal('confirm-modal');
  };

  // ---------- export / print (simple) ----------
  window.exportDepositsExcel = function () { window.print(); };
  window.printDepositsPdf = function () { window.print(); };
  window.exportRekapExcel = function () { window.print(); };

  window.printRekapReport = function () {
    var ad = activeData();
    var months = []; for (var m = 1; m <= 12; m++) months.push(m);
    var tahap = state.data.tahaps.filter(function (t) { return String(t.id) === String(state.currentTahapId); })[0];
    var tahapName = tahap ? tahap.nama : 'Semua Tahap';
    var s = state.data.settings || {};
    var ketua = s.ketuaName || '';
    var bendahara = s.bendaharaName || '';
    var parts = ad.parts;
    var validIds = {};
    ad.parts.forEach(function (p) { validIds[String(p.id)] = true; });
    var deps = ad.deps.filter(function (d) { return validIds[String(d.anggota_id)]; });
    var monthDates = {};
    deps.forEach(function (d) {
      var b = Number(d.bulan) || 0;
      if (b >= 1 && b <= 12 && d.tanggal) { (monthDates[b] = monthDates[b] || []).push(d.tanggal); }
    });
    function fmtDate(x) { var p = String(x).split('-'); return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : x; }
    var rows = parts.map(function (p) {
      var paid = {}; var total = 0;
      deps.filter(function (d) { return d.anggota_id === p.id; }).forEach(function (d) {
        var b = Number(d.bulan) || 0; if (b >= 1 && b <= 12) paid[b] = (paid[b] || 0) + (Number(d.jumlah) || 0);
        total += (Number(d.jumlah) || 0);
      });
      var cells = months.map(function (mm) {
        var v = paid[mm] || 0;
        if (v > 0) return '<td style="padding:5px 4px;border:1px solid #e2e8f0;background:#ecfdf5;color:#047857;font-weight:700;text-align:center;font-size:10px;white-space:nowrap;">' + Number(v).toLocaleString('id-ID') + '</td>';
        return '<td style="padding:5px 4px;border:1px solid #e2e8f0;color:#cbd5e1;text-align:center;font-size:10px;">-</td>';
      }).join('');
      return '<tr><td style="padding:6px 8px;border:1px solid #e2e8f0;font-weight:600;color:#1e293b;white-space:nowrap;">' + esc(p.nama) + '</td>' + cells +
        '<td style="padding:6px 8px;border:1px solid #e2e8f0;font-weight:700;color:#334155;text-align:right;">' + Number(total).toLocaleString('id-ID') + '</td></tr>';
    }).join('');
    var totalRow = '<tr style="background:#f1f5f9;font-weight:700;color:#1e293b;"><td style="padding:6px 8px;border:1px solid #cbd5e1;white-space:nowrap;">TOTAL KESELURUHAN</td>';
    months.forEach(function (mm) {
      var sum = 0; deps.forEach(function (d) { if (Number(d.bulan) === mm) sum += (Number(d.jumlah) || 0); });
      totalRow += '<td style="padding:5px 4px;border:1px solid #cbd5e1;color:#047857;text-align:center;font-size:10px;white-space:nowrap;">' + (sum > 0 ? Number(sum).toLocaleString('id-ID') : '-') + '</td>';
    });
    var grand = 0; deps.forEach(function (d) { grand += (Number(d.jumlah) || 0); });
    totalRow += '<td style="padding:6px 8px;border:1px solid #cbd5e1;text-align:right;">' + Number(grand).toLocaleString('id-ID') + '</td></tr>';
    if (!rows) rows = '<tr><td colspan="14" style="padding:18px;text-align:center;color:#94a3b8;font-size:12px;">Belum ada anggota.</td></tr>';
    rows = rows + totalRow;
    var headCells = months.map(function (mm) {
      var ds = monthDates[mm] || [];
      var txt = ds.length ? fmtDate(ds.slice().sort()[0]) : '-';
      return '<th style="padding:6px 6px;border:1px solid #cbd5e1;background:#f1f5f9;color:#334155;font-size:11px;"><div>Bulan ' + mm + '</div><div style="font-weight:400;font-size:9px;color:#94a3b8;margin-top:2px;">' + txt + '</div></th>';
    }).join('');
    var now = new Date();
    var dateStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + ' ' + now.toLocaleTimeString('id-ID');

    function build(qrLib) {
      var html =
        '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Rekap Setoran - ' + esc(tahapName) + '</title>' +
        '<style>@page{size:330mm 210mm;margin:15mm;}*{box-sizing:border-box;font-family:"Segoe UI",Tahoma,Arial,sans-serif;}' +
        'body{margin:0;color:#1e293b;}.doc{padding:8mm;}.hdr{display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #059669;padding-bottom:12px;margin-bottom:14px;}' +
        '.brand{font-size:20px;font-weight:800;color:#059669;}.sub{font-size:11px;color:#64748b;margin-top:2px;}' +
        '.meta{font-size:11px;color:#475569;text-align:right;}.title{font-size:14px;font-weight:700;margin:0 0 4px;}' +
        'table{width:100%;border-collapse:collapse;font-size:11px;}.ttot{border:1px solid #cbd5e1;background:#f1f5f9;font-weight:700;}' +
        '.sig{display:flex;justify-content:space-between;margin-top:40px;font-size:13px;color:#334155;}' +
        '.sig .box{width:34%;text-align:center;}.sig .role{font-weight:700;color:#334155;margin-bottom:10px;font-size:14px;}' +
        '.sig .qr{width:120px;height:120px;margin:0 auto;}.sig .nm{font-size:13px;color:#475569;margin-top:10px;font-weight:700;}</style>' +
        (qrLib ? '<script>' + qrLib + '<\/script>' : '') +
        '</head>' +
        '<body><div class="doc">' +
        '<div class="hdr"><div><div class="brand">QurbanKu</div><div class="sub">Sistem Tabungan Qurban Bersama</div><div class="sub" style="color:#b45309;font-weight:700;">Keluarga Besar H. Dimyati</div></div>' +
        '<div class="meta"><div class="title">Rekapitulasi Setoran Bulanan</div>Periode: <b>' + esc(tahapName) + '</b><br>Dicetak: ' + esc(dateStr) + '</div></div>' +
        '<table><thead><tr><th class="ttot" style="padding:8px;border:1px solid #cbd5e1;background:#f1f5f9;text-align:left;">Shohibul</th>' + headCells + '<th class="ttot" style="padding:8px;border:1px solid #cbd5e1;background:#f1f5f9;">Total</th></tr></thead>' +
        '<tbody>' + rows + '</tbody></table>' +
        '<div class="sig">' +
        '<div class="box"><div class="role">Ketua</div><div class="qr" id="qr-ketua"></div><div class="nm">' + esc(ketua) + '</div></div>' +
        '<div class="box"><div class="role">Bendahara</div><div class="qr" id="qr-bendahara"></div><div class="nm">' + esc(bendahara) + '</div></div>' +
        '</div>' +
        '<script>' +
        'function drawQR(id,text){var el=document.getElementById(id);if(!el||typeof qrcode==="undefined"||!text)return;' +
        'try{var qr=qrcode(0,"M");qr.addData(String(text));qr.make();el.innerHTML=qr.createSvgTag({cellSize:3,margin:0,scalable:true});}catch(e){}}' +
        'drawQR("qr-bendahara","' + esc(bendahara || 'Bendahara') + '");' +
        'drawQR("qr-ketua","' + esc(ketua || 'Ketua') + '");' +
        'window.onload=function(){window.focus();setTimeout(function(){window.print();},300);};' +
        '<\/script>' +
        '</div></body></html>';
      return html;
    }

    function openReport(htmlStr) {
      try {
        var blob = new Blob([htmlStr], { type: 'text/html' });
        var url = URL.createObjectURL(blob);
        var w = window.open(url, '_blank');
        if (!w) { toast('Blokir popup aktif, izinkan untuk mencetak', 'error'); return; }
        setTimeout(function () { try { URL.revokeObjectURL(url); } catch (e) {} }, 60000);
      } catch (e) {
        var w2 = window.open('', '_blank');
        if (!w2) { toast('Blokir popup aktif, izinkan untuk mencetak', 'error'); return; }
        w2.document.open(); w2.document.write(htmlStr); w2.document.close();
      }
    }

    var qrPath = location.pathname.substring(0, location.pathname.lastIndexOf('/') + 1) + '../assets/js/vendor/qrcode.js';
    fetch(qrPath).then(function (r) { return r.text(); }).then(function (lib) {
      openReport(build(lib));
    }).catch(function () {
      openReport(build(''));
    });
  };

  // ---------- sync ----------
  window.syncWithCloud = function () {
    loadData().then(renderCurrentPage).then(function () { toast('Data disinkronkan', 'success'); }).catch(function () { toast('Gagal memuat data', 'error'); });
  };

  // ---------- boot ----------
  function bootApp(rawUser) {
    var d = (rawUser && rawUser.data) || {};
    state.user = {
      role: (rawUser && rawUser.role) || 'shohibul',
      name: d.nama || rawUser.name || '',
      participantId: d.id || rawUser.participantId || null
    };
    renderHeader();
    startClock();
    loadData().then(function () {
      fillGroupSelect();
      fillStageFilters();
      renderCurrentPage();
      renderHeader(); // update badge tahap setelah data terload
    }).catch(function (e) {
      setCloud('Gagal memuat', false);
      toast('Gagal memuat data: ' + (e.message || ''), 'error');
    });
  }

  function startClock() {
    function tick() {
      var now = new Date();
      var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
      var dateStr = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
      var timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0') + ' WIB';
      var de = el('header-clock-date'); if (de) de.textContent = dateStr;
      var te = el('header-clock-time'); if (te) te.textContent = timeStr;
    }
    tick();
    if (window.__clockTimer) clearInterval(window.__clockTimer);
    window.__clockTimer = setInterval(tick, 1000);
  }

  function fmtTanggalID(x) {
    if (!x) return '-';
    var p = String(x).split(' ')[0].split('-');
    if (p.length !== 3) return x;
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return p[2] + ' ' + months[Number(p[1]) - 1] + ' ' + p[0];
  }
  window.initApp = bootApp;

  if (typeof window.__qurbankuBooted === 'undefined') {
    window.__qurbankuBooted = true;
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () { if (typeof window.__bootUser !== 'undefined') bootApp(window.__bootUser); });
    }
  }
})();
