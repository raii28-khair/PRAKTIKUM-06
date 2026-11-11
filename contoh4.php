<?php
// Contoh 4 - method overriding
header('Content-Type: text/plain; charset=utf-8');

class Mahasiswa {
    public function hitungBiaya(): int { return 5000000; }
}
class MahasiswaBeasiswa extends Mahasiswa {
    public function hitungBiaya(): int { return 2000000; }
}

$a = new Mahasiswa();
$b = new MahasiswaBeasiswa();
echo $a->hitungBiaya() . ' | ' . $b->hitungBiaya() . PHP_EOL;
// Hasil: 5000000 | 2000000
