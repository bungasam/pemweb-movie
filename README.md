# 🎬 CineView — Sistem Informasi Review Film dan Series

## 📖 Deskripsi

**CineView** adalah sistem informasi review film dan series berbasis web yang dirancang untuk membantu pengguna menemukan informasi film, melihat penilaian dari komunitas, serta membagikan pendapat mengenai film yang telah ditonton.

Website ini menyediakan informasi film seperti judul, genre, tahun rilis, sutradara, sinopsis, poster, rating, dan ulasan pengguna. Pengguna dapat mencari film, melakukan filter berdasarkan genre, memberikan rating, serta menulis ulasan.

Komentar pada ulasan bersifat opsional sehingga pengguna dapat memberikan rating tanpa harus menulis komentar. CineView juga menyediakan halaman rekomendasi yang menampilkan film berdasarkan rating tertinggi dari komunitas.

Selain halaman pengguna, tersedia panel admin yang digunakan untuk mengelola data film, data pengguna, ulasan, dan statistik website.

---

## 🎯 Tujuan

* Menyediakan informasi film dan series yang mudah diakses.
* Membantu pengguna menemukan rekomendasi tontonan.
* Menyediakan tempat bagi pengguna untuk memberikan rating dan ulasan.
* Menampilkan penilaian film berdasarkan pendapat komunitas.
* Mempermudah admin dalam mengelola film, pengguna, dan ulasan.
* Menerapkan sistem informasi berbasis web menggunakan PHP Native dan MySQL.
* Menerapkan akses database menggunakan PDO dan prepared statement.

---

## 👨‍💻 Tim Pengembang

### 1. Bunga Salma Azzahrah

**Frontend Developer / User Interface**

**Tanggung Jawab:**

* Mendesain tampilan website CineView.
* Mengembangkan halaman landing page.
* Mengembangkan tampilan daftar film.
* Mengembangkan halaman detail film.
* Mengembangkan tampilan form rating dan ulasan.
* Mengatur desain warna, tipografi, tombol, dan komponen website.
* Mengembangkan antarmuka halaman pengguna.
* Membantu membuat tampilan website yang responsif dan mudah digunakan.

### 2. Naylarifa Maulida Asri

**Backend Developer / User System**

**Tanggung Jawab:**

* Mengembangkan sistem login pengguna.
* Mengembangkan sistem registrasi akun.
* Mengelola session pengguna.
* Mengembangkan fitur logout dengan konfirmasi.
* Mengembangkan halaman profil pengguna.
* Mengembangkan fitur edit username.
* Mengembangkan fitur upload foto profil.
* Mengembangkan fitur tambah rating dan ulasan.
* Mengembangkan fitur edit dan hapus ulasan.
* Membuat rating dapat dikirim tanpa komentar.
* Mengembangkan fitur lupa password.
* Mengembangkan fitur reset password berdasarkan email yang tersimpan di database.
* Mengintegrasikan sistem pengguna dengan database menggunakan PDO.

### 3. Gayatri Pradnya Aira Putri

**Admin Developer / Database System**

**Tanggung Jawab:**

* Merancang database MySQL CineView.
* Mengembangkan dashboard admin.
* Mengembangkan fitur statistik website.
* Mengembangkan fitur pengelolaan film.
* Mengembangkan fitur tambah film.
* Mengembangkan fitur edit film.
* Mengembangkan fitur hapus film.
* Mengembangkan fitur pengelolaan pengguna.
* Mengembangkan fitur pencarian pengguna.
* Mengembangkan fitur melihat riwayat review pengguna.
* Mengembangkan fitur pengelolaan dan moderasi ulasan.
* Mengembangkan filter ulasan berdasarkan film, pengguna, rating, dan tanggal.
* Mengintegrasikan panel admin dengan database menggunakan PDO.

---

## 👥 Aktor Sistem

CineView memiliki tiga aktor utama, yaitu **Guest**, **User**, dan **Admin**.

### 1. Guest atau Pengunjung

Guest adalah pengunjung yang belum melakukan login.

Guest dapat melakukan:

