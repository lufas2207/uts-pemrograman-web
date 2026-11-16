<?php
declare(strict_types=1);

namespace App\Model;

class Ebook extends Media {
    private string $jumlahHalaman;

    public function __construct(string $id, string $judul, string $penulis, int $hargaSewa, string $jumlahHalaman, bool $tersedia = true) {
        parent::__construct($id, $judul, $penulis, $hargaSewa, $tersedia);
        $this->jumlahHalaman = $jumlahHalaman;
    }

    public function getTipe(): string {
        return 'ebook';
    }

    public function getDetailInfo(): string {
        return $this->jumlahHalaman;
    }

    public function hitungTotalSewa(int $durasiHari): int {
        $total = $this->hargaSewa * $durasiHari;
        if ($durasiHari > 7) {
            $total = (int)($total * 0.9);
        }
        return $total;
    }
}