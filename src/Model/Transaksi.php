<?php
declare(strict_types=1);

namespace App\Model;

class Transaksi {
    private string $kodeTransaksi;
    private string $idMedia;
    private string $judulMedia;
    private string $tipeMedia;
    private string $namaMember;
    private string $identitasMember;
    private int $durasi;
    private int $totalBayar;
    private string $waktuPinjam;
    private string $status;

    public function __construct(
        string $kodeTransaksi,
        string $idMedia,
        string $judulMedia,
        string $tipeMedia,
        string $namaMember,
        string $identitasMember,
        int $durasi,
        int $totalBayar,
        string $waktuPinjam,
        string $status = 'AKTIF'
    ) {
        $this->kodeTransaksi = $kodeTransaksi;
        $this->idMedia = $idMedia;
        $this->judulMedia = $judulMedia;
        $this->tipeMedia = $tipeMedia;
        $this->namaMember = $namaMember;
        $this->identitasMember = $identitasMember;
        $this->durasi = $durasi;
        $this->totalBayar = $totalBayar;
        $this->waktuPinjam = $waktuPinjam;
        $this->status = $status;
    }

    public function getIdMedia(): string { return $this->idMedia; }
    public function getStatus(): string { return $this->status; }

    public function tandaiSelesai(): void {
        $this->status = 'SELESAI';
    }

    public function toArray(): array {
        return [
            'kodeTransaksi' => $this->kodeTransaksi,
            'idMedia' => $this->idMedia,
            'judul' => $this->judulMedia,
            'tipe' => $this->tipeMedia,
            'peminjam' => $this->namaMember,
            'identitas' => $this->identitasMember,
            'durasi' => $this->durasi . ' Hari',
            'totalBayar' => $this->totalBayar,
            'waktuPinjam' => $this->waktuPinjam,
            'status' => $this->status
        ];
    }
}