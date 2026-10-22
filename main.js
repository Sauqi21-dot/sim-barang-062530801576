/* =====================================================
   SIM-Barang - main.js (Pertemuan 4)
   1. Validasi formulir saat event submit
   2. Pencarian / filter tabel secara real-time
   3. Notifikasi toast saat formulir berhasil divalidasi
   ===================================================== */

/* ---------- Ambil elemen dari HTML ---------- */
const formBarang = document.querySelector("#form-barang form");
const tbody = document.querySelector("#inventaris tbody");
const inputCari = document.querySelector("#cari");

const inputKode = document.querySelector("#kode");
const inputNama = document.querySelector("#nama");
const selectKategori = document.querySelector("#kategori");
const inputJumlah = document.querySelector("#jumlah");
const selectKondisi = document.querySelector("#kondisi");

// semua kolom yang divalidasi, disimpan dalam satu array
const semuaKolom = [inputKode, inputNama, selectKategori, inputJumlah, selectKondisi];

/* =====================================================
   1. VALIDASI FORMULIR
   ===================================================== */

// Cek apakah kode sudah dipakai oleh barang lain di tabel
function kodeSudahAda(kode) {
  const sel = tbody.querySelectorAll("tr:not(.kosong) td:first-child");
  return Array.from(sel).some((td) => td.textContent.trim() === kode);
}

// Aturan validasi tiap kolom. Hasilnya: teks pesan error, atau "" jika valid.
const aturan = {
  kode: function (nilai) {
    const kode = nilai.trim();
    if (kode === "") return "Kode barang wajib diisi.";
    // harus diawali "BRG-" lalu angka saja, contoh: BRG-001
    if (!/^BRG-\d+$/.test(kode)) {
      return 'Kode harus diawali "BRG-" diikuti angka, contoh: BRG-001.';
    }
    if (kodeSudahAda(kode)) return "Kode " + kode + " sudah ada di tabel.";
    return "";
  },

  nama: function (nilai) {
    if (nilai.trim().length < 3) return "Nama barang minimal 3 karakter.";
    return "";
  },

  kategori: function (nilai) {
    return nilai === "" ? "Pilih kategori barang." : "";
  },

  jumlah: function (nilai) {
    if (nilai.trim() === "") return "Jumlah wajib diisi.";
    const angka = Number(nilai);
    if (Number.isNaN(angka)) return "Jumlah harus berupa angka.";
    if (angka <= 0) return "Jumlah harus lebih dari 0.";
    if (!Number.isInteger(angka)) return "Jumlah harus berupa bilangan bulat.";
    return "";
  },

  kondisi: function (nilai) {
    return nilai === "" ? "Pilih kondisi barang." : "";
  },
};

// Tampilkan atau hapus pesan error di bawah sebuah kolom
function setStatus(kolom, pesan) {
  const pembungkus = kolom.parentElement; // elemen <p> yang membungkus kolom
  let kotak = pembungkus.querySelector(".pesan-error");

  if (pesan) {
    if (!kotak) {
      kotak = document.createElement("small");
      kotak.className = "pesan-error";
      kotak.id = "error-" + kolom.id;
      pembungkus.appendChild(kotak);
    }
    kotak.textContent = pesan;
    kolom.classList.add("salah");
    kolom.setCustomValidity(pesan); // supaya CSS :invalid ikut aktif
    kolom.setAttribute("aria-invalid", "true");
    kolom.setAttribute("aria-describedby", kotak.id);
  } else {
    if (kotak) kotak.remove();
    kolom.classList.remove("salah");
    kolom.setCustomValidity("");
    kolom.removeAttribute("aria-invalid");
    kolom.removeAttribute("aria-describedby");
  }
}

// Validasi satu kolom, hasilnya true jika valid
function validasiKolom(kolom) {
  const pesan = aturan[kolom.id](kolom.value);
  setStatus(kolom, pesan);
  return pesan === "";
}

// Setelah kolom ditandai salah, cek ulang otomatis saat user memperbaikinya
semuaKolom.forEach(function (kolom) {
  kolom.addEventListener("input", function () {
    if (kolom.classList.contains("salah")) validasiKolom(kolom);
  });
});

