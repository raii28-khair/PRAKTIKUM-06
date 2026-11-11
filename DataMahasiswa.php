<?php
declare(strict_types=1);

require_once __DIR__ . '/MahasiswaReguler.php';
require_once __DIR__ . '/MahasiswaBeasiswa.php';

/**
 * Repository: menyimpan objek Mahasiswa (Reguler maupun Beasiswa) dengan key = NIM.
 * Semua parameter bertipe Mahasiswa, sehingga tidak ada percabangan per jenis.
 */
class DataMahasiswa {
    /** @var array<string, Mahasiswa> */
    private array $data = [];

    public function __construct(array $awal = []) {
        foreach ($awal as $m) {
            if ($m instanceof Mahasiswa) {
                $this->data[$m->getNim()] = $m;
            }
        }
    }

    public function tambah(Mahasiswa $m): bool {
        if (isset($this->data[$m->getNim()])) {
            return false; // NIM duplikat
        }
        $this->data[$m->getNim()] = $m;
        return true;
    }

    public function cari(string $nim): ?Mahasiswa {
        return $this->data[$nim] ?? null;
    }

    /** Pencarian berdasarkan potongan NIM atau nama (tidak peka huruf besar/kecil). */
    public function cariKata(string $kata): array {
        $kata = trim($kata);
        if ($kata === '') {
            return $this->data;
        }
        return array_filter(
            $this->data,
            fn(Mahasiswa $m): bool =>
                stripos($m->getNim(), $kata) !== false || stripos($m->getNama(), $kata) !== false
        );
    }

    /** @return array<string, Mahasiswa> */
    public function semua(): array {
        return $this->data;
    }

    /** NIM tidak boleh berubah; jenis boleh berubah (objek child baru pada NIM yang sama). */
    public function ubah(string $nimLama, Mahasiswa $m): bool {
        if (!isset($this->data[$nimLama]) || $m->getNim() !== $nimLama) {
            return false;
        }
        $this->data[$nimLama] = $m;
        return true;
    }

    public function hapus(string $nim): bool {
        if (!isset($this->data[$nim])) {
            return false;
        }
        unset($this->data[$nim]);
        return true;
    }
}