* Melihat halaman beranda.
* Melihat film populer.
* Melihat film terbaru.
* Melihat daftar rekomendasi film.
* Mencari film berdasarkan judul.
* Melakukan filter film berdasarkan genre.
* Melihat detail film.
* Melihat poster film.
* Melihat genre film.
* Melihat tahun rilis film.
* Melihat nama sutradara.
* Membaca sinopsis film.
* Melihat rata-rata rating film.
* Membaca ulasan pengguna.
* Melakukan registrasi akun.
* Melakukan login.
* Menggunakan fitur lupa password.

Guest tidak dapat memberikan rating atau ulasan sebelum melakukan login.

### 2. User

User adalah pengguna yang telah memiliki akun dan melakukan login.

User dapat melakukan:

* Login ke dalam website.
* Logout dengan dialog konfirmasi.
* Melihat halaman beranda.
* Melihat seluruh film.
* Mencari film berdasarkan judul.
* Melakukan filter film berdasarkan genre.
* Melihat detail film.
* Melihat rekomendasi film.
* Memberikan rating dari 1 sampai 5.
* Memberikan rating tanpa komentar.
* Memberikan rating disertai komentar.
* Melihat ulasan pengguna lain.
* Mengedit rating dan ulasan miliknya.
* Menghapus rating dan ulasan miliknya.
* Melihat halaman profil.
* Melihat jumlah review yang telah dibuat.
* Melihat riwayat ulasan pribadi.
* Mengubah username.
* Mengunggah atau mengganti foto profil.
* Melihat tanggal pembuatan akun.
* Menggunakan fitur lupa password.
* Membuat password baru setelah email ditemukan pada database.

Setiap user hanya dapat memberikan satu ulasan untuk satu film. Ulasan tersebut masih dapat diedit atau dihapus oleh pemiliknya.

### 3. Admin

Admin adalah pengguna yang memiliki hak akses khusus untuk mengelola website.

Admin dapat melakukan:

* Login sebagai admin.
* Logout dengan dialog konfirmasi.
* Mengakses dashboard admin.
* Melihat total film.
* Melihat total pengguna.
* Melihat total ulasan.
* Melihat rata-rata rating seluruh film.
* Melihat film terbaru.
* Melihat ulasan terbaru.
* Mengelola data film.
* Menambahkan film baru.
* Mengunggah poster film.
* Mengubah informasi film.
* Menghapus film.
* Mencari film pada halaman pengelolaan film.
* Mengelola data pengguna.
* Mencari pengguna berdasarkan username atau email.
* Melihat jumlah ulasan setiap pengguna.
* Melihat riwayat ulasan pengguna.
* Menghapus akun pengguna.
* Mengelola seluruh ulasan.
* Mencari ulasan berdasarkan judul film.
* Melakukan filter ulasan berdasarkan pengguna.
* Melakukan filter ulasan berdasarkan rating.
* Melakukan filter ulasan berdasarkan tanggal.
* Melihat isi komentar pengguna.
* Menghapus ulasan yang tidak sesuai.

---

## ✨ Fitur Utama

### 🔐 Autentikasi

* Login user dan admin.
* Registrasi akun user.
* Pengelolaan session login.
* Pembatasan halaman berdasarkan role.
* Forgot password.
* Reset password.

### 👤 Profil Pengguna

* Menampilkan username.
* Menampilkan email.
* Menampilkan foto profil.
* Menampilkan tanggal bergabung.
* Menampilkan jumlah review yang telah ditulis.
* Mengubah username.
* Upload dan mengganti foto profil.
* Menampilkan riwayat rating dan ulasan.
* Edit ulasan.
* Hapus ulasan.

### 🎞️ Informasi Film

* Daftar film populer.
* Daftar film terbaru.
* Daftar seluruh film.
* Poster film.
* Judul film.
* Genre film.
* Tahun rilis.
* Nama sutradara.
* Sinopsis.
* Rata-rata rating.
* Jumlah ulasan.
* Halaman detail film.

### 🔍 Pencarian dan Filter

