<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Monitoring Gaji Karyawan</title>
    <link rel="stylesheet" href="{{ asset('css/app-gaji.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-money-bill-wave"></i>
            <span>Gaji Cuy</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="#" class="nav-item active" data-page="dashboard">
            <i class="fas fa-chart-pie"></i><span>Dashboard</span>
        </a>
        <a href="#" class="nav-item" data-page="karyawan">
            <i class="fas fa-users"></i><span>Data Karyawan</span>
        </a>
        <a href="#" class="nav-item" data-page="gaji">
            <i class="fas fa-file-invoice-dollar"></i><span>Data Gaji</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <i class="fas fa-circle-info"></i>
        <span>v1.0.0</span>
    </div>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">

    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-btn" id="menuBtn"><i class="fas fa-bars"></i></button>
            <h1 class="page-title" id="pageTitle">Dashboard</h1>
        </div>
        <div class="topbar-right">
            <span class="date-badge"><i class="fas fa-calendar-alt"></i> <span id="currentDate"></span></span>
        </div>
    </header>

    <!-- Pages -->
    <div class="page-content">

        <!-- ===== DASHBOARD PAGE ===== -->
        <div id="page-dashboard" class="page active">
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <p class="stat-label">Total Karyawan Aktif</p>
                        <h2 class="stat-value" id="statKaryawan">-</h2>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-info">
                        <p class="stat-label">Total Gaji Bulan Ini</p>
                        <h2 class="stat-value" id="statTotalGaji">-</h2>
                    </div>
                </div>
                <div class="stat-card teal">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <p class="stat-label">Sudah Dibayar</p>
                        <h2 class="stat-value" id="statSudahBayar">-</h2>
                    </div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <p class="stat-label">Belum Dibayar</p>
                        <h2 class="stat-value" id="statBelumBayar">-</h2>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card wide">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-bar"></i> Statistik Gaji 12 Bulan Terakhir</h3>
                    </div>
                    <div class="chart-body">
                        <canvas id="chartBulanan"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-trophy"></i> Top 5 Gaji Tertinggi Bulan Ini</h3>
                    </div>
                    <div class="chart-body">
                        <div id="topGajiList" class="top-list"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== KARYAWAN PAGE ===== -->
        <div id="page-karyawan" class="page">
            <div class="page-toolbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchKaryawan" placeholder="Cari nama, NIK, jabatan...">
                </div>
                <button class="btn btn-primary" id="btnTambahKaryawan">
                    <i class="fas fa-plus"></i> Tambah Karyawan
                </button>
            </div>
            <div class="table-card">
                <div class="table-responsive">
                    <table class="data-table" id="tableKaryawan">
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Departemen</th>
                                <th>Email</th>
                                <th>Tgl Masuk</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKaryawan">
                            <tr><td colspan="8" class="text-center loading-row"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===== GAJI PAGE ===== -->
        <div id="page-gaji" class="page">
            <div class="page-toolbar">
                <div class="filter-group">
                    <select id="filterBulan" class="filter-select">
                        <option value="">Semua Bulan</option>
                        <option value="1">Januari</option><option value="2">Februari</option>
                        <option value="3">Maret</option><option value="4">April</option>
                        <option value="5">Mei</option><option value="6">Juni</option>
                        <option value="7">Juli</option><option value="8">Agustus</option>
                        <option value="9">September</option><option value="10">Oktober</option>
                        <option value="11">November</option><option value="12">Desember</option>
                    </select>
                    <select id="filterTahun" class="filter-select">
                        <option value="">Semua Tahun</option>
                    </select>
                    <select id="filterStatus" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="sudah_bayar">Sudah Bayar</option>
                        <option value="belum_bayar">Belum Bayar</option>
                    </select>
                    <button class="btn btn-secondary" id="btnFilter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <button class="btn btn-primary" id="btnTambahGaji">
                    <i class="fas fa-plus"></i> Tambah Data Gaji
                </button>
            </div>
            <div class="table-card">
                <div class="table-responsive">
                    <table class="data-table" id="tableGaji">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th>Periode</th>
                                <th>Gaji Pokok</th>
                                <th>Tunjangan</th>
                                <th>Potongan</th>
                                <th>Gaji Bersih</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyGaji">
                            <tr><td colspan="9" class="text-center loading-row"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- end page-content -->
