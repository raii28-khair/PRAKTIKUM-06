<?php
declare(strict_types=1);

require_once __DIR__ . '/Mahasiswa.php';

/** Child pertama: mahasiswa reguler membayar UKT penuh. */
final class MahasiswaReguler extends Mahasiswa {
    private int $ukt;

    public function __construct(
        string $nim, string $nama, string $fakultas, string $prodi,
        string $asalSekolah, int $semester, string $hobi, string $agama,
        int $ukt
    ) {
        // Inisialisasi bagian parent (data umum + validasinya)
        parent::__construct($nim, $nama, $fakultas, $prodi, $asalSekolah, $semester, $hobi, $agama);

        // Validasi khusus child
        if ($ukt < 0) {
            throw new InvalidArgumentException('UKT tidak boleh negatif');
        }
        $this->ukt = $ukt;
    }

    public function getUkt(): int { return $this->ukt; }

    // Overriding: implementasi biaya milik Reguler
    public function hitungBiaya(): int { return $this->ukt; }

    public function getJenis(): string { return 'Reguler'; }
}
