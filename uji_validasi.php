<?php
declare(strict_types=1);
// Skenario pengujian 01-10 (tanpa browser). Skenario 11 diuji di browser (refresh), 12 di uji_akses_private.php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/DataMahasiswa.php';

function reguler(string $nim, int $semester, int $ukt, string $nama = 'Andi'): MahasiswaReguler {
    return new MahasiswaReguler($nim, $nama, 'Sains dan Teknologi', 'Informatika',
                                'SMAN 1 Sukabumi', $semester, 'Membaca', 'Islam', $ukt);
}
function beasiswa(string $nim, int $semester, int $ukt, int $potongan, string $nama = 'Siti'): MahasiswaBeasiswa {
    return new MahasiswaBeasiswa($nim, $nama, 'Sains dan Teknologi', 'Informatika',
                                 'SMAN 1 Sukabumi', $semester, 'Membaca', 'Islam', $ukt, $potongan);
}
function rp(int $n): string { return 'Rp' . number_format($n, 0, ',', '.'); }

function uji(string $no, string $skenario, callable $aksi): void {
    echo "[$no] $skenario" . PHP_EOL;
    try {
        echo "      Hasil : " . $aksi() . PHP_EOL;
    } catch (InvalidArgumentException $e) {
        echo "      Hasil : DITOLAK - " . $e->getMessage() . PHP_EOL;
    }
}

$repo = new DataMahasiswa();

uji('01', 'Tambah Reguler UKT 5.000.000', function () use ($repo) {
    $m = reguler('23001', 4, 5000000);
    if (!$repo->tambah($m)) throw new InvalidArgumentException('NIM sudah terdaftar');
    return 'disimpan, jenis ' . $m->getJenis() . ', biaya akhir ' . rp($m->hitungBiaya());
});

uji('02', 'Tambah Beasiswa UKT 5.000.000, potongan 60%', function () use ($repo) {
    $m = beasiswa('23002', 4, 5000000, 60);
    if (!$repo->tambah($m)) throw new InvalidArgumentException('NIM sudah terdaftar');
    return 'disimpan, jenis ' . $m->getJenis() . ', biaya akhir ' . rp($m->hitungBiaya());
});

uji('03', 'NIM kosong', function () {
    reguler('', 4, 5000000);
    return 'DITERIMA (tidak diharapkan)';
});

uji('04a', 'Semester 0', function () {
    reguler('23003', 0, 5000000);
    return 'DITERIMA (tidak diharapkan)';
});

uji('04b', 'Semester 15', function () {
    reguler('23003', 15, 5000000);
    return 'DITERIMA (tidak diharapkan)';
});

uji('05', 'UKT -100', function () {
    reguler('23003', 4, -100);
    return 'DITERIMA (tidak diharapkan)';
});

uji('06', 'Potongan 120%', function () {
    beasiswa('23004', 4, 5000000, 120);
    return 'DITERIMA (tidak diharapkan)';
});

uji('07', 'NIM duplikat (23001)', function () use ($repo) {
    if (!$repo->tambah(reguler('23001', 4, 5000000))) {
        throw new InvalidArgumentException('NIM sudah terdaftar');
    }
    return 'DITERIMA (tidak diharapkan)';
});

uji('08', 'Edit 23001 dari Reguler menjadi Beasiswa (NIM sama)', function () use ($repo) {
    if (!$repo->ubah('23001', beasiswa('23001', 4, 5000000, 20, 'Andi'))) {
        throw new InvalidArgumentException('Edit ditolak');
    }
    return 'jenis berubah menjadi ' . $repo->cari('23001')->getJenis()
         . ', jumlah record tetap ' . count($repo->semua());
});

uji('09', 'Edit 23001 dengan NIM berbeda (99999)', function () use ($repo) {
    if (!$repo->ubah('23001', reguler('99999', 4, 5000000))) {
        throw new InvalidArgumentException('Edit ditolak: NIM tidak boleh berubah');
    }
    return 'DITERIMA (tidak diharapkan)';
});

uji('10', 'Hapus satu record (23002)', function () use ($repo) {
    if (!$repo->hapus('23002')) throw new InvalidArgumentException('Data tidak ditemukan');
    return 'dihapus; sisa record ' . count($repo->semua())
         . ', NIM 23001 masih ada: ' . ($repo->cari('23001') !== null ? 'ya' : 'tidak');
});
