<?php
declare(strict_types=1);

namespace App\Model;

use App\Interface\SewaInterface;

abstract class Media implements SewaInterface {
    protected string $id;
    protected string $judul;
    protected string $penulis;
    protected int $hargaSewa;
    protected bool $tersedia;

    public function __construct(string $id, string $judul, string $penulis, int $hargaSewa, bool $tersedia = true) {
        $this->id = $id;
        $this->judul = $judul;
        $this->penulis = $penulis;
        $this->hargaSewa = $hargaSewa;
        $this->tersedia = $tersedia;
    }

    public function getId(): string { return $this->id; }
    public function getJudul(): string { return $this->judul; }
    public function getPenulis(): string { return $this->penulis; }
    public function getHargaSewa(): int { return $this->hargaSewa; }
    public function isTersedia(): bool { return $this->tersedia; }

    public function setTersedia(bool $status): void {
        $this->tersedia = $status;
    }

    abstract public function getTipe(): string;
}