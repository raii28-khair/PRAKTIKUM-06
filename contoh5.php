<?php
// Contoh 5 - parent::method dan trait
header('Content-Type: text/plain; charset=utf-8');

trait BisaCetak {
    public function cetak(): string { return 'Siap dicetak'; }
}
class Mahasiswa {
    public function label(): string { return 'Mahasiswa'; }
}
class MahasiswaReguler extends Mahasiswa {
    use BisaCetak;
    public function label(): string {
        return parent::label() . ' Reguler';
    }
}

$m = new MahasiswaReguler();
echo $m->label() . ' - ' . $m->cetak() . PHP_EOL;
// Hasil: Mahasiswa Reguler - Siap dicetak
