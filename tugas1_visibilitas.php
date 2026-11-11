<?php
// Tugas 1 - perbandingan private dan protected pada pewarisan
header('Content-Type: text/plain; charset=utf-8');

// Warning PHP diubah menjadi exception agar mudah ditangkap dan dicetak.
set_error_handler(function (int $no, string $pesan): bool {
    throw new ErrorException($pesan, 0, $no);
});

class Induk {
    public string $terbuka        = 'nilai public';
    protected string $terlindungi = 'nilai protected';
    private string $rahasia       = 'nilai private';

    public function bacaRahasia(): string { return $this->rahasia; }
}

class Anak extends Induk {
    public function lihatTerlindungi(): string {
        return $this->terlindungi;                      // boleh: protected
    }
    public function cobaRahasia(): string {
        return isset($this->rahasia)                    // private milik Induk
            ? 'terbaca'
            : 'TIDAK terbaca dari Anak (private milik Induk)';
    }
}

$a = new Anak();
echo "1. public dari luar        : " . $a->terbuka . PHP_EOL;
echo "2. protected via method Anak: " . $a->lihatTerlindungi() . PHP_EOL;
echo "3. private via getter Induk : " . $a->bacaRahasia() . PHP_EOL;
echo "4. private dari Anak        : " . $a->cobaRahasia() . PHP_EOL;

foreach (['terlindungi', 'rahasia'] as $prop) {
    try {
        $x = $a->$prop;
        echo "5. \$a->$prop dari luar terbaca (tidak diharapkan)" . PHP_EOL;
    } catch (Throwable $e) {
        echo "5. \$a->$prop dari luar ditolak: " . $e->getMessage() . PHP_EOL;
    }
}
