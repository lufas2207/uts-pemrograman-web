document.addEventListener('DOMContentLoaded', () => {
    let dataKatalog = [];
    let filterAktif = 'semua';
    let itemDipilih = null;

    const gridEl = document.getElementById('katalog-grid');
    const inputCari = document.getElementById('input-cari');
    const modalSewa = document.getElementById('modal-sewa');
    const modalTambah = document.getElementById('modal-tambah');

    const formatRp = (angka) => 'Rp ' + Number(angka).toLocaleString('id-ID');

    async function muatSemuaData() {
        try {
            const [resKatalog, resTrx] = await Promise.all([
                fetch('api.php?action=list'),
                fetch('api.php?action=transaksi')
            ]);
            const jsonKatalog = await resKatalog.json();
            const jsonTrx = await resTrx.json();

            if (jsonKatalog.status) {
                dataKatalog = jsonKatalog.data;
                renderKatalog();
            }
            if (jsonTrx.status) {
                renderRiwayatTransaksi(jsonTrx.data);
            }
        } catch (err) {
            gridEl.innerHTML = '<p>Gagal memuat data dari server.</p>';
        }
    }

    function renderKatalog() {
        const keyword = inputCari.value.toLowerCase();
        const hasil = dataKatalog.filter(item => {
            const cocokTipe = filterAktif === 'semua' || item.tipe === filterAktif;
            const cocokCari = item.judul.toLowerCase().includes(keyword) || 
                              item.penulis.toLowerCase().includes(keyword);
            return cocokTipe && cocokCari;
        });

        if (hasil.length === 0) {
            gridEl.innerHTML = '<p>Tidak ada koleksi yang sesuai pencarian.</p>';
            return;
        }

        gridEl.innerHTML = hasil.map(item => `
            <article class="card">
                <div>
                    <div class="card-top">
                        <span class="badge-tipe">${item.tipe}</span>
                        <span class="badge-status ${item.tersedia ? 'status-ready' : 'status-out'}">
                            ${item.tersedia ? '● Tersedia' : '○ Dipinjam'}
                        </span>
                    </div>
                    <h3>${item.judul}</h3>
                    <p class="card-meta">Oleh ${item.penulis} &bull; ${item.infoEkstra}</p>
                </div>
                <div class="card-bottom">
                    <div class="harga">${formatRp(item.hargaSewa)} <small>/hari</small></div>
                    <div class="card-actions">
                        ${item.tersedia 
                            ? `<button class="btn btn-primary btn-sewa" data-id="${item.id}">Sewa</button>
                               <button class="btn btn-danger btn-hapus" data-id="${item.id}" data-judul="${item.judul}" title="Hapus Media">Hapus</button>`
                            : `<button class="btn btn-return btn-kembali" data-id="${item.id}">Kembalikan</button>`
                        }
                    </div>
                </div>
            </article>
        `).join('');
    }

    function renderRiwayatTransaksi(daftarTrx) {
        const container = document.getElementById('struk-container');
        const badgeAktif = document.getElementById('jumlah-aktif');

        const totalAktif = daftarTrx.filter(t => t.status === 'AKTIF').length;
        badgeAktif.textContent = `${totalAktif} Sedang Disewa`;

        if (daftarTrx.length === 0) {
            container.className = 'struk-empty';
            container.innerHTML = 'Belum ada riwayat transaksi penyewaan.';
            return;
        }

        container.className = 'table-wrapper';
        container.innerHTML = `
            <table class="trx-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Waktu</th>
                        <th>Member</th>
                        <th>Media</th>
                        <th>Durasi</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${daftarTrx.map(trx => `
                        <tr>
                            <td><strong>${trx.kodeTransaksi}</strong></td>
                            <td>${trx.waktuPinjam}</td>
                            <td>${trx.peminjam}<br><small style="color:#64748b">${trx.identitas}</small></td>
                            <td>${trx.judul} <small style="color:#2563eb">[${trx.tipe}]</small></td>
                            <td>${trx.durasi}</td>
                            <td><strong>${formatRp(trx.totalBayar)}</strong></td>
                            <td>
                                <span class="status-pill ${trx.status === 'AKTIF' ? 'pill-aktif' : 'pill-selesai'}">
                                    ${trx.status === 'AKTIF' ? 'Sedang Disewa' : 'Dikembalikan'}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelector('.filter-btn.active').classList.remove('active');
            btn.classList.add('active');
            filterAktif = btn.dataset.filter;
            renderKatalog();
        });
    });

    inputCari.addEventListener('input', renderKatalog);

    gridEl.addEventListener('click', async (e) => {
        if (e.target.classList.contains('btn-sewa')) {
            const id = e.target.dataset.id;
            itemDipilih = dataKatalog.find(x => x.id === id);
            if (!itemDipilih) return;

            document.getElementById('sewa-id').value = itemDipilih.id;
            document.getElementById('sewa-judul-preview').textContent = itemDipilih.judul;
            document.getElementById('sewa-harga-preview').textContent = `${formatRp(itemDipilih.hargaSewa)} / hari (${itemDipilih.tipe.toUpperCase()})`;
            document.getElementById('form-sewa').reset();
            document.getElementById('sewa-durasi').value = 3;
            bersihkanErrorForm();
            hitungEstimasi();
            modalSewa.showModal();
        }

        if (e.target.classList.contains('btn-kembali')) {
            const idMedia = e.target.dataset.id;
            const res = await fetch('api.php?action=kembalikan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idMedia })
            });
            const json = await res.json();
            if (json.status) muatSemuaData();
        }

        if (e.target.classList.contains('btn-hapus')) {
            const idMedia = e.target.dataset.id;
            const judul = e.target.dataset.judul;

            if (!confirm(`Yakin ingin menghapus "${judul}" dari katalog?`)) return;

            const res = await fetch('api.php?action=hapus', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idMedia })
            });
            const json = await res.json();
            if (json.status) {
                muatSemuaData();
            } else {
                alert(json.pesan);
            }
        }
    });

    const inputDurasi = document.getElementById('sewa-durasi');
    function hitungEstimasi() {
        if (!itemDipilih) return;
        const hari = parseInt(inputDurasi.value) || 0;
        let total = itemDipilih.hargaSewa * hari;
        let ketDiskon = '';

        if (itemDipilih.tipe === 'ebook' && hari > 7) {
            total = Math.floor(total * 0.9);
            ketDiskon = ' (Diskon 10%)';
        }
        document.getElementById('sewa-estimasi').textContent = formatRp(total) + ketDiskon;
    }
    inputDurasi.addEventListener('input', hitungEstimasi);

    function bersihkanErrorForm() {
        document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');
    }

    document.getElementById('form-sewa').addEventListener('submit', async (e) => {
        e.preventDefault();
        bersihkanErrorForm();

        const nama = document.getElementById('sewa-nama').value.trim();
        const identitas = document.getElementById('sewa-identitas').value.trim();
        const durasi = parseInt(inputDurasi.value);
        let valid = true;

        if (nama.length < 3) {
            document.getElementById('err-nama').textContent = 'Nama minimal 3 karakter.';
            valid = false;
        }
        if (identitas.length < 5) {
            document.getElementById('err-identitas').textContent = 'Masukkan NIM atau Email yang valid.';
            valid = false;
        }
        if (isNaN(durasi) || durasi < 1 || durasi > 30) {
            document.getElementById('err-durasi').textContent = 'Durasi harus antara 1 sampai 30 hari.';
            valid = false;
        }

        if (!valid) return;

        const res = await fetch('api.php?action=pinjam', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                idMedia: itemDipilih.id,
                nama,
                identitas,
                durasi
            })
        });

        const json = await res.json();
        if (json.status) {
            modalSewa.close();
            muatSemuaData();
        } else {
            alert(json.pesan);
        }
    });

    document.getElementById('btn-buka-tambah').addEventListener('click', () => {
        document.getElementById('form-tambah').reset();
        modalTambah.showModal();
    });

    document.getElementById('tambah-tipe').addEventListener('change', (e) => {
        const label = document.getElementById('label-ekstra');
        const input = document.getElementById('tambah-ekstra');
        if (e.target.value === 'majalah') {
            label.textContent = 'Edisi Terbit *';
            input.placeholder = 'Contoh: Edisi Desember 2026';
        } else {
            label.textContent = 'Jumlah Halaman *';
            input.placeholder = 'Contoh: 320 Halaman';
        }
    });

    document.getElementById('form-tambah').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            tipe: document.getElementById('tambah-tipe').value,
            judul: document.getElementById('tambah-judul').value.trim(),
            penulis: document.getElementById('tambah-penulis').value.trim(),
            harga: parseInt(document.getElementById('tambah-harga').value),
            infoEkstra: document.getElementById('tambah-ekstra').value.trim()
        };

        if (!payload.judul || !payload.penulis || !payload.infoEkstra || payload.harga <= 0) {
            alert('Harap lengkapi semua kolom dengan benar.');
            return;
        }

        const res = await fetch('api.php?action=tambah', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (json.status) {
            modalTambah.close();
            muatSemuaData();
        }
    });

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.close).close();
        });
    });

    muatSemuaData();
});