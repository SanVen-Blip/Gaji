
/* ===== PAYROLL MONITORING APP - JavaScript ===== */
'use strict';

// ---- Helpers ----
const BASE_URL = '/api';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function rupiah(n) {
    return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
}

function ajax(method, url, data = null) {
    return fetch(BASE_URL + url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: data ? JSON.stringify(data) : null,
    }).then(r => r.json());
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = '';
    const icon = document.createElement('i');
    icon.className = type === 'success' ? 'fas fa-check-circle' : type === 'error' ? 'fas fa-times-circle' : 'fas fa-exclamation-circle';
    t.appendChild(icon);
    t.appendChild(document.createTextNode(' ' + msg));
    t.className = 'toast show ' + type;
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.className = 'toast'; }, 3500);
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function clearErrors(fields) {
    fields.forEach(f => {
        const el = document.getElementById(f);
        if (el) { el.textContent = ''; }
    });
}

function showErrors(errors) {
    Object.entries(errors).forEach(([key, msgs]) => {
        const errKey = 'err' + key.charAt(0).toUpperCase() + key.slice(1).replace(/_([a-z])/g, (_, c) => c.toUpperCase());
        const el = document.getElementById(errKey);
        if (el) el.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
    });
}

// ---- Date ----
(function setDate() {
    const el = document.getElementById('currentDate');
    if (el) el.textContent = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
})();

// ---- Sidebar Toggle ----
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sidebarToggle = document.getElementById('sidebarToggle');
const menuBtn = document.getElementById('menuBtn');

// Set tooltip untuk nav item saat collapsed
const tooltipMap = { dashboard: 'Dashboard', karyawan: 'Data Karyawan', gaji: 'Data Gaji' };
document.querySelectorAll('.nav-item').forEach(item => {
    const page = item.dataset.page;
    if (tooltipMap[page]) item.setAttribute('data-tooltip', tooltipMap[page]);
});

function toggleSidebar() {
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        sidebar.classList.toggle('mobile-open');
    } else {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    }
}

if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
menuBtn.addEventListener('click', toggleSidebar);

// ---- Navigation ----
const pageTitles = { dashboard: 'Dashboard', karyawan: 'Data Karyawan', gaji: 'Data Gaji', absensi: 'Absensi' };

function navigateTo(page) {
    if (!page) return;

    // Sembunyikan semua halaman (termasuk absensi yang pakai display:none)
    document.querySelectorAll('.page').forEach(p => {
        p.classList.remove('active');
        p.style.display = 'none';
    });

    // Tampilkan halaman yang dipilih
    const pageEl = document.getElementById('page-' + page);
    if (!pageEl) return;
    pageEl.style.display = 'block';
    pageEl.classList.add('active');

    // Update nav active state
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    const activeNav = document.querySelector(`.nav-item[data-page="${page}"]`);
    if (activeNav) activeNav.classList.add('active');

    // Update judul topbar
    document.getElementById('pageTitle').textContent = pageTitles[page] || page;

    // Load data sesuai halaman
    if (page === 'dashboard') loadDashboard();
    if (page === 'karyawan')  loadKaryawan();
    if (page === 'gaji')      loadGaji();
    if (page === 'absensi')   loadAbsensi();

    // Tutup sidebar di mobile
    if (window.innerWidth <= 768) sidebar.classList.remove('mobile-open');
}

document.querySelectorAll('.nav-item[data-page]').forEach(item => {
    item.addEventListener('click', () => navigateTo(item.dataset.page));
});

// ---- Modal close buttons ----
document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.modal));
});
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeModal(overlay.id);
    });
});

// ============================================================
// DASHBOARD
// ============================================================
let chartBulanan = null;

