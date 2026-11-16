<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PustakaByte - Sistem Penyewaan E-Book</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="navbar">
        <div class="container nav-content">
            <div class="brand">
                <span class="brand-dot"></span>
                <h1>Pustaka<span>Byte</span></h1>
            </div>
            <nav class="nav-actions">
                <button type="button" id="btn-buka-tambah" class="btn btn-outline-light">+ Tambah Katalog</button>
            </nav>
        </div>
    </header>

    <main class="container main-layout">
        <section class="toolbar" aria-label="Filter Katalog">
            <div class="search-box">
                <input type="text" id="input-cari" placeholder="Cari judul buku atau penulis...">
            </div>
            <div class="filter-group">
                <button type="button" class="filter-btn active" data-filter="semua">Semua</button>
                <button type="button" class="filter-btn" data-filter="ebook">E-Book</button>
                <button type="button" class="filter-btn" data-filter="majalah">Majalah</button>
            </div>
        </section>

        <section aria-label="Daftar Media Digital">
            <div id="katalog-grid" class="katalog-grid">
            </div>
        </section>

        <section class="riwayat-section" aria-label="Catatan Transaksi">
            <div class="riwayat-header">
                <h2>Catatan Transaksi Penyewaan</h2>
                <span id="jumlah-aktif" class="badge-aktif">0 Sedang Disewa</span>
            </div>
            <div id="struk-container" class="struk-empty">
                Belum ada riwayat transaksi penyewaan.
            </div>
        </section>
    </main>

    <dialog id="modal-sewa" class="modal">
        <form id="form-sewa" novalidate>
            <div class="modal-header">
                <h3>Form Penyewaan Media</h3>
                <button type="button" class="btn-close" data-close="modal-sewa">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sewa-id">
                <div class="info-buku-box">
                    <strong id="sewa-judul-preview">-</strong>
                    <small id="sewa-harga-preview">Rp 0 / hari</small>
                </div>

                <div class="form-group">
                    <label for="sewa-nama">Nama Lengkap Member *</label>
                    <input type="text" id="sewa-nama" required placeholder="Contoh: Budi Santoso">
                    <span class="error-msg" id="err-nama"></span>
                </div>

                <div class="form-group">
                    <label for="sewa-identitas">NIM / Email Member *</label>
                    <input type="text" id="sewa-identitas" required placeholder="Contoh: 202410370110xxx">
                    <span class="error-msg" id="err-identitas"></span>
                </div>

                <div class="form-group">
                    <label for="sewa-durasi">Durasi Sewa (Hari) *</label>
                    <input type="number" id="sewa-durasi" min="1" max="30" value="3" required>
                    <small class="hint">Sewa E-Book &gt; 7 hari otomatis diskon 10%.</small>
                    <span class="error-msg" id="err-durasi"></span>
                </div>

                <div class="estimasi-box">
                    <span>Estimasi Total:</span>
                    <strong id="sewa-estimasi">Rp 0</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close="modal-sewa">Batal</button>
                <button type="submit" class="btn btn-primary">Konfirmasi Sewa</button>
            </div>
        </form>
    </dialog>

    <!-- Modal Tambah Katalog Baru -->
    <dialog id="modal-tambah" class="modal">
        <form id="form-tambah" novalidate>
            <div class="modal-header">
                <h3>Tambah Koleksi Baru</h3>
                <button type="button" class="btn-close" data-close="modal-tambah">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="tambah-tipe">Jenis Media *</label>
                    <select id="tambah-tipe">
                        <option value="ebook">E-Book</option>
                        <option value="majalah">Majalah Digital</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tambah-judul">Judul Media *</label>
                    <input type="text" id="tambah-judul" required placeholder="Masukkan judul lengkap">
                </div>

                <div class="form-group">
                    <label for="tambah-penulis">Penulis / Penerbit *</label>
                    <input type="text" id="tambah-penulis" required placeholder="Nama penulis">
                </div>

                <div class="form-group">
                    <label for="tambah-harga">Tarif Sewa per Hari (Rp) *</label>
                    <input type="number" id="tambah-harga" min="1000" step="500" value="10000" required>
                </div>

                <div class="form-group">
                    <label for="tambah-ekstra" id="label-ekstra">Jumlah Halaman *</label>
                    <input type="text" id="tambah-ekstra" required placeholder="Contoh: 250 Halaman">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close="modal-tambah">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan ke Katalog</button>
            </div>
        </form>
    </dialog>

    <footer class="footer">
        <div class="container">
            <p>UTS Pemrograman Web &mdash; Implementasi PHP 8 OOP &amp; Vanilla JS</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>