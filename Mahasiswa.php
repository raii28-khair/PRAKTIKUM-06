<?php
declare(strict_types=1);

/**
 * Parent class (abstract) untuk semua jenis mahasiswa.
 * Semua data umum + validasinya diletakkan di sini SATU KALI saja.
 * Property tetap private (enkapsulasi Pertemuan 5); child membaca lewat getter.
 */
abstract class Mahasiswa {
    public const AGAMA_VALID = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    private string $nim;
    private string $nama;
    private string $fakultas;
    private string $prodi;
    private string $asalSekolah;
    private int    $semester;
    private string $hobi;
    private string $agama;

    public function __construct(
        string $nim, string $nama, string $fakultas, string $prodi,
        string $asalSekolah, int $semester, string $hobi, string $agama
    ) {
        $nim = trim($nim);
        if ($nim === '') {
            throw new InvalidArgumentException('NIM wajib diisi');
        }
        $nama        = self::wajibIsi($nama, 'Nama');
        $fakultas    = self::wajibIsi($fakultas, 'Fakultas');
        $prodi       = self::wajibIsi($prodi, 'Prodi');
        $asalSekolah = self::wajibIsi($asalSekolah, 'Asal sekolah');
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException('Semester harus 1–14');
        }
        $agama = trim($agama);
        if (!in_array($agama, self::AGAMA_VALID, true)) {
            throw new InvalidArgumentException('Agama tidak termasuk daftar yang diizinkan');
        }

        $this->nim         = $nim;
        $this->nama        = $nama;
        $this->fakultas    = $fakultas;
        $this->prodi       = $prodi;
        $this->asalSekolah = $asalSekolah;
        $this->semester    = $semester;
        $this->hobi        = trim($hobi); // hobi boleh kosong
        $this->agama       = $agama;
    }

    // ---- Getter (akses baca untuk child, repository, dan tampilan) ----
    public function getNim(): string         { return $this->nim; }
    public function getNama(): string        { return $this->nama; }
    public function getFakultas(): string    { return $this->fakultas; }
    public function getProdi(): string       { return $this->prodi; }
    public function getAsalSekolah(): string { return $this->asalSekolah; }
    public function getSemester(): int       { return $this->semester; }
    public function getHobi(): string        { return $this->hobi; }
    public function getAgama(): string       { return $this->agama; }

    // ---- Kontrak yang WAJIB diimplementasikan child ----
    abstract public function hitungBiaya(): int;
    abstract public function getJenis(): string;

    private static function wajibIsi(string $nilai, string $label): string {
        $nilai = trim($nilai);
        if ($nilai === '') {
            throw new InvalidArgumentException("$label wajib diisi");
        }
        return $nilai;
    }
}