</div><!-- end main-content -->

<!-- ===== MODAL KARYAWAN ===== -->
<div class="modal-overlay" id="modalKaryawan">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalKaryawanTitle"><i class="fas fa-user-plus"></i> Tambah Karyawan</h3>
            <button class="modal-close" data-modal="modalKaryawan"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="formKaryawan" novalidate>
                <input type="hidden" id="karyawanId">
                <div class="form-grid">
                    <div class="form-group">
                        <label>NIK <span class="required">*</span></label>
                        <input type="text" id="kNik" name="nik" placeholder="Contoh: EMP001" required>
                        <span class="field-error" id="errNik"></span>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap <span class="required">*</span></label>
                        <input type="text" id="kNama" name="nama" placeholder="Nama karyawan" required>
                        <span class="field-error" id="errNama"></span>
                    </div>
                    <div class="form-group">
                        <label>Jabatan <span class="required">*</span></label>
                        <input type="text" id="kJabatan" name="jabatan" placeholder="Jabatan" required>
                        <span class="field-error" id="errJabatan"></span>
                    </div>
                    <div class="form-group">
                        <label>Departemen <span class="required">*</span></label>
                        <input type="text" id="kDepartemen" name="departemen" placeholder="Departemen" required>
                        <span class="field-error" id="errDepartemen"></span>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" id="kEmail" name="email" placeholder="email@perusahaan.com" required>
                        <span class="field-error" id="errEmail"></span>
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" id="kTelepon" name="telepon" placeholder="08xxxxxxxxxx">
                        <span class="field-error" id="errTelepon"></span>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Masuk <span class="required">*</span></label>
                        <input type="date" id="kTanggalMasuk" name="tanggal_masuk" required>
                        <span class="field-error" id="errTanggalMasuk"></span>
                    </div>
                    <div class="form-group">
                        <label>Status <span class="required">*</span></label>
                        <select id="kStatus" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                        <span class="field-error" id="errStatus"></span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-modal="modalKaryawan">Batal</button>
            <button class="btn btn-primary" id="btnSimpanKaryawan">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- ===== MODAL GAJI ===== -->
