/* =====================================================
   SIM-Barang - main.js (Pertemuan 4, diperbarui di Pertemuan 5)
   1. Validasi formulir saat event submit
      (jika valid, formulir dikirim ke simpan.php)
   2. Pencarian / filter tabel secara real-time
   3. Notifikasi toast
   ===================================================== */

/* ---------- Ambil elemen dari HTML ---------- */
const formBarang = document.querySelector("#form-barang form");
const tbody = document.querySelector("#inventaris tbody");
const inputCari = document.querySelector("#cari");

const inputKode = document.querySelector("#kode");
const inputNama = document.querySelector("#nama");
const selectKategori = document.querySelector("#kategori");
const inputJumlah = document.querySelector("#jumlah");
const inputHarga = document.querySelector("#harga");
const selectKondisi = document.querySelector("#kondisi");

const semuaKolom = [inputKode, inputNama, selectKategori, inputJumlah, inputHarga, selectKondisi];

/* =====================================================
   1. VALIDASI FORMULIR
   ===================================================== */

function kodeSudahAda(kode) {
  const sel = tbody.querySelectorAll("tr:not(.kosong) td:first-child");
  return Array.from(sel).some((td) => td.textContent.trim() === kode);
}

// Aturan tiap kolom. Hasilnya: teks pesan error, atau "" jika valid.
const aturan = {
  kode: function (nilai) {
    const kode = nilai.trim();
    if (kode === "") return "Kode barang wajib diisi.";
    if (!/^BRG-\d+$/.test(kode)) {
      return 'Kode harus diawali "BRG-" diikuti angka, contoh: BRG-001.';
    }
    if (kodeSudahAda(kode)) return "Kode " + kode + " sudah ada di tabel.";
    return "";
  },

  nama: function (nilai) {
    return nilai.trim().length < 3 ? "Nama barang minimal 3 karakter." : "";
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

  // Baru di Pertemuan 5: harga satuan
  harga: function (nilai) {
    if (nilai.trim() === "") return "Harga satuan wajib diisi.";
    const angka = Number(nilai);
    if (Number.isNaN(angka)) return "Harga satuan harus berupa angka.";
    if (angka <= 0) return "Harga satuan harus lebih dari 0.";
    if (!Number.isInteger(angka)) return "Harga satuan harus berupa bilangan bulat.";
    return "";
  },

  kondisi: function (nilai) {
    return nilai === "" ? "Pilih kondisi barang." : "";
  },
};

// Tampilkan atau hapus pesan error di bawah sebuah kolom
function setStatus(kolom, pesan) {
  const pembungkus = kolom.parentElement;
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
    kolom.setCustomValidity(pesan);
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

function validasiKolom(kolom) {
  const pesan = aturan[kolom.id](kolom.value);
  setStatus(kolom, pesan);
  return pesan === "";
}

// Cek ulang otomatis saat user memperbaiki kolom yang salah
semuaKolom.forEach(function (kolom) {
  kolom.addEventListener("input", function () {
    if (kolom.classList.contains("salah")) validasiKolom(kolom);
  });
});

// Peristiwa utama: formulir dikirim (submit)
formBarang.addEventListener("submit", function (event) {
  let semuaValid = true;
  let kolomPertamaSalah = null;

  semuaKolom.forEach(function (kolom) {
    if (!validasiKolom(kolom)) {
      semuaValid = false;
      if (!kolomPertamaSalah) kolomPertamaSalah = kolom;
    }
  });

  if (!semuaValid) {
    event.preventDefault(); // tahan pengiriman: masih ada data yang salah
    kolomPertamaSalah.focus();
    tampilkanToast("Periksa kembali isian formulir.", "error");
  }
  // Jika semua valid, preventDefault() TIDAK dipanggil, sehingga
  // browser mengirim data ke simpan.php (sesuai atribut action pada <form>).
});

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

  function tutup() {
    toast.classList.add("keluar");
    toast.addEventListener("animationend", function () {
      toast.remove();
    }, { once: true });
  }

  toast.addEventListener("click", tutup);
  setTimeout(tutup, 3500);
}