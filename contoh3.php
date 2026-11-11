<?php
// Contoh 3 - private versus protected
header('Content-Type: text/plain; charset=utf-8');

class Mahasiswa {
    private string $nim = '23001';
    protected string $kategori = 'Mahasiswa';
    public function getNim(): string { return $this->nim; }
}
class MahasiswaReguler extends Mahasiswa {
    public function info(): string {
        // return $this->nim; // SALAH: private milik parent
        return $this->getNim() . ' / ' . $this->kategori;
    }
}

echo (new MahasiswaReguler())->info() . PHP_EOL;
// Hasil: 23001 / Mahasiswa
