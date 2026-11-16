<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Ebook;
use App\Model\MajalahDigital;
use App\Model\Media;
use App\Model\Member;
use App\Model\Transaksi;

class KatalogService {
    private string $katalogPath;
    private string $transaksiPath;
    /** @var Media[] */
    private array $daftarMedia = [];
    /** @var Transaksi[] */
    private array $daftarTransaksi = [];

    public function __construct(string $katalogPath, string$transaksiPath) {
        $this->katalogPath =$katalogPath;
        $this->transaksiPath =$transaksiPath;
        $this->loadData();$this->loadTransaksi();
    }

    private function loadData(): void {
        if (!file_exists($this->katalogPath)) {
            file_put_contents($this->katalogPath, json_encode([]));
        }

        $raw = json_decode(file_get_contents($this->katalogPath), true) ?? [];$this->daftarMedia = [];

        foreach ($raw as$item) {
            if ($item['tipe'] === 'majalah') {
                $this->daftarMedia[] = new MajalahDigital($item['id'], $item['judul'],$item['penulis'],
                    (int)$item['hargaSewa'], $item['infoEkstra'], (bool)$item['tersedia']
                );
            } else {
                $this->daftarMedia[] = new Ebook($item['id'], $item['judul'],$item['penulis'],
                    (int)$item['hargaSewa'], $item['infoEkstra'], (bool)$item['tersedia']
                );
            }
        }
    }

    private function loadTransaksi(): void {
        if (!file_exists($this->transaksiPath)) {
            file_put_contents($this->transaksiPath, json_encode([]));
        }

        $raw = json_decode(file_get_contents($this->transaksiPath), true) ?? [];$this->daftarTransaksi = [];

        foreach ($raw as$trx) {
            $durasiAngka = (int) filter_var($trx['durasi'], FILTER_SANITIZE_NUMBER_INT);
            $this->daftarTransaksi[] = new Transaksi($trx['kodeTransaksi'],
                $trx['idMedia'],$trx['judul'],
                $trx['tipe'],$trx['peminjam'],
                $trx['identitas'],$durasiAngka > 0 ? $durasiAngka : 1, (int)$trx['totalBayar'],
                $trx['waktuPinjam'],$trx['status'] ?? 'AKTIF'
            );
        }
    }

    private function saveData(): void {
        $output = [];
        foreach ($this->daftarMedia as $media) {$output[] = [
                'id' => $media->getId(),
                'tipe' => $media->getTipe(),
                'judul' => $media->getJudul(),
                'penulis' => $media->getPenulis(),
                'hargaSewa' => $media->getHargaSewa(),
                'infoEkstra' => $media->getDetailInfo(),
                'tersedia' => $media->isTersedia()
            ];
        }
        file_put_contents($this->katalogPath, json_encode($output, JSON_PRETTY_PRINT));
    }

    private function saveTransaksi(): void {
        $output = [];
        foreach ($this->daftarTransaksi as$trx) {
            $output[] =$trx->toArray();
        }
        file_put_contents($this->transaksiPath, json_encode($output, JSON_PRETTY_PRINT));
    }

    public function getSemuaMedia(): array {
        $result = [];
        foreach ($this->daftarMedia as $m) {$result[] = [
                'id' => $m->getId(),
                'tipe' => $m->getTipe(),
                'judul' => $m->getJudul(),
                'penulis' => $m->getPenulis(),
                'hargaSewa' => $m->getHargaSewa(),
                'infoEkstra' => $m->getDetailInfo(),
                'tersedia' => $m->isTersedia()
            ];
        }
        return $result;
    }

    public function getSemuaTransaksi(): array {
        $result = [];
        foreach (array_reverse($this->daftarTransaksi) as$trx) {
            $result[] =$trx->toArray();
        }
        return $result;
    }

    public function tambahMedia(string $tipe, string$judul, string $penulis, int$harga, string $infoEkstra): array {$prefix = $tipe === 'majalah' ? 'MJ-' : 'BK-';$id = $prefix . str_pad((string)(count($this->daftarMedia) + 1), 2, '0', STR_PAD_LEFT);

        if ($tipe === 'majalah') {
            $baru = new MajalahDigital($id, $judul,$penulis, $harga,$infoEkstra, true);
        } else {
            $baru = new Ebook($id, $judul,$penulis, $harga,$infoEkstra, true);
        }

        $this->daftarMedia[] =$baru;
        $this->saveData();

        return ['status' => true, 'pesan' => 'Media berhasil ditambahkan ke katalog.'];
    }

    public function hapusMedia(string $idMedia): array {
        foreach ($this->daftarMedia as $index =>$media) {
            if ($media->getId() ===$idMedia) {
                if (!$media->isTersedia()) {
                    return [
                        'status' => false,
                        'pesan' => 'Media sedang dipinjam! Kembalikan terlebih dahulu sebelum menghapus.'
                    ];
                }

                array_splice($this->daftarMedia, $index, 1);$this->saveData();

                return ['status' => true, 'pesan' => 'Media berhasil dihapus dari katalog.'];
            }
        }
        return ['status' => false, 'pesan' => 'Media tidak ditemukan.'];
    }

    public function prosesPinjam(string $idMedia, Member $member, int$durasi): array {
        date_default_timezone_set('Asia/Jakarta');

        foreach ($this->daftarMedia as$media) {
            if ($media->getId() ===$idMedia) {
                if (!$media->isTersedia()) {
                    return ['status' => false, 'pesan' => 'Maaf, item ini sedang dipinjam.'];
                }

                $media->setTersedia(false);$this->saveData();

                $totalBiaya =$media->hitungTotalSewa($durasi);$kode = 'TRX-' . rand(1000, 9999);
                $waktu = date('d M Y, H:i');

                $transaksiBaru = new Transaksi($kode,
                    $media->getId(),$media->getJudul(),
                    strtoupper($media->getTipe()),$member->getNama(),
                    $member->getIdentitas(),$durasi,
                    $totalBiaya,$waktu,
                    'AKTIF'
                );

                $this->daftarTransaksi[] =$transaksiBaru;
                $this->saveTransaksi();

                return [
                    'status' => true,
                    'pesan' => 'Transaksi berhasil!',
                    'struk' => $transaksiBaru->toArray()
                ];
            }
        }
        return ['status' => false, 'pesan' => 'Media tidak ditemukan.'];
    }

    public function kembalikanMedia(string $idMedia): array {
        foreach ($this->daftarMedia as$media) {
            if ($media->getId() ===$idMedia) {
                $media->setTersedia(true);$this->saveData();

                foreach ($this->daftarTransaksi as$trx) {
                    if ($trx->getIdMedia() ===$idMedia && $trx->getStatus() === 'AKTIF') {$trx->tandaiSelesai();
                    }
                }
                $this->saveTransaksi();

                return ['status' => true, 'pesan' => 'Media berhasil dikembalikan.'];
            }
        }
        return ['status' => false, 'pesan' => 'ID tidak ditemukan.'];
    }
}