function loadDashboard() {
    ajax('GET', '/gaji/dashboard').then(res => {
        if (!res.success) return;
        const d = res.data;
        document.getElementById('statKaryawan').textContent = d.total_karyawan;
        document.getElementById('statTotalGaji').textContent = rupiah(d.total_gaji_bulan_ini);
        document.getElementById('statSudahBayar').textContent = d.sudah_bayar + ' orang';
        document.getElementById('statBelumBayar').textContent = d.belum_bayar + ' orang';

        // Chart
        const labels = d.statistik_bulanan.map(s => s.label);
        const values = d.statistik_bulanan.map(s => s.total);
        const ctx = document.getElementById('chartBulanan').getContext('2d');
        if (chartBulanan) chartBulanan.destroy();
        chartBulanan = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Total Gaji Bersih',
                    data: values,
                    backgroundColor: 'rgba(79,70,229,.7)',
                    borderColor: 'rgba(79,70,229,1)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => rupiah(ctx.raw)
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: v => 'Rp ' + (v / 1000000).toFixed(1) + 'jt'
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Top gaji
        const topEl = document.getElementById('topGajiList');
        topEl.innerHTML = '';
        if (!d.top_gaji.length) {
            topEl.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px">Belum ada data</p>';
            return;
        }
        const rankClass = ['gold', 'silver', 'bronze', '', ''];
        d.top_gaji.forEach((item, i) => {
            topEl.innerHTML += `
            <div class="top-item">
                <div class="top-rank ${rankClass[i] || ''}">${i + 1}</div>
                <div class="top-info">
                    <div class="top-name">${item.nama}</div>
                    <div class="top-jabatan">${item.jabatan}</div>
                </div>
                <div class="top-gaji">${rupiah(item.gaji_bersih)}</div>
            </div>`;
        });
    });
}

// ============================================================
// KARYAWAN
// ============================================================
let karyawanList = [];

function loadKaryawan() {
    document.getElementById('tbodyKaryawan').innerHTML =
        '<tr><td colspan="8" class="text-center loading-row"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>';
    ajax('GET', '/karyawan').then(res => {
        karyawanList = res.data || [];
        renderKaryawan(karyawanList);
    });
}

function renderKaryawan(data) {
    const tbody = document.getElementById('tbodyKaryawan');
    if (!data.length) {
        tbody.innerHTML = '<tr class="empty-row"><td colspan="8"><i class="fas fa-inbox"></i> Tidak ada data karyawan</td></tr>';
        return;
    }
    tbody.innerHTML = data.map(k => `
    <tr>
        <td><strong>${k.nik}</strong></td>
        <td>${k.nama}</td>
        <td>${k.jabatan}</td>
        <td>${k.departemen}</td>
        <td>${k.email}</td>
        <td>${k.tanggal_masuk ? new Date(k.tanggal_masuk).toLocaleDateString('id-ID') : '-'}</td>
        <td><span class="badge ${k.status === 'aktif' ? 'badge-success' : 'badge-secondary'}">${k.status === 'aktif' ? 'Aktif' : 'Non-Aktif'}</span></td>
        <td>
            <div class="action-btns">
                <button class="btn btn-sm btn-secondary btn-icon" title="Edit" onclick="editKaryawan(${k.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger btn-icon" title="Hapus" onclick="hapusKaryawan(${k.id}, '${k.nama}')"><i class="fas fa-trash"></i></button>
            </div>
        </td>
    </tr>`).join('');
}

// Search karyawan
document.getElementById('searchKaryawan').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    const filtered = karyawanList.filter(k =>
        k.nama.toLowerCase().includes(q) ||
        k.nik.toLowerCase().includes(q) ||
        k.jabatan.toLowerCase().includes(q) ||
        k.departemen.toLowerCase().includes(q)
    );
    renderKaryawan(filtered);
});

// Tambah karyawan
document.getElementById('btnTambahKaryawan').addEventListener('click', () => {
    document.getElementById('modalKaryawanTitle').innerHTML = '<i class="fas fa-user-plus"></i> Tambah Karyawan';
    document.getElementById('formKaryawan').reset();
    document.getElementById('karyawanId').value = '';
    clearErrors(['errNik','errNama','errJabatan','errDepartemen','errEmail','errTelepon','errTanggalMasuk','errStatus']);
    openModal('modalKaryawan');
});

