<?php
declare(strict_types=1);
// Eksperimen kunci: satu method, perilaku berbeda
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/MahasiswaReguler.php';
require_once __DIR__ . '/MahasiswaBeasiswa.php';

$daftar = [
    new MahasiswaReguler('23001', 'Andi', 'Sains dan Teknologi', 'Informatika',
                         'SMAN 1 Sukabumi', 4, 'Futsal', 'Islam', 5000000),
    new MahasiswaBeasiswa('23002', 'Siti', 'Sains dan Teknologi', 'Informatika',
                          'SMAN 2 Sukabumi', 4, 'Membaca', 'Islam', 5000000, 60),
];

foreach ($daftar as $m) {
    echo $m->getNama() . ' / ' . $m->getJenis()
       . ' / Rp' . number_format($m->hitungBiaya(), 0, ',', '.') . PHP_EOL;
}
// Hasil:
// Andi / Reguler / Rp5.000.000
// Siti / Beasiswa / Rp2.000.000