<div class="modal-overlay" id="modalGaji">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="modalGajiTitle"><i class="fas fa-file-invoice-dollar"></i> Tambah Data Gaji</h3>
            <button class="modal-close" data-modal="modalGaji"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="formGaji" novalidate>
                <input type="hidden" id="gajiId">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Karyawan <span class="required">*</span></label>
                        <select id="gKaryawanId" name="karyawan_id" required>
                            <option value="">-- Pilih Karyawan --</option>
                        </select>
                        <span class="field-error" id="errGKaryawan"></span>
                    </div>
                    <div class="form-group">
                        <label>Bulan <span class="required">*</span></label>
                        <select id="gBulan" name="bulan" required>
                            <option value="">-- Pilih Bulan --</option>
                            <option value="1">Januari</option><option value="2">Februari</option>
                            <option value="3">Maret</option><option value="4">April</option>
                            <option value="5">Mei</option><option value="6">Juni</option>
                            <option value="7">Juli</option><option value="8">Agustus</option>
                            <option value="9">September</option><option value="10">Oktober</option>
                            <option value="11">November</option><option value="12">Desember</option>
                        </select>
                        <span class="field-error" id="errGBulan"></span>
                    </div>
                    <div class="form-group">
                        <label>Tahun <span class="required">*</span></label>
                        <input type="number" id="gTahun" name="tahun" min="2000" max="2100" required>
                        <span class="field-error" id="errGTahun"></span>
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-plus-circle"></i> Pendapatan</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Gaji Pokok <span class="required">*</span></label>
                        <div class="input-prefix"><span>Rp</span><input type="number" id="gGajiPokok" name="gaji_pokok" min="0" step="1000" required></div>
                        <span class="field-error" id="errGGajiPokok"></span>
                    </div>
                    <div class="form-group">
                        <label>Tunjangan Transport</label>
                        <div class="input-prefix"><span>Rp</span><input type="number" id="gTunjanganTransport" name="tunjangan_transport" min="0" step="1000" value="0"></div>
                    </div>
                    <div class="form-group">
                        <label>Tunjangan Makan</label>
                        <div class="input-prefix"><span>Rp</span><input type="number" id="gTunjanganMakan" name="tunjangan_makan" min="0" step="1000" value="0"></div>
                    </div>
                    <div class="form-group">
                        <label>Tunjangan Lainnya</label>
                        <div class="input-prefix"><span>Rp</span><input type="number" id="gTunjanganLainnya" name="tunjangan_lainnya" min="0" step="1000" value="0"></div>
                    </div>
                    <div class="form-group">
                        <label>Bonus</label>
                        <div class="input-prefix"><span>Rp</span><input type="number" id="gBonus" name="bonus" min="0" step="1000" value="0"></div>
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-minus-circle"></i> Potongan</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Potongan BPJS</label>
                        <div class="input-prefix"><span>Rp</span><input type="number" id="gPotonganBpjs" name="potongan_bpjs" min="0" step="1000" value="0"></div>
                    </div>
                    <div class="form-group">
                        <label>Potongan Pajak</label>
                        <div class="input-prefix"><span>Rp</span><input type="number" id="gPotonganPajak" name="potongan_pajak" min="0" step="1000" value="0"></div>
                    </div>
                    <div class="form-group">
                        <label>Potongan Lainnya</label>
                        <div class="input-prefix"><span>Rp</span><input type="number" id="gPotonganLainnya" name="potongan_lainnya" min="0" step="1000" value="0"></div>
                    </div>
                </div>

                <div class="gaji-bersih-preview">
                    <span>Estimasi Gaji Bersih:</span>
                    <strong id="previewGajiBersih">Rp 0</strong>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Status Pembayaran <span class="required">*</span></label>
                        <select id="gStatusBayar" name="status_bayar" required>
                            <option value="belum_bayar">Belum Bayar</option>
                            <option value="sudah_bayar">Sudah Bayar</option>
                        </select>
                    </div>
                    <div class="form-group" id="groupTanggalBayar">
                        <label>Tanggal Bayar</label>
                        <input type="date" id="gTanggalBayar" name="tanggal_bayar">
                    </div>
                    <div class="form-group full-width">
                        <label>Keterangan</label>
                        <textarea id="gKeterangan" name="keterangan" rows="2" placeholder="Keterangan tambahan..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-modal="modalGaji">Batal</button>
            <button class="btn btn-primary" id="btnSimpanGaji">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- ===== MODAL DETAIL GAJI ===== -->
<div class="modal-overlay" id="modalDetailGaji">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Detail Slip Gaji</h3>
            <button class="modal-close" data-modal="modalDetailGaji"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detailGajiContent"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-modal="modalDetailGaji">Tutup</button>
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button>
        </div>
    </div>
</div>

<!-- ===== MODAL KONFIRMASI HAPUS ===== -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal modal-sm">
        <div class="modal-header danger">
            <h3><i class="fas fa-trash-alt"></i> Konfirmasi Hapus</h3>
            <button class="modal-close" data-modal="modalHapus"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p id="hapusMessage">Apakah Anda yakin ingin menghapus data ini?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-modal="modalHapus">Batal</button>
            <button class="btn btn-danger" id="btnKonfirmasiHapus">
                <i class="fas fa-trash-alt"></i> Hapus
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="toast"></div>

<script src="{{ asset('js/app-gaji.js') }}"></script>
</body>
</html>