function editKaryawan(id) {
    ajax('GET', '/karyawan/' + id).then(res => {
        if (!res.success) return showToast('Gagal memuat data', 'error');
        const k = res.data;
        document.getElementById('modalKaryawanTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit Karyawan';
        document.getElementById('karyawanId').value = k.id;
        document.getElementById('kNik').value = k.nik;
        document.getElementById('kNama').value = k.nama;
        document.getElementById('kJabatan').value = k.jabatan;
        document.getElementById('kDepartemen').value = k.departemen;
        document.getElementById('kEmail').value = k.email;
        document.getElementById('kTelepon').value = k.telepon || '';
        document.getElementById('kTanggalMasuk').value = k.tanggal_masuk;
        document.getElementById('kStatus').value = k.status;
        clearErrors(['errNik','errNama','errJabatan','errDepartemen','errEmail','errTelepon','errTanggalMasuk','errStatus']);
        openModal('modalKaryawan');
    });
}

document.getElementById('btnSimpanKaryawan').addEventListener('click', () => {
    const id = document.getElementById('karyawanId').value;
    const data = {
        nik:           document.getElementById('kNik').value.trim(),
        nama:          document.getElementById('kNama').value.trim(),
        jabatan:       document.getElementById('kJabatan').value.trim(),
        departemen:    document.getElementById('kDepartemen').value.trim(),
        email:         document.getElementById('kEmail').value.trim(),
        telepon:       document.getElementById('kTelepon').value.trim(),
        tanggal_masuk: document.getElementById('kTanggalMasuk').value,
        status:        document.getElementById('kStatus').value,
    };
    clearErrors(['errNik','errNama','errJabatan','errDepartemen','errEmail','errTelepon','errTanggalMasuk','errStatus']);
    const method = id ? 'PUT' : 'POST';
    const url    = id ? '/karyawan/' + id : '/karyawan';
    ajax(method, url, data).then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalKaryawan');
            loadKaryawan();
        } else if (res.errors) {
            showErrors(res.errors);
        } else {
            showToast(res.message || 'Terjadi kesalahan', 'error');
        }
    });
});

let hapusCallback = null;
function hapusKaryawan(id, nama) {
    document.getElementById('hapusMessage').textContent = `Hapus karyawan "${nama}"? Semua data gaji terkait juga akan dihapus.`;
    hapusCallback = () => {
        ajax('DELETE', '/karyawan/' + id).then(res => {
            showToast(res.message, res.success ? 'success' : 'error');
            closeModal('modalHapus');
            if (res.success) loadKaryawan();
        });
    };
    openModal('modalHapus');
}

// ============================================================
// GAJI
// ============================================================
let gajiList = [];

// Isi filter tahun
(function fillTahun() {
    const sel = document.getElementById('filterTahun');
    const now = new Date().getFullYear();
    for (let y = now; y >= now - 5; y--) {
        const opt = document.createElement('option');
        opt.value = y; opt.textContent = y;
        sel.appendChild(opt);
    }
    sel.value = now;
    // Isi juga input tahun di form
    document.getElementById('gTahun').value = now;
})();

// Set default bulan filter ke bulan ini
document.getElementById('filterBulan').value = new Date().getMonth() + 1;

function loadGaji() {
    const bulan  = document.getElementById('filterBulan').value;
    const tahun  = document.getElementById('filterTahun').value;
    const status = document.getElementById('filterStatus').value;
    let url = '/gaji?';
    if (bulan)  url += 'bulan=' + bulan + '&';
    if (tahun)  url += 'tahun=' + tahun + '&';
    if (status) url += 'status_bayar=' + status + '&';

    document.getElementById('tbodyGaji').innerHTML =
        '<tr><td colspan="9" class="text-center loading-row"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>';

    ajax('GET', url).then(res => {
        gajiList = res.data || [];
        renderGaji(gajiList);
    });
}

