<?php
/* =====================================================
   SIM-Barang - Barang.php (Pertemuan 6)
   Class Barang: cetak biru untuk objek barang inventaris.
   ===================================================== */

class Barang
{
    // Property (data milik tiap objek). "private" = hanya bisa diakses dari dalam class.
    private string $kode;
    private string $nama;
    private string $kategori;
    private int $jumlah;
    private string $kondisi;

    // Constructor: dijalankan otomatis saat objek dibuat dengan "new Barang(...)"
    public function __construct(string $kode, string $nama, string $kategori, int $jumlah, string $kondisi)
    {
        $this->kode = $kode;
        $this->nama = $nama;
        $this->kategori = $kategori;
        $this->jumlah = $jumlah;
        $this->kondisi = $kondisi;
    }

    // Getter: cara resmi untuk membaca property yang private
    public function getKode(): string     { return $this->kode; }
    public function getNama(): string     { return $this->nama; }
    public function getKategori(): string { return $this->kategori; }
    public function getJumlah(): int      { return $this->jumlah; }
    public function getKondisi(): string  { return $this->kondisi; }

    // Method untuk menampilkan informasi barang dalam satu format teks
    public function tampilkanInfo(): string
    {
        return "{$this->kode} - {$this->nama} | {$this->kategori} | {$this->jumlah} unit | Kondisi: {$this->kondisi}";
    }
}