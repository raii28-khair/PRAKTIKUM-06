<?php
declare(strict_types=1);
// Tugas 2 - implementasi inheritance sederhana: Pegawai, PegawaiTetap, PegawaiKontrak
header('Content-Type: text/plain; charset=utf-8');

abstract class Pegawai {
    private string $id;
    private string $nama;

    public function __construct(string $id, string $nama) {
        if (trim($id) === '' || trim($nama) === '') {
            throw new InvalidArgumentException('ID dan nama pegawai wajib diisi');
        }
        $this->id   = trim($id);
        $this->nama = trim($nama);
    }

    public function getId(): string   { return $this->id; }
    public function getNama(): string { return $this->nama; }

    abstract public function hitungGaji(): int;
    abstract public function getJenis(): string;
}

final class PegawaiTetap extends Pegawai {
    private int $gajiPokok;

    public function __construct(string $id, string $nama, int $gajiPokok) {
        parent::__construct($id, $nama);
        if ($gajiPokok < 0) {
            throw new InvalidArgumentException('Gaji pokok tidak boleh negatif');
        }
        $this->gajiPokok = $gajiPokok;
    }

    public function hitungGaji(): int { return $this->gajiPokok; }
    public function getJenis(): string { return 'Tetap'; }
}

final class PegawaiKontrak extends Pegawai {
    private int $upahPerJam;
    private int $jamKerja;

    public function __construct(string $id, string $nama, int $upahPerJam, int $jamKerja) {
        parent::__construct($id, $nama);
        if ($upahPerJam < 0 || $jamKerja < 0) {
            throw new InvalidArgumentException('Upah per jam dan jam kerja tidak boleh negatif');
        }
        $this->upahPerJam = $upahPerJam;
        $this->jamKerja   = $jamKerja;
    }

    public function hitungGaji(): int { return $this->upahPerJam * $this->jamKerja; }
    public function getJenis(): string { return 'Kontrak'; }
}

function rp(int $n): string { return 'Rp' . number_format($n, 0, ',', '.'); }

$daftar = [
    new PegawaiTetap('P-001', 'Budi Santoso', 6500000),
    new PegawaiKontrak('K-001', 'Citra Lestari', 30000, 160),
];

echo "DAFTAR PEGAWAI" . PHP_EOL;
foreach ($daftar as $p) {
    echo $p->getId() . ' | ' . $p->getNama() . ' | ' . $p->getJenis()
       . ' | gaji ' . rp($p->hitungGaji()) . PHP_EOL;
}

echo PHP_EOL . "PENGUJIAN VALIDASI" . PHP_EOL;
$uji = [
    'Gaji pokok -1'      => fn() => new PegawaiTetap('P-002', 'Dedi', -1),
    'Upah per jam -500'  => fn() => new PegawaiKontrak('K-002', 'Eka', -500, 10),
    'ID kosong'          => fn() => new PegawaiTetap('', 'Fajar', 1000000),
];
foreach ($uji as $nama => $aksi) {
    try {
        $aksi();
        echo $nama . ' -> DITERIMA (tidak diharapkan)' . PHP_EOL;
    } catch (InvalidArgumentException $e) {
        echo $nama . ' -> Ditolak: ' . $e->getMessage() . PHP_EOL;
    }
}
