<?php
declare(strict_types=1);

namespace App\Interface;

interface SewaInterface {
    public function hitungTotalSewa(int $durasiHari): int;
    public function getDetailInfo(): string;
}