* Pencarian film berdasarkan judul.
* Pencarian secara langsung menggunakan JavaScript.
* Filter film berdasarkan genre.
* Tombol untuk menampilkan seluruh genre.
* Pengurutan rekomendasi berdasarkan rating tertinggi.
* Pencarian pengguna pada halaman admin.
* Pencarian dan filter ulasan pada halaman admin.

### ⭐ Rating dan Ulasan

* Rating dari 1 sampai 5 bintang.
* Komentar bersifat opsional.
* User dapat memberikan rating tanpa komentar.
* User dapat memberikan rating dengan komentar.
* Satu user hanya dapat memberikan satu ulasan pada satu film.
* Menampilkan rata-rata rating film.
* Menampilkan jumlah ulasan film.
* Menampilkan username pemberi ulasan.
* Menampilkan tanggal ulasan.
* Edit rating dan komentar.
* Hapus rating dan komentar.
* Menampilkan keterangan khusus untuk rating tanpa komentar.

### 🎯 Rekomendasi Film

* Menampilkan film berdasarkan rating komunitas.
* Film diurutkan dari rating tertinggi.
* Jumlah ulasan digunakan sebagai bagian dari pengurutan.
* Pencarian film pada halaman rekomendasi.
* Filter berdasarkan genre.
* Menampilkan rating rata-rata setiap film.

---

## 🗺️ Sitemap

```text
CineView
│
├── Guest
│   ├── Beranda
│   ├── Rekomendasi Film
│   │   ├── Pencarian Film
│   │   └── Filter Genre
│   ├── Detail Film
│   │   ├── Informasi Film
│   │   ├── Rating Film
│   │   └── Daftar Ulasan
│   ├── Tentang
│   ├── Login
│   ├── Register
│   └── Lupa Password
│       └── Reset Password
│
├── User
│   ├── Beranda
│   ├── Rekomendasi Film
│   ├── Detail Film
│   │   ├── Tambah Rating
│   │   └── Tambah Ulasan
│   ├── Profil Saya
│   │   ├── Informasi Profil
│   │   ├── Edit Profil
│   │   ├── Upload Foto Profil
│   │   ├── Riwayat Ulasan
│   │   ├── Edit Ulasan
│   │   └── Hapus Ulasan
│   └── Logout
│
└── Admin
    ├── Dashboard Admin
    │   ├── Statistik Website
    │   ├── Film Terbaru
    │   └── Ulasan Terbaru
    ├── Kelola Film
    │   ├── Cari Film
    │   ├── Tambah Film
    │   ├── Edit Film
    │   └── Hapus Film
    ├── Kelola Pengguna
    │   ├── Cari Pengguna
    │   ├── Lihat Review Pengguna
    │   └── Hapus Pengguna
    ├── Kelola Ulasan
    │   ├── Cari Ulasan
    │   ├── Filter Ulasan
    │   ├── Lihat Komentar
    │   └── Hapus Ulasan
    ├── Lihat Website
    └── Logout
```

---

## 📁 Struktur Folder

```text
CineView/
│
├── index.php
├── login.php
├── register.php
├── logout.php
├── forgot_password.php
├── reset_password.php
├── rekomendasi.php
├── detail.php
├── tentang.php
├── proses_review.php
├── koneksi.php
├── footer.php
├── style.css
├── script.js
│
├── includes/
│   └── footer.php
│
├── admin/
│   ├── dashboard.php
│   ├── kelola_film.php
│   ├── tambah_film.php
│   ├── edit_film.php
│   ├── hapus_film.php
│   ├── kelola_review.php
│   ├── hapus_review.php
│   └── kelola_user.php
│
├── user/
│   ├── dashboard.php
│   ├── edit_profil.php
│   ├── edit_review.php
│   └── hapus_review.php
│
└── img/
    ├── default.svg
    ├── poster_film.jpg
    └── foto_profil.jpg
```

## 💻 Teknologi yang Digunakan
Frontend : HTML, CSS, JavaScript
Backend : PHP
Database : MySQL
Local Server : XAMPP
Version Control : Git / GitHub

## 🌐 Alamat Website Lokal

http://localhost/cineview/
