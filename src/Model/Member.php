<?php
declare(strict_types=1);

namespace App\Model;

class Member {
    private string $nama;
    private string $nimOrEmail;

    public function __construct(string $nama, string $nimOrEmail) {
        $this->nama = trim($nama);
        $this->nimOrEmail = trim($nimOrEmail);
    }

    public function getNama(): string {
        return $this->nama;
    }

    public function getIdentitas(): string {
        return $this->nimOrEmail;
    }
}