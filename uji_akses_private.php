<?php
declare(strict_types=1);
// Kasus uji 12: akses langsung property private tidak diperbolehkan
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/MahasiswaReguler.php';

// Warning PHP diubah menjadi exception agar semua bentuk penolakan tertangkap.
set_error_handler(function (int $no, string $pesan): bool {
    throw new ErrorException($pesan, 0, $no);
});

$m = new MahasiswaReguler('23001', 'Siti', 'Sains dan Teknologi', 'Informatika',
                          'SMAN 1 Sukabumi', 4, 'Membaca', 'Islam', 5000000);

echo "Via getter getNim(): " . $m->getNim() . PHP_EOL;

// nim = private milik parent; ukt = private milik child
foreach (['nim', 'ukt'] as $prop) {
    try {
        $nilai = $m->$prop;
        echo "\$m->$prop terbaca: $nilai (TIDAK diharapkan)" . PHP_EOL;
    } catch (Throwable $e) {
        echo "Akses langsung \$m->$prop ditolak: " . $e->getMessage() . PHP_EOL;
    }
}