function renderGaji(data) {
    const tbody = document.getElementById('tbodyGaji');
    if (!data.length) {
        tbody.innerHTML = '<tr class="empty-row"><td colspan="9"><i class="fas fa-inbox"></i> Tidak ada data gaji</td></tr>';
        return;
    }
    tbody.innerHTML = data.map(g => {
        const tunjangan = g.tunjangan_transport + g.tunjangan_makan + g.tunjangan_lainnya + g.bonus;
        const potongan  = g.potongan_bpjs + g.potongan_pajak + g.potongan_lainnya;
        const statusBadge = g.status_bayar === 'sudah_bayar'
            ? '<span class="badge badge-success"><i class="fas fa-check"></i> Sudah Bayar</span>'
            : '<span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Bayar</span>';
        return `
        <tr>
            <td>
                <strong>${g.nama_karyawan}</strong><br>
                <small style="color:var(--text-muted)">${g.jabatan}</small>
            </td>
            <td>${g.departemen}</td>
            <td>${g.nama_bulan} ${g.tahun}</td>
            <td>${rupiah(g.gaji_pokok)}</td>
            <td style="color:var(--success)">${rupiah(tunjangan)}</td>
            <td style="color:var(--danger)">${rupiah(potongan)}</td>
            <td><strong>${rupiah(g.gaji_bersih)}</strong></td>
            <td>${statusBadge}</td>
            <td>
                <div class="action-btns">
                    <button class="btn btn-sm btn-secondary btn-icon" title="Detail" onclick="detailGaji(${g.id})"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-secondary btn-icon" title="Edit" onclick="editGaji(${g.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus" onclick="hapusGaji(${g.id}, '${g.nama_karyawan}', '${g.nama_bulan} ${g.tahun}')"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

document.getElementById('btnFilter').addEventListener('click', loadGaji);

// Populate karyawan dropdown di form gaji
function populateKaryawanSelect() {
    ajax('GET', '/karyawan').then(res => {
        const sel = document.getElementById('gKaryawanId');
        const current = sel.value;
        sel.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
        (res.data || []).filter(k => k.status === 'aktif').forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.id;
            opt.textContent = k.nik + ' - ' + k.nama;
            sel.appendChild(opt);
        });
        if (current) sel.value = current;
    });
}

// Hitung preview gaji bersih
function hitungPreview() {
    const pokok = parseFloat(document.getElementById('gGajiPokok').value) || 0;
    const tt    = parseFloat(document.getElementById('gTunjanganTransport').value) || 0;
    const tm    = parseFloat(document.getElementById('gTunjanganMakan').value) || 0;
    const tl    = parseFloat(document.getElementById('gTunjanganLainnya').value) || 0;
    const bon   = parseFloat(document.getElementById('gBonus').value) || 0;
    const pbpjs = parseFloat(document.getElementById('gPotonganBpjs').value) || 0;
    const ppjk  = parseFloat(document.getElementById('gPotonganPajak').value) || 0;
    const pl    = parseFloat(document.getElementById('gPotonganLainnya').value) || 0;
    const bersih = pokok + tt + tm + tl + bon - pbpjs - ppjk - pl;
    document.getElementById('previewGajiBersih').textContent = rupiah(bersih);
}

['gGajiPokok','gTunjanganTransport','gTunjanganMakan','gTunjanganLainnya','gBonus',
 'gPotonganBpjs','gPotonganPajak','gPotonganLainnya'].forEach(id => {
    document.getElementById(id).addEventListener('input', hitungPreview);
});

// Toggle tanggal bayar
document.getElementById('gStatusBayar').addEventListener('change', function () {
    document.getElementById('groupTanggalBayar').style.opacity = this.value === 'sudah_bayar' ? '1' : '.4';
});

// Tambah gaji
document.getElementById('btnTambahGaji').addEventListener('click', () => {
    document.getElementById('modalGajiTitle').innerHTML = '<i class="fas fa-file-invoice-dollar"></i> Tambah Data Gaji';
    document.getElementById('formGaji').reset();
    document.getElementById('gajiId').value = '';
    document.getElementById('gTahun').value = new Date().getFullYear();
    document.getElementById('gBulan').value = new Date().getMonth() + 1;
    document.getElementById('previewGajiBersih').textContent = 'Rp 0';
    clearErrors(['errGKaryawan','errGBulan','errGTahun','errGGajiPokok']);
    populateKaryawanSelect();
    openModal('modalGaji');
});

function editGaji(id) {
    ajax('GET', '/gaji/' + id).then(res => {
        if (!res.success) return showToast('Gagal memuat data', 'error');
        const g = res.data;
        document.getElementById('modalGajiTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Data Gaji';
        document.getElementById('gajiId').value = g.id;
        populateKaryawanSelect();
        setTimeout(() => { document.getElementById('gKaryawanId').value = g.karyawan_id; }, 300);
        document.getElementById('gBulan').value = g.bulan;
        document.getElementById('gTahun').value = g.tahun;
        document.getElementById('gGajiPokok').value = g.gaji_pokok;
        document.getElementById('gTunjanganTransport').value = g.tunjangan_transport;
        document.getElementById('gTunjanganMakan').value = g.tunjangan_makan;
        document.getElementById('gTunjanganLainnya').value = g.tunjangan_lainnya;
        document.getElementById('gBonus').value = g.bonus;
        document.getElementById('gPotonganBpjs').value = g.potongan_bpjs;
        document.getElementById('gPotonganPajak').value = g.potongan_pajak;
        document.getElementById('gPotonganLainnya').value = g.potongan_lainnya;
        document.getElementById('gStatusBayar').value = g.status_bayar;
        document.getElementById('gTanggalBayar').value = g.tanggal_bayar || '';
        document.getElementById('gKeterangan').value = g.keterangan || '';
        hitungPreview();
        clearErrors(['errGKaryawan','errGBulan','errGTahun','errGGajiPokok']);
        openModal('modalGaji');
    });
}

document.getElementById('btnSimpanGaji').addEventListener('click', () => {
    const id = document.getElementById('gajiId').value;
    const data = {
        karyawan_id:         document.getElementById('gKaryawanId').value,
        bulan:               parseInt(document.getElementById('gBulan').value),
        tahun:               parseInt(document.getElementById('gTahun').value),
        gaji_pokok:          parseFloat(document.getElementById('gGajiPokok').value) || 0,
        tunjangan_transport: parseFloat(document.getElementById('gTunjanganTransport').value) || 0,
        tunjangan_makan:     parseFloat(document.getElementById('gTunjanganMakan').value) || 0,
        tunjangan_lainnya:   parseFloat(document.getElementById('gTunjanganLainnya').value) || 0,
        bonus:               parseFloat(document.getElementById('gBonus').value) || 0,
        potongan_bpjs:       parseFloat(document.getElementById('gPotonganBpjs').value) || 0,
        potongan_pajak:      parseFloat(document.getElementById('gPotonganPajak').value) || 0,
        potongan_lainnya:    parseFloat(document.getElementById('gPotonganLainnya').value) || 0,
        status_bayar:        document.getElementById('gStatusBayar').value,
        tanggal_bayar:       document.getElementById('gTanggalBayar').value || null,
        keterangan:          document.getElementById('gKeterangan').value.trim(),
    };
    clearErrors(['errGKaryawan','errGBulan','errGTahun','errGGajiPokok']);
    const method = id ? 'PUT' : 'POST';
    const url    = id ? '/gaji/' + id : '/gaji';
    ajax(method, url, data).then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalGaji');
            loadGaji();
        } else if (res.errors) {
            showErrors(res.errors);
        } else {
            showToast(res.message || 'Terjadi kesalahan', 'error');
        }
    });
});

function detailGaji(id) {
    ajax('GET', '/gaji/' + id).then(res => {
        if (!res.success) return showToast('Gagal memuat data', 'error');
        const g = res.data;
        const k = g.karyawan || {};
        const namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const tunjangan = parseFloat(g.tunjangan_transport) + parseFloat(g.tunjangan_makan) + parseFloat(g.tunjangan_lainnya) + parseFloat(g.bonus);
        const potongan  = parseFloat(g.potongan_bpjs) + parseFloat(g.potongan_pajak) + parseFloat(g.potongan_lainnya);

        document.getElementById('detailGajiContent').innerHTML = `
        <div class="slip-header">
            <h2>SLIP GAJI KARYAWAN</h2>
            <p>Periode: ${namaBulan[g.bulan]} ${g.tahun}</p>
        </div>
        <div class="slip-info">
            <div class="slip-info-item"><span class="label">Nama</span><span class="value">${k.nama || '-'}</span></div>
            <div class="slip-info-item"><span class="label">NIK</span><span class="value">${k.nik || '-'}</span></div>
            <div class="slip-info-item"><span class="label">Jabatan</span><span class="value">${k.jabatan || '-'}</span></div>
            <div class="slip-info-item"><span class="label">Departemen</span><span class="value">${k.departemen || '-'}</span></div>
        </div>
        <table class="slip-table">
            <thead><tr><th>Komponen</th><th>Jumlah</th></tr></thead>
            <tbody>
                <tr><td colspan="2" style="font-weight:700;color:var(--success);padding-top:10px">PENDAPATAN</td></tr>
                <tr><td>Gaji Pokok</td><td>${rupiah(g.gaji_pokok)}</td></tr>
                <tr><td>Tunjangan Transport</td><td>${rupiah(g.tunjangan_transport)}</td></tr>
                <tr><td>Tunjangan Makan</td><td>${rupiah(g.tunjangan_makan)}</td></tr>
                <tr><td>Tunjangan Lainnya</td><td>${rupiah(g.tunjangan_lainnya)}</td></tr>
                <tr><td>Bonus</td><td>${rupiah(g.bonus)}</td></tr>
                <tr style="font-weight:700"><td>Total Pendapatan</td><td>${rupiah(parseFloat(g.gaji_pokok) + tunjangan)}</td></tr>
                <tr><td colspan="2" style="font-weight:700;color:var(--danger);padding-top:10px">POTONGAN</td></tr>
                <tr><td>Potongan BPJS</td><td>${rupiah(g.potongan_bpjs)}</td></tr>
                <tr><td>Potongan Pajak</td><td>${rupiah(g.potongan_pajak)}</td></tr>
                <tr><td>Potongan Lainnya</td><td>${rupiah(g.potongan_lainnya)}</td></tr>
                <tr style="font-weight:700"><td>Total Potongan</td><td>${rupiah(potongan)}</td></tr>
            </tbody>
        </table>
        <div class="slip-total">
            <span>GAJI BERSIH</span>
            <strong>${rupiah(g.gaji_bersih)}</strong>
        </div>
        <p style="margin-top:12px;font-size:12px;color:var(--text-muted);text-align:center">
            Status: <strong>${g.status_bayar === 'sudah_bayar' ? '✅ Sudah Dibayar' : '⏳ Belum Dibayar'}</strong>
            ${g.tanggal_bayar ? ' pada ' + new Date(g.tanggal_bayar).toLocaleDateString('id-ID') : ''}
        </p>`;
        openModal('modalDetailGaji');
    });
}

function hapusGaji(id, nama, periode) {
    document.getElementById('hapusMessage').textContent = `Hapus data gaji "${nama}" periode ${periode}?`;
    hapusCallback = () => {
        ajax('DELETE', '/gaji/' + id).then(res => {
            showToast(res.message, res.success ? 'success' : 'error');
            closeModal('modalHapus');
            if (res.success) loadGaji();
        });
    };
    openModal('modalHapus');
}

document.getElementById('btnKonfirmasiHapus').addEventListener('click', () => {
    if (hapusCallback) hapusCallback();
});

// ---- Init ----
// Sembunyikan semua page dulu, tampilkan dashboard
document.querySelectorAll('.page').forEach(p => {
    p.classList.remove('active');
    p.style.display = 'none';
});
const initPage = document.getElementById('page-dashboard');
if (initPage) { initPage.style.display = 'block'; initPage.classList.add('active'); }

loadDashboard();
loadKaryawan();

// ============================================================
// ABSENSI
// ============================================================
let absensiList = [];

// Isi dropdown tahun filter absensi & rekap
(function fillAbsTahun() {
    const now = new Date().getFullYear();
    ['absTahunFilter', 'rekapTahun'].forEach(selId => {
        const sel = document.getElementById(selId);
        if (!sel) return;
        for (let y = now; y >= now - 5; y--) {
            const opt = document.createElement('option');
            opt.value = y; opt.textContent = y;
            sel.appendChild(opt);
        }
        sel.value = now;
    });
    // Default bulan ke bulan ini
    const bln = new Date().getMonth() + 1;
    const absBulan = document.getElementById('absBulanFilter');
    const rekapBln = document.getElementById('rekapBulan');
    if (absBulan) absBulan.value = bln;
    if (rekapBln) rekapBln.value = bln;
})();

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        const el = document.getElementById('tab-' + tab);
        if (el) el.style.display = 'block';
    });
});

// Load absensi
function loadAbsensi() {
    const bulan  = document.getElementById('absBulanFilter').value;
    const tahun  = document.getElementById('absTahunFilter').value;
    const status = document.getElementById('absStatusFilter').value;
    let url = '/absensi?';
    if (bulan)  url += 'bulan=' + bulan + '&';
    if (tahun)  url += 'tahun=' + tahun + '&';
    if (status) url += 'status=' + status + '&';

    document.getElementById('tbodyAbsensi').innerHTML =
        '<tr><td colspan="9" class="text-center loading-row"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>';

    ajax('GET', url).then(res => {
        absensiList = res.data || [];
        renderAbsensi(absensiList);
    });
}

function renderAbsensi(data) {
    const tbody = document.getElementById('tbodyAbsensi');
    if (!data.length) {
        tbody.innerHTML = '<tr class="empty-row"><td colspan="9"><i class="fas fa-inbox"></i> Tidak ada data absensi</td></tr>';
        return;
    }

    const statusLabel = {
        hadir: '<span class="badge badge-hadir">✅ Hadir</span>',
        alpha: '<span class="badge badge-alpha">❌ Alpha</span>',
        izin:  '<span class="badge badge-izin">📋 Izin</span>',
        sakit: '<span class="badge badge-sakit">🏥 Sakit</span>',
    };

    tbody.innerHTML = data.map(a => `
    <tr>
        <td>
            <strong>${a.nama_karyawan}</strong><br>
            <small style="color:var(--text-muted)">${a.jabatan}</small>
        </td>
        <td>${a.tanggal}</td>
        <td>${a.hari || '-'}</td>
        <td>${statusLabel[a.status] || a.status}</td>
        <td>${a.jam_masuk || '-'}</td>
        <td>${a.jam_keluar || '-'}</td>
        <td style="color:${a.potongan > 0 ? 'var(--danger)' : 'var(--text-muted)'}; font-weight:${a.potongan > 0 ? '700' : '400'}">
            ${a.potongan > 0 ? '- ' + rupiah(a.potongan) : '-'}
        </td>
        <td>${a.keterangan || '-'}</td>
        <td>
            <div class="action-btns">
                <button class="btn btn-sm btn-secondary btn-icon" title="Edit" onclick="editAbsensi(${a.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-icon" title="Hapus" onclick="hapusAbsensi(${a.id}, '${a.nama_karyawan}', '${a.tanggal}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>`).join('');
}

document.getElementById('btnAbsFilter').addEventListener('click', loadAbsensi);

// Populate karyawan di form absensi
function populateAbsKaryawan() {
    ajax('GET', '/karyawan').then(res => {
        const sel = document.getElementById('absKaryawanId');
        const cur = sel.value;
        sel.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
        (res.data || []).filter(k => k.status === 'aktif').forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.id;
            opt.textContent = k.nik + ' - ' + k.nama;
            sel.appendChild(opt);
        });
        if (cur) sel.value = cur;
    });
}