// Peristiwa utama: formulir dikirim (submit)
formBarang.addEventListener("submit", function (event) {
  event.preventDefault(); // cegah halaman reload, kita proses sendiri

  let semuaValid = true;
  let kolomPertamaSalah = null;

  semuaKolom.forEach(function (kolom) {
    const valid = validasiKolom(kolom);
    if (!valid) {
      semuaValid = false;
      if (!kolomPertamaSalah) kolomPertamaSalah = kolom;
    }
  });

  if (!semuaValid) {
    kolomPertamaSalah.focus();
    tampilkanToast("Periksa kembali isian formulir.", "error");
    return;
  }

  // Semua valid: tambahkan ke tabel, tampilkan toast, kosongkan formulir
  const kode = inputKode.value.trim();
  const nama = inputNama.value.trim();

  tambahBaris(kode, nama, selectKategori.value, inputJumlah.value.trim(), selectKondisi.value);
  tampilkanToast('Barang "' + nama + '" (' + kode + ") berhasil ditambahkan.", "sukses");
  resetFormulir();
});

// Tambah baris baru di tabel inventaris
function tambahBaris(kode, nama, kategori, jumlah, kondisi) {
  const baris = document.createElement("tr");
  baris.className = "baru"; // untuk efek sorot sesaat

  [kode, nama, kategori, jumlah, kondisi].forEach(function (teks) {
    const td = document.createElement("td");
    td.textContent = teks; // textContent aman dari penyisipan HTML
    baris.appendChild(td);
  });

  tbody.appendChild(baris);
  terapkanFilter(); // supaya hasil pencarian tetap konsisten
  baris.scrollIntoView({ behavior: "smooth", block: "center" });
}

function resetFormulir() {
  formBarang.reset();
  semuaKolom.forEach(function (kolom) {
    setStatus(kolom, "");
  });
}

/* =====================================================
   2. PENCARIAN / FILTER TABEL (REAL-TIME)
   ===================================================== */
const infoCari = document.createElement("small");
infoCari.className = "info-cari";
inputCari.parentElement.appendChild(infoCari);

function terapkanFilter() {
  const kata = inputCari.value.trim().toLowerCase();
  const semuaBaris = tbody.querySelectorAll("tr:not(.kosong)");
  let jumlahTampil = 0;

  semuaBaris.forEach(function (baris) {
    const cocok = baris.textContent.toLowerCase().includes(kata);
    baris.hidden = !cocok;
    if (cocok) jumlahTampil++;
  });

  // Baris pesan jika tidak ada hasil
  let barisKosong = tbody.querySelector("tr.kosong");
  if (jumlahTampil === 0) {
    if (!barisKosong) {
      barisKosong = document.createElement("tr");
      barisKosong.className = "kosong";
      const td = document.createElement("td");
      td.colSpan = 5;
      td.textContent = "Tidak ada barang yang cocok dengan pencarian.";
      barisKosong.appendChild(td);
      tbody.appendChild(barisKosong);
    }
  } else if (barisKosong) {
    barisKosong.remove();
  }

  infoCari.textContent =
    kata === ""
      ? semuaBaris.length + " barang"
      : "Menampilkan " + jumlahTampil + " dari " + semuaBaris.length + " barang";
}

// "input" terjadi setiap kali isi kolom berubah, jadi hasil langsung berubah saat mengetik
inputCari.addEventListener("input", terapkanFilter);
terapkanFilter();

/* =====================================================
   3. NOTIFIKASI TOAST
   ===================================================== */
function tampilkanToast(pesan, jenis) {
  let wadah = document.querySelector("#toast-wadah");
  if (!wadah) {
    wadah = document.createElement("div");
    wadah.id = "toast-wadah";
    wadah.setAttribute("aria-live", "polite");
    document.body.appendChild(wadah);
  }

  const toast = document.createElement("div");
  toast.className = "toast " + (jenis === "error" ? "error" : "sukses");
  toast.setAttribute("role", jenis === "error" ? "alert" : "status");
  toast.textContent = pesan;
  wadah.appendChild(toast);

  // Animasi keluar, lalu hapus dari halaman
  function tutup() {
    toast.classList.add("keluar");
    toast.addEventListener("animationend", function () {
      toast.remove();
    }, { once: true });
  }

  toast.addEventListener("click", tutup); // klik toast untuk menutup
  setTimeout(tutup, 3500); // otomatis hilang setelah 3,5 detik
}