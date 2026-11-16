<?php
declare(strict_types=1);

header('Content-Type: application/json');

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use App\Model\Member;
use App\Service\KatalogService;

$service = new KatalogService(
    __DIR__ . '/data/katalog.json',
    __DIR__ . '/data/transaksi.json'
);
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'list') {
        echo json_encode(['status' => true, 'data' => $service->getSemuaMedia()]);
        exit;
    }
    if ($action === 'transaksi') {
        echo json_encode(['status' => true, 'data' => $service->getSemuaTransaksi()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'pinjam') {
        $nama = trim($input['nama'] ?? '');
        $identitas = trim($input['identitas'] ?? '');
        $idMedia = $input['idMedia'] ?? '';
        $durasi = (int)($input['durasi'] ?? 1);

        if ($nama === '' || $identitas === '' || $durasi < 1) {
            http_response_code(400);
            echo json_encode(['status' => false, 'pesan' => 'Data peminjam dan durasi wajib diisi dengan benar.']);
            exit;
        }

        $member = new Member($nama, $identitas);
        echo json_encode($service->prosesPinjam($idMedia, $member, $durasi));
        exit;
    }

    if ($action === 'tambah') {
        $tipe = $input['tipe'] ?? 'ebook';
        $judul = trim($input['judul'] ?? '');
        $penulis = trim($input['penulis'] ?? '');
        $harga = (int)($input['harga'] ?? 0);
        $infoEkstra = trim($input['infoEkstra'] ?? '');

        if ($judul === '' || $penulis === '' || $harga <= 0) {
            http_response_code(400);
            echo json_encode(['status' => false, 'pesan' => 'Semua field katalog wajib diisi valid.']);
            exit;
        }

        echo json_encode($service->tambahMedia($tipe, $judul, $penulis, $harga, $infoEkstra));
        exit;
    }

    if ($action === 'kembalikan') {
        $idMedia = $input['idMedia'] ?? '';
        echo json_encode($service->kembalikanMedia($idMedia));
        exit;
    }
}

http_response_code(404);
echo json_encode(['status' => false, 'pesan' => 'Endpoint tidak valid']);