// Preview potongan saat status berubah
document.getElementById('absStatus').addEventListener('change', function () {
    const preview = document.getElementById('previewPotonganAbsensi');
    preview.style.display = this.value === 'alpha' ? 'flex' : 'none';
    // Auto kosongkan jam masuk/keluar jika alpha
    if (this.value === 'alpha') {
        document.getElementById('absJamMasuk').value  = '';
        document.getElementById('absJamKeluar').value = '';
    }
});

// Tambah absensi
document.getElementById('btnTambahAbsensi').addEventListener('click', () => {
    document.getElementById('modalAbsensiTitle').innerHTML = '<i class="fas fa-calendar-check"></i> Tambah Absensi';
    document.getElementById('formAbsensi').reset();
    document.getElementById('absensiId').value = '';
    document.getElementById('previewPotonganAbsensi').style.display = 'none';
    // Default tanggal hari ini
    document.getElementById('absTanggal').value = new Date().toISOString().split('T')[0];
    clearErrors(['errAbsKaryawanId', 'errAbsTanggal', 'errAbsStatus']);
    populateAbsKaryawan();
    openModal('modalAbsensi');
});

function editAbsensi(id) {
    ajax('GET', '/absensi/' + id).then(res => {
        if (!res.success) return showToast('Gagal memuat data', 'error');
        const a = res.data;
        document.getElementById('modalAbsensiTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Absensi';
        document.getElementById('absensiId').value    = a.id;
        document.getElementById('absTanggal').value   = a.tanggal;
        document.getElementById('absStatus').value    = a.status;
        document.getElementById('absJamMasuk').value  = a.jam_masuk  || '';
        document.getElementById('absJamKeluar').value = a.jam_keluar || '';
        document.getElementById('absKeterangan').value= a.keterangan || '';
        document.getElementById('previewPotonganAbsensi').style.display = a.status === 'alpha' ? 'flex' : 'none';
        clearErrors(['errAbsKaryawanId', 'errAbsTanggal', 'errAbsStatus']);
        populateAbsKaryawan();
        setTimeout(() => { document.getElementById('absKaryawanId').value = a.karyawan_id; }, 300);
        openModal('modalAbsensi');
    });
}

document.getElementById('btnSimpanAbsensi').addEventListener('click', () => {
    const id = document.getElementById('absensiId').value;
    const data = {
        karyawan_id: document.getElementById('absKaryawanId').value,
        tanggal:     document.getElementById('absTanggal').value,
        status:      document.getElementById('absStatus').value,
        jam_masuk:   document.getElementById('absJamMasuk').value  || null,
        jam_keluar:  document.getElementById('absJamKeluar').value || null,
        keterangan:  document.getElementById('absKeterangan').value.trim(),
    };
    clearErrors(['errAbsKaryawanId', 'errAbsTanggal', 'errAbsStatus']);
    const method = id ? 'PUT' : 'POST';
    const url    = id ? '/absensi/' + id : '/absensi';
    ajax(method, url, data).then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modalAbsensi');
            loadAbsensi();
        } else if (res.errors) {
            showErrors(res.errors);
        } else {
            showToast(res.message || 'Terjadi kesalahan', 'error');
        }
    });
});

