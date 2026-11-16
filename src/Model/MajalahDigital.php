<?php
declare(strict_types=1);

namespace App\Model;

class MajalahDigital extends Media {
    private string $edisi;

    public function __construct(string $id, string $judul, string $penulis, int $hargaSewa, string $edisi, bool $tersedia = true) {
        parent::__construct($id, $judul, $penulis, $hargaSewa, $tersedia);
        $this->edisi = $edisi;
    }

    public function getTipe(): string {
        return 'majalah';
    }

    public function getDetailInfo(): string {
        return $this->edisi;
    }

    public function hitungTotalSewa(int $durasiHari): int {
        return $this->hargaSewa * $durasiHari;
    }
}