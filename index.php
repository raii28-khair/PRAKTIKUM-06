<?php
declare(strict_types=1);

// Class harus dimuat SEBELUM session_start() agar objek di session bisa di-unserialize.
require_once __DIR__ . '/DataMahasiswa.php';
session_start();

// Hanya untuk lingkungan praktikum lokal
ini_set('display_errors', '1');
error_reporting(E_ALL);

function aman(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function post(string $kunci): string {
    return trim((string)($_POST[$kunci] ?? ''));
}

/** Ambil bilangan bulat dari form dengan validasi, bukan sekadar casting. */
function angka(string $kunci, string $label): int {
    $n = filter_var(post($kunci), FILTER_VALIDATE_INT);
    if ($n === false) {
        throw new InvalidArgumentException("$label harus berupa bilangan bulat");
    }
    return $n;
}

function rupiah(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/** Membuat objek child sesuai pilihan jenis pada form. */
function bangunMahasiswa(): Mahasiswa {
    $nim = post('nim');
    $nama = post('nama');
    $fakultas = post('fakultas');
    $prodi = post('prodi');
    $asal = post('asalSekolah');
    $semester = angka('semester', 'Semester');
    $hobi = post('hobi');
    $agama = post('agama');
    $ukt = angka('ukt', 'UKT');

    $jenis = post('jenis');
    if ($jenis === 'reguler') {
        return new MahasiswaReguler($nim, $nama, $fakultas, $prodi, $asal, $semester, $hobi, $agama, $ukt);
    }
    if ($jenis === 'beasiswa') {
        $potongan = angka('potongan', 'Potongan');
        return new MahasiswaBeasiswa($nim, $nama, $fakultas, $prodi, $asal, $semester, $hobi, $agama, $ukt, $potongan);
    }
    throw new InvalidArgumentException('Jenis mahasiswa tidak dikenal');
}

$repo  = new DataMahasiswa($_SESSION['mahasiswa'] ?? []);
$error = '';
$info  = (string)($_SESSION['flash_info'] ?? '');
unset($_SESSION['flash_info']);
$form  = [];   // diisi saat ada error agar isian form tidak hilang

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = post('aksi');
    try {
        if ($aksi === 'tambah') {
            if (!$repo->tambah(bangunMahasiswa())) {
                throw new InvalidArgumentException('NIM sudah terdaftar');
            }
            $pesan = 'Data berhasil ditambahkan';
        } elseif ($aksi === 'ubah') {
            if (!$repo->ubah(post('nim_lama'), bangunMahasiswa())) {
                throw new InvalidArgumentException('Edit ditolak: NIM tidak boleh berubah atau data tidak ditemukan');
            }
            $pesan = 'Data berhasil diubah';
        } elseif ($aksi === 'hapus') {
            if (!$repo->hapus(post('nim'))) {
                throw new InvalidArgumentException('Data mahasiswa tidak ditemukan');
            }
            $pesan = 'Data berhasil dihapus';
        } else {
            throw new InvalidArgumentException('Aksi tidak dikenal');
        }
        $_SESSION['mahasiswa'] = $repo->semua();
        $_SESSION['flash_info'] = $pesan;
        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
        $form  = $_POST;
    }
}

$kata = trim((string)($_GET['q'] ?? ''));
$edit = isset($_GET['edit']) ? $repo->cari((string)$_GET['edit']) : null;

// Nilai awal isian form
$v = [
    'nim' => '', 'nama' => '', 'fakultas' => '', 'prodi' => '', 'asalSekolah' => '',
    'semester' => '', 'hobi' => '', 'agama' => '', 'jenis' => 'reguler', 'ukt' => '', 'potongan' => '0',
];
if ($form !== []) {
    foreach ($v as $k => $_) {
        if (isset($form[$k])) {
            $v[$k] = (string)$form[$k];
        }
    }
    $modeEdit = post('aksi') === 'ubah';
    $nimLama  = post('nim_lama');
} elseif ($edit !== null) {
    $v['nim'] = $edit->getNim();
    $v['nama'] = $edit->getNama();
    $v['fakultas'] = $edit->getFakultas();
    $v['prodi'] = $edit->getProdi();
    $v['asalSekolah'] = $edit->getAsalSekolah();
    $v['semester'] = (string)$edit->getSemester();
    $v['hobi'] = $edit->getHobi();
    $v['agama'] = $edit->getAgama();
    if ($edit instanceof MahasiswaBeasiswa) {
        $v['jenis'] = 'beasiswa';
        $v['ukt'] = (string)$edit->getUkt();
        $v['potongan'] = (string)$edit->getPotonganPersen();
    } elseif ($edit instanceof MahasiswaReguler) {
        $v['jenis'] = 'reguler';
        $v['ukt'] = (string)$edit->getUkt();
    }
    $modeEdit = true;
    $nimLama  = $edit->getNim();
} else {
    $modeEdit = false;
    $nimLama  = '';
}

$daftar = $repo->cariKata($kata);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRUD Mahasiswa - Inheritance</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Arial, sans-serif; background: #f3f5f9; color: #1f2937; margin: 0; padding: 32px 16px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { font-size: 24px; margin: 0 0 4px; color: #12284c; }
        .sub { color: #6b7280; margin: 0 0 24px; font-size: 14px; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.08); padding: 20px 24px; margin-bottom: 24px; }
        .card h2 { font-size: 18px; margin: 0 0 16px; color: #12284c; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert.error { background: #fde8e8; color: #9b1c1c; border-left: 4px solid #e02424; }
        .alert.info  { background: #e6f6ec; color: #046c4e; border-left: 4px solid #0e9f6e; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px 24px; }
        .field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #374151; }
        .field input, .field select { width: 100%; padding: 9px 10px; border: 1px solid #cbd2d9; border-radius: 6px; font-size: 14px; font-family: inherit; background: #fff; }
        .field input:focus, .field select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
        .field input[readonly] { background: #eef0f3; color: #6b7280; cursor: not-allowed; }
        .hint { font-size: 12px; color: #6b7280; margin-top: 3px; }
        .actions { margin-top: 18px; display: flex; gap: 10px; align-items: center; }
        .btn { display: inline-block; padding: 8px 16px; border: none; border-radius: 6px; font-size: 14px; font-family: inherit; cursor: pointer; text-decoration: none; line-height: 1.2; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-light { background: #e5e7eb; color: #1f2937; }
        .btn-light:hover { background: #d1d5db; }
        .btn-edit { background: #fef3c7; color: #92400e; padding: 6px 12px; font-size: 13px; }
        .btn-danger { background: #fee2e2; color: #b91c1c; padding: 6px 12px; font-size: 13px; }
        .cari { display: flex; gap: 8px; margin-bottom: 14px; }
        .cari input { flex: 1; max-width: 360px; padding: 8px 10px; border: 1px solid #cbd2d9; border-radius: 6px; font-size: 14px; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; font-size: 14px; }
        thead th { background: #12284c; color: #fff; text-align: left; padding: 10px 12px; white-space: nowrap; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:hover { background: #eef4ff; }
        td.center, th.center { text-align: center; }
        td.kanan, th.kanan { text-align: right; white-space: nowrap; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge.reguler  { background: #dbeafe; color: #1e40af; }
        .badge.beasiswa { background: #dcfce7; color: #166534; }
        .aksi { display: flex; gap: 6px; white-space: nowrap; }
        .aksi form { margin: 0; }
        .kosong { text-align: center; color: #6b7280; padding: 24px; }
        .jumlah { font-size: 13px; color: #6b7280; margin-top: 10px; }
        @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="container">
    <h1>CRUD Data Mahasiswa</h1>
    <p class="sub">Praktikum PBO Pertemuan 06 &ndash; Inheritance (Reguler &amp; Beasiswa, data disimpan di PHP Session)</p>

    <?php if ($error !== ''): ?><div class="alert error"><?= aman($error) ?></div><?php endif; ?>
    <?php if ($info !== ''): ?><div class="alert info"><?= aman($info) ?></div><?php endif; ?>

    <div class="card">
        <h2><?= $modeEdit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h2>
        <form method="post" action="index.php">
            <input type="hidden" name="aksi" value="<?= $modeEdit ? 'ubah' : 'tambah' ?>">
            <?php if ($modeEdit): ?><input type="hidden" name="nim_lama" value="<?= aman($nimLama) ?>"><?php endif; ?>

            <div class="grid">
                <div class="field">
                    <label for="jenis">Jenis Mahasiswa</label>
                    <select id="jenis" name="jenis">
                        <option value="reguler"  <?= $v['jenis'] === 'reguler'  ? 'selected' : '' ?>>Reguler</option>
                        <option value="beasiswa" <?= $v['jenis'] === 'beasiswa' ? 'selected' : '' ?>>Beasiswa</option>
                    </select>
                    <?php if ($modeEdit): ?><div class="hint">Jenis boleh diganti saat edit.</div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="nim">NIM</label>
                    <input id="nim" name="nim" placeholder="Contoh: 23001" value="<?= aman($v['nim']) ?>" <?= $modeEdit ? 'readonly' : '' ?>>
                    <?php if ($modeEdit): ?><div class="hint">NIM tidak dapat diubah.</div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="nama">Nama</label>
                    <input id="nama" name="nama" placeholder="Nama lengkap" value="<?= aman($v['nama']) ?>">
                </div>
                <div class="field">
                    <label for="fakultas">Fakultas</label>
                    <input id="fakultas" name="fakultas" placeholder="Contoh: Sains dan Teknologi" value="<?= aman($v['fakultas']) ?>">
                </div>
                <div class="field">
                    <label for="prodi">Prodi</label>
                    <input id="prodi" name="prodi" placeholder="Contoh: Informatika" value="<?= aman($v['prodi']) ?>">
                </div>
                <div class="field">
                    <label for="asalSekolah">Asal Sekolah</label>
                    <input id="asalSekolah" name="asalSekolah" placeholder="Contoh: SMAN 1 Sukabumi" value="<?= aman($v['asalSekolah']) ?>">
                </div>
                <div class="field">
                    <label for="semester">Semester</label>
                    <input id="semester" name="semester" type="number" placeholder="1 sampai 14" value="<?= aman($v['semester']) ?>">
                </div>
                <div class="field">
                    <label for="hobi">Hobi</label>
                    <input id="hobi" name="hobi" placeholder="Boleh dikosongkan" value="<?= aman($v['hobi']) ?>">
                </div>
                <div class="field">
                    <label for="agama">Agama</label>
                    <select id="agama" name="agama">
                        <option value="">-- pilih --</option>
                        <?php foreach (Mahasiswa::AGAMA_VALID as $a): ?>
                            <option value="<?= aman($a) ?>" <?= $v['agama'] === $a ? 'selected' : '' ?>><?= aman($a) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="ukt">UKT (Rp)</label>
                    <input id="ukt" name="ukt" type="number" placeholder="Contoh: 5000000" value="<?= aman($v['ukt']) ?>">
                </div>
                <div class="field" id="boxPotongan">
                    <label for="potongan">Potongan Beasiswa (%)</label>
                    <input id="potongan" name="potongan" type="number" placeholder="0 sampai 100" value="<?= aman($v['potongan']) ?>">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Simpan Perubahan' : 'Tambah' ?></button>
                <?php if ($modeEdit): ?><a href="index.php" class="btn btn-light">Batal</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Mahasiswa</h2>
        <form method="get" action="index.php" class="cari">
            <input type="text" name="q" placeholder="Cari NIM atau nama..." value="<?= aman($kata) ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
            <?php if ($kata !== ''): ?><a href="index.php" class="btn btn-light">Reset</a><?php endif; ?>
        </form>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NIM</th><th>Nama</th><th>Fakultas</th><th>Prodi</th>
                        <th>Asal Sekolah</th><th class="center">Semester</th><th>Hobi</th><th>Agama</th>
                        <th>Jenis</th><th class="kanan">Biaya Akhir</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($daftar as $m): ?>
                    <tr>
                        <td><?= aman($m->getNim()) ?></td>
                        <td><?= aman($m->getNama()) ?></td>
                        <td><?= aman($m->getFakultas()) ?></td>
                        <td><?= aman($m->getProdi()) ?></td>
                        <td><?= aman($m->getAsalSekolah()) ?></td>
                        <td class="center"><?= aman($m->getSemester()) ?></td>
                        <td><?= aman($m->getHobi()) ?></td>
                        <td><?= aman($m->getAgama()) ?></td>
                        <td><span class="badge <?= aman(strtolower($m->getJenis())) ?>"><?= aman($m->getJenis()) ?></span></td>
                        <td class="kanan"><?= aman(rupiah($m->hitungBiaya())) ?></td>
                        <td>
                            <div class="aksi">
                                <a class="btn btn-edit" href="?edit=<?= urlencode($m->getNim()) ?>">Edit</a>
                                <form method="post" action="index.php" onsubmit="return confirm('Hapus data ini?')">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="nim" value="<?= aman($m->getNim()) ?>">
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($daftar) === 0): ?>
                    <tr><td colspan="11" class="kosong"><?= $kata !== '' ? 'Data tidak ditemukan.' : 'Belum ada data.' ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="jumlah">Total ditampilkan: <?= count($daftar) ?> mahasiswa</div>
    </div>
</div>

<script>
    // Kolom potongan hanya relevan untuk jenis Beasiswa (server tetap memvalidasi).
    const jenis = document.getElementById('jenis');
    const boxPotongan = document.getElementById('boxPotongan');
    function atur() { boxPotongan.style.display = jenis.value === 'beasiswa' ? '' : 'none'; }
    jenis.addEventListener('change', atur);
    atur();
</script>
</body>
</html>