function hapusAbsensi(id, nama, tanggal) {
    document.getElementById('hapusMessage').textContent =
        `Hapus absensi "${nama}" tanggal ${tanggal}? Potongan gaji akan disesuaikan ulang.`;
    hapusCallback = () => {
        ajax('DELETE', '/absensi/' + id).then(res => {
            showToast(res.message, res.success ? 'success' : 'error');
            closeModal('modalHapus');
            if (res.success) loadAbsensi();
        });
    };
    openModal('modalHapus');
}

// ---- Rekap Absensi ----
function loadRekap() {
    const bulan = document.getElementById('rekapBulan').value;
    const tahun = document.getElementById('rekapTahun').value;

    document.getElementById('tbodyRekap').innerHTML =
        '<tr><td colspan="8" class="text-center loading-row"><i class="fas fa-spinner fa-spin"></i> Memuat rekap...</td></tr>';

    ajax('GET', '/absensi/rekap?bulan=' + bulan + '&tahun=' + tahun).then(res => {
        const tbody = document.getElementById('tbodyRekap');
        const data  = res.data || [];
        if (!data.length) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="8"><i class="fas fa-inbox"></i> Tidak ada data</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(r => `
        <tr>
            <td>
                <strong>${r.nama}</strong><br>
                <small style="color:var(--text-muted)">${r.jabatan}</small>
            </td>
            <td>${r.departemen}</td>
            <td class="rekap-num rekap-hadir">${r.hadir}</td>
            <td class="rekap-num rekap-alpha">${r.alpha}</td>
            <td class="rekap-num" style="color:var(--info)">${r.izin}</td>
            <td class="rekap-num" style="color:var(--warning)">${r.sakit}</td>
            <td class="rekap-num">${r.total_hadir}</td>
            <td style="color:${r.total_potongan > 0 ? 'var(--danger)' : 'var(--text-muted)'}; font-weight:700">
                ${r.total_potongan > 0 ? '- ' + rupiah(r.total_potongan) : 'Rp 0'}
            </td>
        </tr>`).join('');
    });
}

document.getElementById('btnRekapFilter').addEventListener('click', loadRekap);
